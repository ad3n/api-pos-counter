<?php

namespace App\Http\Controllers\API\Tenant\Auth;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Validation\ValidationException;
use Log;
use App\Repositories\AuthRepository;
use Auth;
use App\Traits\Authentication;

class AuthController extends Controller
{
    use Authentication;

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
    public function __construct(AuthRepository $engine)
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
            $res = $this->engine->tenantLogin($request->only("no_hp", "password"));

            // if success throw 200 OK
            return response()->json($res, 200);
        } catch (ValidationException $e) {
            return response()->json($e->errors(), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
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
            $newToken = auth("employee")->refresh(true, true);;

            // if success throw 200 OK
            return response()->json([
                'success' => true,
                'token' => $newToken
            ], 200);
        } catch (JWTException $e) {
            return response()->json(error_json($e->getMessage()), $e->getCode());
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getCode());
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
        $this->engine->logoutEmployee();

        return response()->json([
            'success'   => true,
            'messages'  => __('auth.logout')
        ], 200);
    }

    /**
     * Make validation reeuest
     *
     * @author Dian Afrial
     * @return void
     */
    public function validation($request)
    {
        Log::info('log info', $request->only("no_hp", "password"));

        $validator = Validator::make(
            $request->only("no_hp", "password"),
            $this->rules()
        );

        if ($validator->fails()) {
            abort(422, __('auth.failed'));
        }
    }

    /**
     * Get the validation rules that apply to the add product image post request.
     *
     * @return array
     *-----------------------------------------------------------------------*/
    public function rules()
    {
        return [
            'no_hp'          => 'required',
            'password'       => 'required'
        ];
    }

    protected function guard()
    {
        return auth()->guard("employee");
    }
}
