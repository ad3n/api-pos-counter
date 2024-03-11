<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Illuminate\Http\Request;
use App\Models\Super;
use Log;

class JWTAdmin extends BaseMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            $this->authenticateAdmin($request);
        } catch (TokenExpiredException $e) {
            return response()->json(error_json('Token has expired'), 401);
        } catch (UnauthorizedHttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        } catch (JWTException $e) {
            return response()->json(error_json('Token is invalid'), 400);
        }

        $request->guard = 'admin';

        return $next($request);
    }

    /**
     * Attempt to authenticate a user via the token in the request.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @throws \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     *
     * @return void
     */
    public function authenticateAdmin(Request $request)
    {
        $this->checkForToken($request);

        try {
            $user_id = $this->auth->parseToken()->getPayload()->get("sub");

            if (!Super::find($user_id)) {
                throw new UnauthorizedHttpException('jwt-auth', 'User not found');
            }
        } catch (JWTException $e) {
            throw new UnauthorizedHttpException('jwt-auth', $e->getMessage(), $e, $e->getCode());
        }
    }
}
