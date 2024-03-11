<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Repositories\AuthRepository;
use Auth;

class AuthController extends Controller
{   
    /**
     * Engoine Auth Repository
     *
     * @author Dian Afrial
     * @return object
     */
    protected $engine;

    /**
     * __constuctor
     *
     * @author Dian Afrial
     * @return void
     */
    public function __construct(AuthRepository $engine )
    {
        $this->engine = $engine;
    }

    /**
     * Make auth func
     *
     * @author Dian Afrial
     * @return void
     */
    public function authenticate(AuthRequest $request)
    {
        try {
            $res = $this->engine->userLogin( $request->only("phone", "password") );
            return response()->json($res, 200);
        } catch(JWTException $e) {
            return response()->json( error_json($e->getMessage()), 401);
        } catch(HttpException $e) {
            return response()->json( error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    protected function guard()
    {
        return Auth::guard('api');
    }

}
