<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class servce 
{
    public function createToken($data)
    {

        $key = config('constant.secret');

        $payload = array(
            config('constant.required_jwt_data'),
            "data" => $data,
        );
        try
        {
             $jwt = JWT::encode($payload,$key, 'HS256');
             return $jwt;
        }
         catch(Exception $ex)
         {
             return array('error'=>$ex->getMessage());
         }
    }
    public function decodeToken($jwt)
         {
            $key = config('constant.secret'); 
                 
            $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        return $decoded;
    }
}
