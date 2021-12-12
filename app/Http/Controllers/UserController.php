<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Firebase\JWT\JWT;
use Firebase\JWT\key;
use App\Models\User;
use App\Models\Token;
use App\Models\PasswordReset;
use App\Jobs\RegisterUserMail;
use App\Http\Requests\UsersFormRequest;
use App\Http\Requests\UsersloginRequest;
use App\Http\Requests\UsersGetRequest;
use App\Http\Requests\UsersProfile;
use App\Http\Requests\UsersSendResetLinkResponse;
use App\Http\Requests\UsersSendResetResponse;
use App\Providers\servce;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function register(UsersFormRequest $request)
    {
         $user= User::where('email', $request->email)->first();
         try{
         if (isset($user)) {
             return response()->json(['error' => $request->messages()], 403);
         }
         // create new user
         $user = User::create([
             'name' => $request->name,
             'email' => $request->email,
             'password' =>Hash::make($request->password),
             'age' =>$request->age,
             'image' => $request->file('image')->store('profile_picture'),
         ]);

        $url =url('api/EmailConfirmation/'.$request['email']);
        RegisterUserMail::dispatch($request->email,$url);
         //response
        return response()->json([
            'success' => true,
            'message' => 'User created',
            'data' => $user
        ], Response::HTTP_OK);
        }
        catch(Throwable $ex)
        {
            return array('Massage'=>$ex->getMessage());
        }
    }
    // confirmEmail 
    public function confirmEmail($email){
        $user= User::where('email', $email)->first();
        $user->email_verified_at =$user->email_verified_at =time();
        $user->save();
        //    dd($user);
           return $user;
    }

    public function authenticate(UsersloginRequest $request)
    {
        $validator =$request->validated();
        $validator = $request->safe()->only('email', 'password');
        try{
            if (!isset($validator)) {
                return response()->json(['error' => $validator->messages()], 403);
            }else{
                $user= User::where('email', $request->email)->first();
                $tokeexit = Token::where('user_id',$user->id)->first();
            if (!Hash::check($request->password, $user->password)) {
                return response()->json(['messages' => 'Worng Password'], 403);
            }
                if(!$tokeexit)
                {
                    $token = (new servce)->createToken($user->id);
                    $tokenData = Token::create([
                        'token' => $token,
                        'user_id' => $user->id
                    ]);

                    $response = [
                        'user' => $user,
                        'token' => $token,
                    ];
                }else{
                    $response = [
                        'user' => $user,
                        'token' => "already login : ".$tokeexit->token,
                    ];
                }
            
                return response($response, 201);
            }
            }
        catch(Throwable $ex)
        {
            return array('Massage'=>$ex->getMessage());
        }
    }

    public function getToken(Request $request){
        //Get token 
        $jwt = $request->bearerToken();
        if (!isset($jwt)) {
            return response([
                'message' => 'token not found'
            ]);
        }
        //Decode token
        $decoded =(new servce)->decodeToken($jwt);
        //token data
        return $decoded->data;
    }
    function logout(Request $request)
    {

        // //Decode Token
        
        // $jwt = $request->bearerToken();
        // $decoded =(new servce)->decodeToken($jwt);
        
        $userId = $this->getToken($request);
        
        $userExist = Token::where("user_id",$userId)->first();
        try{
        if($userExist){
        
            $userExist->delete();
        
        }else{
            return response()->json([
            "message" => " already logged out"
            ], 404);
            
        }
            return response()->json([ "message" => "logout success" ], 200);
        }
        catch(Throwable $ex)
        {
            return array('Massage'=>$ex->getMessage());
        }
    }
    public function get_user(UsersGetRequest $request)
    {
        
        $validator =$request->validated();
        $validator = $request->safe()->only('token');
        try{
        if (!isset($validator)) {
            return response()->json(['error' => $validator->messages()], 403);
        }
        $decoded =(new servce)->decodeToken($request->token);
        $userId = $decoded->data;
        $user= User::where('id', $userId)->first();
 
        return response()->json(['user' => $user]);
        }
        catch(Throwable $ex)
        {
            return array('Massage'=>$ex->getMessage());
        }
    }
    
    public function profile(UsersProfile $request)
    {
        // $token = $request->bearerToken();
        try{
        // if (!isset($token)) {
        //     return response([
        //         'message' => 'token not found'
        //     ]);
        // }
        // $decoded =(new servce)->decodeToken($token);
        
        $userId = $this->getToken($request);
        $userExist = User::where("id",$userId)->first();

        if($userExist){
            if($request->name){$userExist->name = $request->name;}
            if($request->email){$userExist->email = $request->email;}
            if($request->age){$userExist->age = $request->age;}
            if($request->image){$userExist->image=$request->file('image')->store('profile_picture');}
            $userExist->save();
            return response()->json([ "message" => $userExist ], 200);
        
        }else{
            return response()->json([ "message" => " not update" ], 404);
            
        }
        }catch(Throwable $ex){
            return array('Massage'=>$ex->getMessage());
        }
        
    }
    
    
    protected function sendResetLinkResponse(UsersSendResetLinkResponse $request)
    {
       try{
        $token = (new servce)->createToken($request->email);
        
        $userExist= User::where('email', $request->email)->first();

     
        if($userExist){
            
            $data=PasswordReset::create([
                'token' => $token,
                'email' => $request->email,
                'valid' => 0,
            ]);
            $url =url('api/sendResetResponse/'.$request['email'].'/'.$token);
            Mail::to($request->email)->send(new ResetPasswordMail($url,'khawars282@gmail.com'));
        
        }else{
            $message = "Email could not be sent to this email address";
            return response($message, 404);
        }
        $response = ['data'=>'','message' => 'sent to this email address'];
            return response($response, 200);
        }catch(Throwable $ex){
            return array('Massage'=>$ex->getMessage());
        }
    }
    public function UserExistCheck($userExist){
        if($userExist){
            $message = "Password reset successfully";
            return response()->json($message);
            }else{
            $message = "Email and Token could not ";
            return response()->json($message);
            }
    }
    public function UserAndTokenCheck($tokenExist,$userExist){
        if(!isset($tokenExist)){
            $message = "token not exist";
                return response()->json($message);
                
            }
            if(!isset($userExist)){
                $message = "User not exist";
                return response()->json($message);}
        
    }
    protected function sendResetResponse(UsersSendResetResponse $request,$email,$token){
        try{
        $userExist= User::where('email', $email)->first();
        $tokenExist =PasswordReset::where('token', $token)->first();
        return $this->UserAndTokenCheck($tokenExist,$userExist);
        $decoded =(new servce)->decodeToken($token);
        $userEmail = $decoded->data;
        
            if($userExist->email == $userEmail && $tokenExist->valid == 0){
            $tokenExist->valid = $validTrue =1;
            $tokenExist->save();
            if($request->password){$userExist->password = Hash::make($request->password);}
            $userExist->save();
            return $this->UserExistCheck($userExist);
            }
        $response = ['data'=>$tokenExist->valid,'message' => "Email and Token could not"];
        return response()->json($response);
        }catch(Throwable $ex){
            return array('Massage'=>$ex->getMessage());
        }
        
        
    }

}

