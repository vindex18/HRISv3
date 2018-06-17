<?php

namespace App\Middleware;

class ValidationErrorsMiddleware {
    function __invoke($req, $res, $next){
        var_dump('Validation Middleware Invoked!');
        //$token = $req->getAttribute("token");
        //var_dump("This is the token: ".$token);
        return $next($req, $res);
    }

    function jwtAuthentication($req, $res, $next){

    }
}