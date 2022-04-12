<?php

namespace Integration\BaseCase\stubs\middlewares;

use SWEW\Framework\Http\Request;

final class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     */
    public function handle(Request $request, Closure $next)
    {
        $headers = [
            'Access-Control-Allow-Origin' => $_SERVER['HTTP_ORIGIN'] ?? '*',
            'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age' => '86400',
        ];
//
//        if ($request->isMethod('OPTIONS')) {
//            return $this->resp
//                response('{"method":"OPTIONS"}', 200, $headers);
//        }
//
//        $response = $next($request);
//
//        if (!empty($response)) {
//            foreach ($headers as $key => $value) {
//                $response->header($key, $value);
//            }
//        }

//        return $response;
    }
}
