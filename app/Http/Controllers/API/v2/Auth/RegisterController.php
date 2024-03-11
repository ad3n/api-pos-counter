<?php

namespace App\Http\Controllers\API\v2\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Validation\ValidationException;
use App\Repositories\AuthRepository;
use Illuminate\Validation\Rule;
use App\Interfaces\Constants;
use Auth;
use Validator;

class RegisterController extends Controller
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
     * Make register
     *
     * @author Dian Afrial
     * @return void
     */
    public function postRegister(Request $request)
    {
        try {
            // validate first
            $this->validation($request);

            // make process login
            $res = $this->engine->userRegister( $request );

            // if success throw 200 OK
            return response()->json($res, 200);

        } catch(ValidationException $e) {
            return response()->json( error_json($e->errors()), $e->status );
        } catch(HttpException $e) {
            return response()->json( error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    /**
     * Get Register data
     *
     * @return void
     */
    public function getRegister()
    {
        $data = $this->engine->getMerchantTypes();

        return response()->json(['success' => true, 'merchant_type' => $data]);
    }

    /**
     * Validator
     *
     * @author Dian Afrial
     * @return void
     */
    public function validation($request)
    {
        $validator = Validator::make( 
            $request->only( $this->fields() ), 
            $this->rules() 
        );
        
        if( $validator->fails() ) {
            //abort(422, __('auth.failed'));
            throw new ValidationException($validator);
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
        'merchant_name'          => 'required',
        'merchant_type'          => 'required|exists:merchant_types,code',
        'phone'                  => 'required|min:10|max:20',
        'email'                  => 'nullable|email',
        'name'                   => 'required',
        'address'                => 'required',
        'open_at'                => 'required',
        'closed_at'              => 'required',
        'country_id'             => 'required',
        'province_id'            => 'required',
        'regency_id'             => 'required'
      ];
    }

    /**
     * Get fields
     *
     * @author Dian Afrial
     * @return void
     */
    protected function fields() 
    {
      return [
        "merchant_name", 
        "merchant_type", 
        "phone", 
        "email",
        'name',
        'address',
        'open_at',
        'closed_at',
        'country_id',
        'province_id',
        'regency_id'
      ];
    }

    protected function guard()
    {
        return Auth::guard();
    }

}
