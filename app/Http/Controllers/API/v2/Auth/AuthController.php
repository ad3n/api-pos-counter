<?php

namespace App\Http\Controllers\API\v2\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Validation\ValidationException;
use App\Repositories\AuthRepository;
use Auth;
use Validator;

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
    public function authenticate(Request $request)
    {
        try {
            // validate first
            $this->validation($request);

            // make process login
            $res = $this->engine->userLogin( $request->only("phone"), $request );

            // if success throw 200 OK
            return response()->json($res, 200);

        } catch(ValidationException $e) {
            return response()->json( $e->errors(), $e->status );
        } catch(HttpException $e) {
            return response()->json( error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    /**
     * Make auth func
     *
     * @author Dian Afrial
     * @return void
     */
    public function refresh(Request $request)
    {
        try {
            // make process login
            $newToken = auth()->refresh(true, true);;

            // if success throw 200 OK
            return response()->json([
                'success' => true,
                'token' => $newToken
            ], 200);

        } catch(JWTException $e) {
            return response()->json( error_json($e->getMessage()), $e->getStatusCode() );
        } catch(HttpException $e) {
            return response()->json( error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    /**
     * Logout current user
     *
     * @author Dian Afrial
     * @return void
     */
    public function logout(Request $request)
    {
        auth()->logout(true);

        return response()->json([ 
            'success'   => true,
            'messages'  => __('user.logout') 
        ], 200 );
    }

    /**
     * Make validation reeuest
     *
     * @author Dian Afrial
     * @return \HttpException
     */
    public function validation($request)
    {
      
        $validator = Validator::make( 
            $request->only("phone", "device_no", "ip_address", "app_version"), 
            $this->rules() 
        );
        
        if( $validator->fails() ) {
            abort(422, __('auth.failed'));
        }
    }

    /**
     * Get the validation rules that apply to the add product image post request.
     *
     * @return bool
     *-----------------------------------------------------------------------*/
    public function rules()
    {
      return [
        'phone'          => 'required',
        'device_no'      => 'required',
        'ip_address'     => 'required',
        'app_version'    => 'required'
      ];
    }

    protected function guard()
    {
        return Auth::guard();
    }

}
