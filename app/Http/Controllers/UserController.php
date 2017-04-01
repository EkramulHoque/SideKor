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
use Mockery\Exception;
use Tymon\JWTAuth\Exceptions\JWTException;
use JWTAuth;
use \Validator;
use Illuminate\Foundation\Bus\DispatchesJobs;
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
                return response()->json($validator->errors()->all(),404);
            }

            $authUser=$this->findOrCreateUser($request->provider_id);
            if($authUser){ /// checks if a user exits using social media id
                return response()->json(['error' => 'User Exists with that Social Media',
                    'provider_id' => $request->provider_id
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
                    'provider' => $request->input('provider'),
                    'provider_id' => $request->input('provider_id'),
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
            return response()->json($validator->errors()->all(),404);
        }

        $cred = $request->only('email','password');
        try{
        if(!$token = JWTAuth::attempt($cred)){
            return response()->json(['error' => 'Invalid Credintials!'],404);
        }
        }catch(JWTException $e){
            return response()->json(['error' => 'Could Not Create Token!'],500);
        }
        return response()->json(['token' => $token],200);


    }

    public  function getUserById($id){
    $user = User::find($id);
    if(!$user){
        return response()->json(['error' => 'Could Not Get User!'],404);
    }
        return response()->json(['User' => $user],200);
    }
    public function updateUserById(Request $request,$id){
        $user = User::find($id);
        if(!$user){
            return response()->json(['error' => 'Could Not Get User!'],404);
        }
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
            return response()->json($validator->errors()->all(),404);
        }
        if($request->name != null)
            $user->name = $request->name;
            if($request->email != null)
                $user->email = $request->email;
                if($request->password != null)
                    $user->password = bcrypt($request->password);
                    if($request->provider != null)
                        $user->provider = $request->provider;
                        if($request->provider_id != null)
                            $user->provider_id =  $request->provider_id;
                            if($request->role != null)
                                $user->role = $request->role;

                            $user->save();
        return response()->json(['success' => 'Successfully Updated User'],201);
    }
    public function findOrCreateUser($provider_id)
    {

        $authUser = User::where('provider_id', $provider_id)->first();
        if ($authUser) {
            return $authUser;
        }

    }



}