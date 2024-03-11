<?php

namespace App\Http\Controllers\API\v2\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Validation\ValidationException;
use App\Repositories\CartRepository;
use Illuminate\Validation\Rule;
use App\Interfaces\Constants;
use Validator;
use Auth;

class CartController extends Controller implements Constants
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
    public function __construct( CartRepository $engine )
    {
        $this->engine = $engine;
    }

    /**
     * Create draft transaction 
     *
     * @author Dian Afrial
     * @return json
     */
    public function getCart(Request $request, $order_no = null)
    {
      try {
        // validate first
        $data = $this->engine->getItems( $order_no );

        $properties = $this->engine->getCartProperties();

        // if success throw 200 OK
        return response()->json( collect($data)->merge($properties)->all(), 200);
        //return response()->json( $properties, 200);

      } catch(ValidationException $e) {
        return response()->json(error_json($e->errors()), $e->status);
      } catch(HttpException $e) {
        return response()->json(error_json($e->getMessage()), $e->getStatusCode());
      }
      
    }

    /**
     * Flush cart
     *
     * @return void
     */
    public function flushCart()
    {
      $order_no = $this->engine->flush();

      $res = [
        'success' => true,
        'messages' => 'Cart empty'
      ];

      return response()->json( $res, 200);
    }

    /**
     * Delete cart
     *
     * @param Request $request
     * @param string $order_no
     * @return void
     */
    public function deleteCart(Request $request, $order_no)
    {
      try {
         // validate first
        $this->removeValidation($request);

        // delete item process
        $data = $this->engine->deleteItem($request->input('id'), $order_no);

        // if success throw 200 OK
        return response()->json($data, 200);

      } catch(ValidationException $e) {
        return response()->json(error_json($e->errors()), $e->status);
      } catch(HttpException $e) {
        return response()->json(error_json($e->getMessage()), $e->getStatusCode());
      }
    }

    /**
     * Create draft transaction 
     *
     * @author Dian Afrial
     * @return json
     */
    public function create(Request $request, $type)
    {
      try {
         // check type transaction
        if( $type == 'omzet' ) {
            $res = $this->engine->omzetDraftTransaction();
        } else if( $type == 'expense' ) {
            $res = $this->engine->expenseDraftTransaction();
        } else {
            return response()->json( error_json('No operation exists'), 422);
        }
    
        return response()->json($res, 200);

      } catch(HttpException $e) {
        return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        
      }
     
    }

    /**
     * Add cart items
     *
     * @author Dian Afrial
     * @return json
     */
    public function addCart(Request $request, $order_no)
    {
      try {
        // process add items
        $data = $this->engine->addItems($order_no, $request);

        // return 200 OK
        return response()->json( $data, 200);

      } catch(ValidationException $e) {
        return response()->json(error_json($e->errors()), $e->status);
      } catch(HttpException $e) {
        return response()->json(error_json($e->getMessage()), $e->getStatusCode());
      }
    }

    /**
     * Validator
     *
     * @author Dian Afrial
     * @return void
     */
    public function removeValidation($request)
    {
        $validator = Validator::make( 
            $request->only("id"), 
            [
              'id' => 'required'
            ]
        );
        
        if( $validator->fails() ) {
            throw new ValidationException($validator);
        }
    }
}