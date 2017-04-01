<?php
/**
 * Created by PhpStorm.
 * User: ekram
 * Date: 3/29/17
 * Time: 10:39 PM
 */
namespace App\Http\Controllers;
use App\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mockery\Exception;
use Tymon\JWTAuth\Exceptions\JWTException;
use JWTAuth;

use \Validator;
use Illuminate\Foundation\Bus\DispatchesJobs;
//use  \Validator;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;

class UserController extends Controller {
    private $provider="api";
    private $provider_id="0";
    use DispatchesJobs, ValidatesRequests;
    public function signup( Request $request){
        try {

            $rules = [
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required'
            ];

            $customMessages = [
                'required' => 'The :attribute field can not be blank.',
                'unique' => 'User exists with that email'
            ];


            $validator = Validator::make($request->all(),$rules,$customMessages);
            if($validator->fails()){
                return response()->json($validator->errors()->all(),201);
            }

            $authUser=$this->findOrCreateUser($request->provider_id);
            if($authUser){ /// checks if a user exits using social media id
                return response()->json(['error' => 'User Exists with that Social Media',
                    'provider_id' => $this->provider_id
                ],201);
            }elseif($authUser == null){
                $string = str_random(15);
                $user = new User([
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'password' => bcrypt($request->input('password')),
                    'provider' => 'Manual',
                    'provider_id' => $string,
                    'role' => $request->input('role')
                ]);
            }else {

                $user = new User([
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'password' => bcrypt($request->input('password')),
                    'provider' => $this->provider,
                    'provider_id' => $this->provider_id,
                    'role' => $request->input('role')
                ]);
            }

            $user->save();
        }catch (Exception $e){

            return response()->json(['error' => $e],500);
        }
return response()->json(['success' => 'Successfully Created User'],201);

    }

    public function  signin(Request $request){
        $rules =  ['name' => 'required' ,
            'email' => 'required | email',
            'password' => 'required'
        ];

        $customMessages = [
            'required' => 'The :attribute field can not be blank.',
            'unique' => 'User exists with that email'
        ];


        $validator = Validator::make($request->all(),$rules,$customMessages);
        if($validator->fails()){
            return response()->json($validator->errors()->all(),201);
        }

        $cred = $request->only('email','password');
        try{
        if(!$token = JWTAuth::attempt($cred)){
            return response()->json(['error' => 'Invalid Credintials!'],401);
        }
        }catch(JWTException $e){
            return response()->json(['error' => 'Could Not Create Token!'],500);
        }
        return response()->json(['token' => $token],200);


    }
    public function findOrCreateUser($provider_id)
    {

        $authUser = User::where('provider_id', $provider_id)->first();
        if ($authUser) {
            return $authUser;
        }

    }



}