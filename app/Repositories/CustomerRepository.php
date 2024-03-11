<?php

namespace App\Repositories;

use Auth;
use Hash;
use Cache;
use Carbon\Carbon;
use App\Models\Customer;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Traits\Authentication;
use App\Interfaces\Constants;
use Illuminate\Validation\ValidateException;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CustomerRepository implements Constants
{
    use Authentication;

    /**
     * User Model
     *
     * @author Dian Afrial
     * @return void
     */
    protected $customer;

    /**
     * Merchant Model
     *
     * @author Dian Afrial
     * @return void
     */
    protected $merchant;

      /**
     * Cache instance
     *
     * @author Dian Afrial
     * @return mixed
     */
    protected $cache;

    /**
     * __constructor
     *
     * @author Dian Afrial
     * @return void
     */
    public function __construct(Customer $customer, Merchant $merchant)
    {
        $this->customer = $customer;
        $this->merchant = $merchant;
        $this->cache = Cache::store(config('global.date.driver'));
    }

    public function fetchCustomer($id)
    {
        try {
            $user = $this->customer->find($id);
            return $user;
        } catch (ModelNotFoundException $e) {
            abort(400, $e->getMessage());
        }
    }

    public function fetchCustomerOfTransaction()
    {
        try {
            $customers = $this->customer->has("transactions", ">", 0)->get();
            return [
                'success'   => true,
                'data'      => $customers
            ];
        } catch (QueryException $th) {
            abort(400, $th->getMessage());
        }
    }

    public function getCustomerList($request, $offset = 0, $limit = 20)
    {
        try {
            $users = $this->customer->skip($offset)->take($limit)->get();
            return [
                'offset' => $offset,
                'limit' => $limit,
                'count' => $this->customer->count(),
                'data' => $users
            ];
        } catch (QueryException $e) {
            abort(400, $e->getMessage());
        }
    }

    public function addCustomer($request)
    {
        try {
            $newCust = new $this->customer;

            $newCust->name = $request->input("name");
            $newCust->no_hp = $request->input("no_hp");

            if( $request->input("no_hp_2") ) {
                $newCust->no_hp_2 = $request->input("no_hp_2");
            }

            if( $request->input("no_hp_3") ) {
                $newCust->no_hp_3 = $request->input("no_hp_3");
            }

            if( $request->input("pln_token") ) {
                $newCust->pln_token = $request->input("pln_token");
            }

            if( $request->input("bpjs") ) {
                $newCust->bpjs = $request->input("bpjs");
            }

            if( $request->input("gopay_va") ) {
                $newCust->gopay_va = $request->input("gopay_va");
            }

            if( $request->input("maxim_id") ) {
                $newCust->maxim_id = $request->input("maxim_id");
            }

            if( $request->input("dana_va") ) {
                $newCust->dana_va = $request->input("dana_va");
            }

            if( $request->input("ovo_va") ) {
                $newCust->ovo_va = $request->input("ovo_va");
            }

            if( $request->input("shopee_va") ) {
                $newCust->shopee_va = $request->input("shopee_va");
            }

            $newCust->note = $request->input("note");

            $newCust->created_by = $this->getUser()->id;
            // if( $request->input("credit_limit") ) {
            //     $newCust->credit_limit = $request->input("credit_limit");
            // }
            $newCust->save();

            return $newCust;

        } catch (QueryException $th) {
            abort(400, $th->getMessage());
        }
    }

    public function updateCustomer($id, $request)
    {
        try {
            $cust = $this->fetchCustomer($id);

            $cust->name = $request->input("name");
            $cust->no_hp = $request->input("no_hp");

            if( $request->input("no_hp_2") ) {
                $cust->no_hp_2 = $request->input("no_hp_2");
            }

            if( $request->input("no_hp_3") ) {
                $cust->no_hp_3 = $request->input("no_hp_3");
            }

            if( $request->input("pln_token") ) {
                $cust->pln_token = $request->input("pln_token");
            }

            if( $request->input("bpjs") ) {
                $cust->bpjs = $request->input("bpjs");
            }

            if( $request->input("gopay_va") ) {
                $cust->gopay_va = $request->input("gopay_va");
            }

            if( $request->input("maxim_id") ) {
                $cust->maxim_id = $request->input("maxim_id");
            }

            if( $request->input("dana_va") ) {
                $cust->dana_va = $request->input("dana_va");
            }

            if( $request->input("ovo_va") ) {
                $cust->ovo_va = $request->input("ovo_va");
            }

            if( $request->input("shopee_va") ) {
                $cust->shopee_va = $request->input("shopee_va");
            }


            $cust->note = $request->input("note");
            // if( $request->input("credit_limit") ) {
            //     $cust->credit_limit = $request->input("credit_limit");
            // }
            $cust->save();

            return $cust;

        } catch (QueryException $th) {
            abort(400, $th->getMessage());
        } catch ( ModelNotFoundException $th) {
            abort(400, $th->getMessage());
        }
    }

}
