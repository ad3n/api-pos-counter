<?php

namespace App\Http\Controllers\API\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Validation\ValidationException;
use App\Repositories\SuperRepository;
use Illuminate\Validation\Rule;
use App\Interfaces\Constants;
use Auth;
use Validator;

class SuperController extends Controller implements Constants
{   
    /**
     * Engine Repository
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
    public function __construct( SuperRepository $engine )
    {
        $this->engine = $engine;
    }

    public function getAll( Request $request ) 
    {
        try {
			// make process login
			$res = $this->engine->getUserList( $request );

			// if success throw 200 OK
			return response()->json($res, 200);

		} catch(ValidationException $e) {
			return response()->json( error_json($e->errors()), $e->status );
		} catch(HttpException $e) {
			return response()->json( error_json($e->getMessage()), $e->getStatusCode());
		}
    }

    public function createUser( Request $request ) 
    {
        try {
            $this->validation($request);

			// make process login
			$res = $this->engine->createUser( $request );

			// if success throw 200 OK
			return response()->json($res, 200);

		} catch(ValidationException $e) {
			return response()->json( error_validation_json($e->errors()), $e->status );
		} catch(HttpException $e) {
			return response()->json( error_json($e->getMessage()), $e->getStatusCode());
		}
    }

    public function updateUser( Request $request, $id ) 
    {
        try {
            // fetch first
            $user = $this->engine->fetchUser($id);

            // validate fields
            $this->updateValidation($request, $user);

			// make process login
			$res = $this->engine->updateUser( $user, $request );

			// if success throw 200 OK
			return response()->json($res, 200);

		} catch(ValidationException $e) {
			return response()->json( error_validation_json($e->errors()), $e->status );
		} catch(HttpException $e) {
			return response()->json( error_json($e->getMessage()), $e->getStatusCode());
		}
    }

    public function flagUser( Request $request, $id ) 
    {
        try {
            // fetch first
            $user = $this->engine->fetchUser($id);

            // validate fields
            $this->flagValidation($request);

			// make process login
			$res = $this->engine->setFlagUser( $user, $request );

			// if success throw 200 OK
			return response()->json($res, 200);

		} catch(ValidationException $e) {
			return response()->json( error_validation_json($e->errors()), $e->status );
		} catch(HttpException $e) {
			return response()->json( error_json($e->getMessage()), $e->getStatusCode());
		}
    }

    /**
     * Get user roles
     *
     * @return void
     */
    public function getRoles( Request $request )
    {
        try {
			// make process login
			$res = $this->engine->getRoles( $request );

			// if success throw 200 OK
			return response()->json($res, 200);

		} catch(ValidationException $e) {
			return response()->json( error_json($e->errors()), $e->status );
		} catch(HttpException $e) {
			return response()->json( error_json($e->getMessage()), $e->getStatusCode());
		}
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
            $request->all(), 
            [
                'name'              => 'required|string|min:1',
                'role_id'           => 'required|exists:roles,id',
                'phone'             => 'required|min:6|max:18|unique:supers,phone',
                'password'          => 'required|min:6|max:20',
                'email'             => 'required|email|unique:supers,email'
            ]
        );
        
        if( $validator->fails() ) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Make validation reeuest
     *
     * @author Dian Afrial
     * @return \HttpException
     */
    public function updateValidation($request, $user)
    {
        $validator = Validator::make( 
            $request->all(), 
            [
                'name'              => 'required|string|min:1',
                'role_id'           => 'required|exists:roles,id',
                'phone'             => [
                    'required',
                    'min:8',
                    'max:18',
                    Rule::unique('supers')->ignore($user->id),
                ],
                'email'             => [
                    'required',
                    'email',
                    Rule::unique('supers')->ignore($user->id),
                ]
            ]
        );
        
        if( $validator->fails() ) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Make validation reeuest
     *
     * @author Dian Afrial
     * @return \HttpException
     */
    public function flagValidation($request)
    {
        $validator = Validator::make( 
            $request->all(), 
            [
                'flag'             => [
                    'required',
                    Rule::in(['yes', 'no']),
                ]
            ]
        );
        
        if( $validator->fails() ) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    protected function guard()
    {
        return Auth::guard();
    }

}
