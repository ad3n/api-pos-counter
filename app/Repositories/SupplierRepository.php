<?php

namespace App\Repositories;

use Auth;
use Hash;
use Cache;
use Carbon\Carbon;
use App\Models\Supplier;
use App\Models\Role;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Traits\Authentication;
use App\Interfaces\Constants;
use Illuminate\Validation\ValidateException;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SupplierRepository implements Constants
{

    use Authentication;

    /**
     * User Model
     *
     * @author Dian Afrial
     * @return void
     */
    protected $supplier;

    /**
     * Merchant Model
     *
     * @author Dian Afrial
     * @return void
     */
    protected $merchant;

    /**
     * Cache Driver
     *
     * @author Dian Afrial
     * @return object
     */
    protected $cache;

    /**
     * __constructor
     *
     * @author Dian Afrial
     * @return void
     */
    public function __construct(Supplier $supplier, Merchant $merchant)
    {
        $this->supplier = $supplier;
        $this->merchant = $merchant;

        $this->cache = Cache::store(config('global.date.driver'));
    }

    public function fetchOne($id)
    {
        try {
            $user = $this->supplier->findOrFail($id);
            return $user;
        } catch (ModelNotFoundException $e) {
            abort(400, $e->getMessage());
        }
    }

    /**
     * Get supplier list
     *
     * @param mixed $request
     * @return mixed
     */
    public function getSupplierList($request)
    {
        try {
            if ($request->input('merchant_id')) {
                $users = $this->supplier->where("merchant_id", $request->input('merchant_id'))->get();
            } else {
                $users = $this->supplier->get();
            }

            return $users;
        } catch (QueryException $e) {
            abort(400, $e->getMessage());
        }
    }

    /**
     * create new supplier
     *
     * @param mixed $request
     * @return mixed
     */
    public function createNew($request)
    {
        try {
            $supplier = new $this->supplier;
            //$this->getUserMerchant()->id;
            $supplier->merchant_id = $request->input('merchant_id');
            $supplier->name = $request->input('name');
            $supplier->address = $request->input('address');
            $supplier->phone = $request->input('phone');

            // Default fill
            $supplier->country_id = 11;
            $supplier->province_id = 21;
            $supplier->regency_id = 2171;

            if ($request->input('sales_person')) {
                $supplier->sales_person = $request->input('sales_person');
            }

            if ($request->input('sales_contact')) {
                $supplier->sales_contact = $request->input('sales_contact');
            }

            if ($request->input('telp')) {
                $supplier->telp = $request->input('telp');
            }

            $supplier->save();

            $res = [
                'success'   => true,
                'messages'  => 'Great! supplier has been created!',
                'data'      => $supplier
            ];
            return $res;
        } catch (HttpException $th) {
            abort(400, $th->getMessage());
        }
    }

    public function update($id, array $data)
    {
        try {
            $supplier = $this->fetchOne($id);

            $supplier->fill($data)->save();

            $res = [
                'success'   => true,
                'messages'  => 'Great! supplier is successfully updated!'
            ];

            return $res;
        } catch (HttpException $e) {
            abort(400, $e->getMessage());
        } catch (QueryException $e) {
            abort(400, $e->getMessage());
        }
    }

    public function forceDelete($id, $request)
    {
        try {
            $supplier = $this->fetchOne($id);

            $supplier->delete();

            $res = [
                'success'   => true,
                'messages'  => 'Great! supplier is successfully deleted!'
            ];
        } catch (HttpException $th) {
            abort(400, $th->getMessage());
        }
    }

    /**
     * Get user roles
     *
     * @return array
     */
    public function getRoles()
    {
        return config('global.employee.roles');
    }
}
