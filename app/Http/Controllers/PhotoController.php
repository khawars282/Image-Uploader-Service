<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\key;
use App\Models\User;
use App\Models\Token;
use App\Models\Photo;
use App\Models\PhotoPrivacy;

use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Mail;

use App\Http\Requests\PhotoSendLinkPrivacyRequest;
use App\Http\Requests\PhotoLinkPrivacyAccessRequest;

use App\Http\Requests\UploadPhotoRequest;
use App\Providers\servce;

class PhotoController extends Controller
{
    public function getToken(Request $request){
        //Get token 
        $jwt = $request->bearerToken();
        //Decode token
        $decoded =(new servce)->decodeToken($jwt);
        //token data
        return $decoded->data;
    }
    public function uploadPhoto(UploadPhotoRequest $request){
        //Pick token data
        $user = $this->getToken($request);
        //Find login user exist 
        $userExist = User::where("id",$user)->first();
        try{
            //Check user Exist
            if(isset($userExist)){
            //Pick id 
            $id = $userExist->id;
                //check file
                if($request->hasfile('image')) 
                    { 
                        //pick image
                        $image = $request->file('image');
                        // getting image extension
                        $extension = $image->getClientOriginalExtension(); 
                        // getting image name
                        $nameWithExtension = $image->getClientOriginalName();
                        //pick name first part
                        $name = explode('.', $nameWithExtension)[0];
                        //concatinat name with extention
                        $imageName =$name.'.'.$extension;
                        $image->move('Pictures/', $imageName);
                    }
                        $photo = Photo::create([
                            'name' => $request->name,
                            'image' => 'Pictures/'. $imageName,
                            'extension' => $extension,
                            'privacy'=> $request->privacy,
                            'user_id'=>$userExist->id,
                            ]);
                            $photo->save();
                            return response()->json(["success" => 'Page created successfully.'], 200);
                                
                    }else{
                        return response()->json(["message" => "Not Upload success "], 404);
                        
                    }
                return response()->json([ "message" => "Upload success" ], 200);
        }
        catch(Throwable $ex)
        {
            return array('Massage'=>$ex->getMessage());
        }
        
    }
    public function removePhoto(Request $request,$phototId){
        try{
        // $jwt = $request->bearerToken();
        // $decoded =(new servce)->decodeToken($jwt);
        $user = $this->getToken($request);
        
        $userExist = Photo::where("user_id",$user)->first();
        if(!$userExist){
            return response()->json(["massege"=>"user no upload photo"],400);
        }
        $photoExist = Photo::where("id",$phototId)->where("user_id",$userExist->user_id)->first();

        if(isset($photoExist->user_id)==isset($userExist->user_id)){
            $photoExist->delete();
            return response()->json([
                "success" => 'Photo delete successfully.'
                ], 200);
        }else{
            return response()->json([
                "success" => 'wrong Photo'
                ], 403);
        }
        }catch(Throwable $ex)
        {
            return array('Massage'=>$ex->getMessage());
        }
    }
    public function listAllPhotos(Request $request){
        try{
        //     $jwt = $request->bearerToken();
        //     $decoded =(new servce)->decodeToken($jwt);
            $user = $this->getToken($request);
            $Photos = Photo::where('user_id', $user)->pluck('image');
                if ($Photos) {
                    return response()->json([
                        "Photo" => $Photos
                    ]);
                }else{
                    return response()->json([
                        "Photo" => 'No Data'
                    ]);
                }

        }catch(Throwable $ex)
        {
            return array('Massage'=>$ex->getMessage());
        }
    }
    // date, time, name, extensions, private, public, hidden
    public function PhotosFindByName(Request $request,$name){
        try{
            if(!$name){
                return response()->json([
                "Photo" => 'No Name '
            ],400);}
            // $jwt = $request->bearerToken();
            // $decoded =(new servce)->decodeToken($jwt);
            $userId = $this->getToken($request);

            $PhotosExist = Photo::where('user_id', $userId)->where('name', $name)->pluck('image');
                if ($PhotosExist) {
                    return response()->json([
                        "Photo" => $PhotosExist
                    ],200);
                }else{
                    return response()->json([
                        "Photo" => 'No Data'
                    ],400);
                }

        }catch(Throwable $ex)
        {
            return array('Massage'=>$ex->getMessage());
        }
    }
    public function PhotosFindByExtensions(Request $request,$extensions){
        try{
            // $jwt = $request->bearerToken();
            // $decoded =(new servce)->decodeToken($jwt);
            $userId = $this->getToken($request);

            $PhotosExist = Photo::where('user_id', $userId)->where('extension', $extensions)->pluck('image');
                if ($PhotosExist) {
                    return response()->json([
                        "Photo" => $PhotosExist
                    ]);
                }else{
                    return response()->json([
                        "Photo" => 'No Data'
                    ]);
                }

        }catch(Throwable $ex)
        {
            return array('Massage'=>$ex->getMessage());
        }
    }
    public function PhotosFindByPrivacy(Request $request,$privacy){
        try{
            // $jwt = $request->bearerToken();
            // $decoded =(new servce)->decodeToken($jwt);
            $userId = $this->getToken($request);

            $PhotosExist = Photo::where('user_id', $userId)->first()->orwhere('privacy', $privacy)->pluck('image');
             if ($PhotosExist) {
                    return response()->json([
                        "Photo" => $PhotosExist
                    ]);
                }else{
                    return response()->json([
                        "Photo" => 'No Data'
                    ]);
                }

        }catch(Throwable $ex)
        {
            return array('Massage'=>$ex->getMessage());
        }
    }
    public function PhotosFindByTime(Request $request,$time){
        try{
            // $jwt = $request->bearerToken();
            // $decoded =(new servce)->decodeToken($jwt);
            $userId = $this->getToken($request);

            $PhotosExist = Photo::where('user_id', $userId)->where('created_at', $time)->pluck('image');
                if ($PhotosExist) {
                    return response()->json([
                        "Photo" => $PhotosExist
                    ]);
                }else{
                    return response()->json([
                        "Photo" => 'No Data'
                    ]);
                }

        }catch(Throwable $ex)
        {
            return array('Massage'=>$ex->getMessage());
        }
    }

    protected function sendPhotoLinkPrivacy(PhotoSendLinkPrivacyRequest $request)
    {
            // $jwt = $request->bearerToken();
            // $decoded =(new servce)->decodeToken($jwt);
            $userId = $this->getToken($request);
        // dd($request->image_id);

        // dd($userId);
        //    try{
        $userSender= User::where('id', $userId)->first();
        $userReceiver= User::where('email', $request->email)->first();
        if($userSender->email==$userReceiver->email){
            return response(["message" => "Email not send link"], 400);
        }
        if(isset($userReceiver)){
            
            $data=PhotoPrivacy::create([
                'user_id' => $userId,
                'email' => $request->email,
                'image_id' => $request->image_id,
            ]);
            $data->save();
            $url =url('api/photoLink/'.$request['email'].'/imageId/'.$request['image_id']);
            // dd($url);
            Mail::to($request->email)->send(new ResetPasswordMail($url,'khawars282@gmail.com'));
        
        }else{
            $message = "Email could not be sent to this email address";
            return response($message, 404);
        }
        $response = ['data'=>'','message' => 'sent to this email address'];
            return response($response, 200);
        // }catch(Throwable $ex){
        //     return array('Massage'=>$ex->getMessage());
        // }
    }
    protected function PhotoLink(Request $request,$email,$image_id){
            // $jwt = $request->bearerToken();
            // $decoded =(new servce)->decodeToken($jwt);
            $userId = $this->getToken($request);
        try{
            $userLogin= User::where('id', $userId)->first();
            if(!isset($userLogin)){
                return response()->json(["message"=>"login first"]);
                }
            // dd($userId);
            $userExist= User::where('email', $email)->first();
            // dd($userExist);
            if(!isset($userExist)){
                return response()->json(["message"=>"User not exist"]);
                }
            $photoRecever =PhotoPrivacy::where('email', $userExist->email)->where('image_id', $image_id)->first();
            if(!isset($photoRecever)){
                return response()->json(["message"=>"Photo not exist"]);
                }
            $photoExist =Photo::where('user_id', $photoRecever->user_id)->where('id', $image_id)->first();
            
            if($userLogin->email == $email && $photoExist->privacy == 'public'){
                return response()->json(['Photo'=>$photoExist->image,'privacy'=>$photoExist->privacy]);

            }else if($userLogin->email == $photoRecever->email && $photoExist->privacy == 'private'){
                return response()->json(['Photo'=>$photoExist->image,'privacy'=>$photoExist->privacy]);
            }else{
                return response()->json(['privacy'=>$photoExist->privacy,'message' => "you do not allow"]);
            }
                return response()->json(['message' => "Email and Token could not"]);
        }catch(Throwable $ex){
            return array('Massage'=>$ex->getMessage());
        }
        
        
    }
    protected function PhotoLinkAccessPrivacy(Request $request,$email,$image_id,$privacy){
        // $jwt = $request->bearerToken();
        // $decoded =(new servce)->decodeToken($jwt);
        $userId = $this->getToken($request);
    try{
        $array = array('public', 'private');
        if($array[0]!=$privacy &&  $array[1]!=$privacy){return response()->json(["message"=>"only give privacy ".$array[0].' : '.$array[1]]);}
    $userLogin= User::where('id', $userId)->first();
    if(!isset($userLogin)){
        return response()->json(["message"=>"login first"]);
        }
    $userExist= User::where('email', $email)->first();
    if(!isset($userExist)){
        return response()->json(["message"=>"User not exist"]);
        }
    $photoAdmin =PhotoPrivacy::where('user_id', $userLogin->id)->where('email', $userExist->email)->where('image_id', $image_id)->first();
    if(!isset($photoAdmin)){
        return response()->json(["message"=>"only admin access"]);
        }
    $photoExist =Photo::where('user_id', $photoAdmin->user_id)->where('id', $image_id)->first();
     if($userLogin->id == $photoAdmin->user_id && isset($photoExist)){
        $photoExist->privacy = $privacy;
        $photoExist->save();
            return response()->json(['Photo'=>$photoExist->image,'privacy'=>'Privacy Change Successfully ']);
        }else{
            return response()->json(['privacy'=>$photoExist->privacy,'message' => "Privacy Change do not allow you"]);
        }
            return response()->json(['message' => "Email and Token could not"]);
    

    }catch(Throwable $ex){
        return array('Massage'=>$ex->getMessage());
    }
    
    
}
}
