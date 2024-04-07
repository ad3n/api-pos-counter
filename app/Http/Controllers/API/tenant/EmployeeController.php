<?php

namespace App\Http\Controllers\API\Tenant;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Validation\ValidationException;
use App\Repositories\EmployeeRepository;
use Illuminate\Validation\Rule;
use App\Interfaces\Constants;
use Auth;
use Validator;

class EmployeeController extends Controller implements Constants
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
    public function __construct(EmployeeRepository $engine)
    {
        $this->engine = $engine;
    }

    public function getAll(Request $request)
    {
        try {
            $this->engine->guard = $request->guard;
            // make process login
            $res = $this->engine->getUserList($request);

            // if success throw 200 OK
            return response()->json($res, 200);
        } catch (ValidationException $e) {
            return response()->json(error_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    public function getSession(Request $request)
    {
        try {
            $this->engine->guard = $request->guard;
            // make process login
            $res = $this->engine->getLastSession($request);

            // if success throw 200 OK
            return response()->json($res, 200);
        } catch (ValidationException $e) {
            return response()->json(error_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    /**
     * Update Session
     *
     * @param Request $request
     * @param string $state is open or close
     * @return ResponseJson
     */
    public function updateSession(Request $request, $state)
    {
        try {
            $this->engine->guard = $request->guard;
            // make update state
            $res = $this->engine->updateSession($state);

            // if success throw 200 OK
            return response()->json($res, 200);
        } catch (ValidationException $e) {
            return response()->json(error_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    public function getUser(Request $request, $id)
    {
        try {
            $this->engine->guard = $request->guard;
            // fetch first
            $userData = $this->engine->fetchUser($id);

            // if success throw 200 OK
            return response()->json($userData, 200);
        } catch (ValidationException $e) {
            return response()->json(error_validation_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }


    public function createUser(Request $request)
    {
        try {
            $this->validation($request);
            $this->engine->guard = $request->guard;
            // make process login
            $res = $this->engine->createUser($request->all());

            // if success throw 200 OK
            return response()->json($res, 200);
        } catch (ValidationException $e) {
            return response()->json(error_validation_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    public function updateUser(Request $request, $id)
    {
        try {
            $this->engine->guard = $request->guard;
            // fetch first
            $user = $this->engine->fetchUser($id);

            // validate fields
            $this->updateValidation($request, $user);

            // make process login
            $res = $this->engine->updateUserData($user->id, $request->all());

            // if success throw 200 OK
            return response()->json($res, 200);
        } catch (ValidationException $e) {
            return response()->json(error_validation_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    public function changePassword(Request $request, $id)
    {
        try {
            $this->engine->guard = $request->guard;
            // fetch first
            $user = $this->engine->fetchUser($id);

            // validate fields
            $this->changePasswordValidation($request, $user);

            // make process login
            $res = $this->engine->changePassword($user->id, $request->all());

            // if success throw 200 OK
            return response()->json($res, 200);
        } catch (ValidationException $e) {
            return response()->json(error_validation_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }


    public function flagUser(Request $request, $id)
    {
        try {
            $this->engine->guard = $request->guard;
            // fetch first
            $user = $this->engine->fetchUser($id);

            // validate fields
            $this->flagValidation($request);

            // make process login
            $res = $this->engine->setFlagUser($user, $request);

            // if success throw 200 OK
            return response()->json($res, 200);
        } catch (ValidationException $e) {
            return response()->json(error_validation_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    public function trashUser(Request $request, $id)
    {
        try {
            $this->engine->guard = $request->guard;
            // make proces
            $res = $this->engine->deleteUser($id, $request);

            // if success throw 200 OK
            return response()->json($res, 200);
        } catch (ValidationException $e) {
            return response()->json(error_validation_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    /**
     * Get user roles
     *
     * @return void
     */
    public function getRoles(Request $request)
    {
        try {
            $this->engine->guard = $request->guard;
            // make process login
            $res = $this->engine->getRoles($request);

            // if success throw 200 OK
            return response()->json((array) $res, 200);
        } catch (ValidationException $e) {
            return response()->json(error_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    /**
     * Make validation request
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
                'role'              => ['required', Rule::in(config('global.employee.roles'))],
                'no_hp'             => 'required|min:6|max:18|unique:employees,no_hp',
                'password'          => 'required|min:6|max:20',
                'email'             => 'required|email|unique:employees,email'
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Make validation request
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
                'role'              => ['required', Rule::in(config('global.employee.roles'))],
                'no_hp'             => [
                    'required',
                    'min:8',
                    'max:18',
                    Rule::unique('employees', 'no_hp')->ignore($user->id),
                ],
                'email'             => [
                    'required',
                    'email',
                    Rule::unique('employees', 'no_hp')->ignore($user->id),
                ]
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Make validation request
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

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Make validation request
     *
     * @author Dian Afrial
     * @return \HttpException
     */
    public function changePasswordValidation($request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'password'          => 'required|min:6|max:20',
                'old_password'      => 'required|min:6|max:20',
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Get guard
     *
     * @return void
     */
    protected function guard()
    {
        return Auth::guard();
    }
}
