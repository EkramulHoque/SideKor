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
use Tymon\JWTAuth\Exceptions\JWTException;
use JWTAuth;

class UserController extends Controller {

    public function signup( Request $request){
        $this->validate($request,
        [    'name' => 'required',
        'email' => 'required|email|unique:users',
        'password' => 'required'
]
            );
        $user = new User([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password'=> bcrypt($request->input('password'))

        ]);
$user->save();
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
}