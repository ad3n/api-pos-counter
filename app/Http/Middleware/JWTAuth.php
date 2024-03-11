<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Log;

class JWTAuth extends BaseMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            $this->authenticate($request);
        } catch (TokenExpiredException $e) {
            return response()->json(error_json('Token has expired'), 401);
        } catch (UnauthorizedHttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        } catch (JWTException $e) {
            return response()->json(error_json('Token is invalid'), 400);
        }

        $request->guard = 'api';

        return $next($request);
    }
}
