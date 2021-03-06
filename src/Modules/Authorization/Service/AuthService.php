<?php

namespace App\Modules\Authorization\Service;
use App\Modules\Authorization\Models\AuthModel;
use Respect\Validation\Validator as v;
use App\Utils\Validator;
use \Datetime;
use \Firebase\JWT\JWT;
use \Tuupola\Base62;
use App\Middleware\Auth;

class AuthService {
    function validatecredentials($req, $res){
        //return $req->getParams(); 
        //return $req->getParam('email');

        $validation = Validator::validate($req, [
            'email' => v::noWhitespace()->notEmpty()->email(),
            'password' => v::noWhitespace()->notEmpty() //->length(6, null),
        ]); 
        
        if(!is_null($validation))
            return $validation;
            
        $userexists = AuthModel::where('email', '=', strip_tags($req->getParam('email')))->first();

        if(is_null($userexists))
            return array('status' => false, 'msg' => 'Email doesn\'t exists!', 'tk' => '');
        else{
            if($userexists->is_active)
            {
                if(password_verify(strip_tags($req->getParam('password')), $userexists->password)){
                    //var_dump($userexists); die();
                    //$last_punch = AttendanceDao::getLastPunch($userexists->);
                    return array('status' => true, 
                                 'msg' => 'Auth Success', 
                                 'tk' => AuthService::generate_jwt($req, $res, $userexists->email, $userexists));
                }
                else
                    //var_dump("User Authentication Failed!");
                    return array('status' => false, 'msg' => 'Incorrect Email/Password!', 'tk' => '');
            }
            else
            {
                //var_dump('User Account Deactivated');
                return array('status' => false, 'msg' => 'Account Deactivated! Contact The Administrator!', 'tk' => ''); 
            }
        }
    }

    function generate_jwt($req, $res, $email, $userexists){
        $now = new DateTime();
        //$future = new DateTime("now +2 hours");
        $future = new DateTime("now +2 months"); //+1 day
        $base62 = new \Tuupola\Base62;

        $payload = [
            'iat' => $now->getTimeStamp(), //issued at
             //'exp' => $now->createFromFormat('d/m/Y H:i:s', '23/05/2013'), 
            'exp' => $future->getTimeStamp(), //expiration
            'jti' => $base62->encode(random_bytes(32)), //json token id
            'sub' => $base62->encode($email), //subject
            'iss' => $base62->encode("invento-hris"), //issuer
            'firstname' => base64_encode($userexists->first_name),
            'middlename' => base64_encode($userexists->middle_name),
            'lastname' => base64_encode($userexists->last_name),
            'email' => base64_encode($userexists->email),
            'position' => base64_encode($userexists->pos_title),
            'sid' => base64_encode($userexists->id)
        ];

        $secret = Auth::jwtSecret();
        return JWT::encode($payload, $secret, "HS256");
        /*$jwt = JWT::encode($payload, $secret, "HS256");
        $decoded = JWT::decode($jwt, $secret, array('HS256'));
        print_r($decoded);
        die();*/
    }

}