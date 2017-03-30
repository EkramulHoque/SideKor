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

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;

class UserController extends Controller {
    private $provider="api";
    private $provider_id="0";
    use DispatchesJobs, ValidatesRequests;
    public function signup( Request $request){
        try {
             $this->validate($request,
                ['name' => 'required',
                    'email' => 'required|email|unique:users',
                    'password' => 'required'
                ]
            );

             if ($request->input('provider') !='') {
                $this->provider = $request->input('provider');
            }
            if ($request->input('provider_id') !='') {
                $this->provider_id = $request->input('provider_id');
            }
            $authUser=$this->findOrCreateUser($this->provider_id);
            if($authUser){ /// checks if a user exits
                return response()->json(['message' => $authUser],201);
            }
            $user = new User([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => bcrypt($request->input('password')),
                'provider' => $this->provider,
                'provider_id' => $this->provider_id,
            ]);

            $user->save();
        }catch (Exception $e){

            return response()->json(['message' => $e],500);
        }
return response()->json(['message' => 'Successfully Created User'],201);

    }

    public function  signin(Request $request){
        $this-> validate($request , ['name' => 'required' ,
            'email' => 'required | email',
            'password' => 'required'

            ]);
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
    protected function formatValidationErrors(Validator $validator)
    {
        return $validator->errors()->all();
    }
}