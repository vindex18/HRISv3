<?php

namespace App\Middleware;
use \Firebase\JWT\JWT;
use \Tuupola\Base62;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class AuthMiddleware {
    function __construct(){
        //var_dump(urlencode(base64_encode(2)));
        //var_dump('Auth Middleware Constructed!<br>');
    }

    function __invoke(Request $req, Response $res, $next){
        var_dump('Auth Middleware Invoked!<br>');
        //$token = $req->getAttribute("token");
        //var_dump("This is the token: ".$token);
        return $next($req, $res);
    }
    
    function jwtSecret(){
        return "Q!w12x2512g1c2a4Wa23Kpb752x&95z*";
    }

    function validatejwt(Request $req, Response $res){
        //$key = "Q!w12x2512g1c2a4Wa23Kpb752x&95z*";
        //$jwt = JWT::encode($payload, $secret, "HS256");
        //$decoded = JWT::decode($jwt, $key, array('HS256'));
        /*print_r($decoded);
        die();
        */
    }
}