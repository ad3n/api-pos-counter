<?php

namespace App\Repositories\User;

use Illuminate\Database\QueryException;
use App\Models\MerchantType;
use Carbon\Carbon;
use Log;

trait MerchantTypeTrait {

    /**
     * Get Merchant types
     *
     * @param array $request
     * @return void
     */
    public function getMerchantTypes($request)
    {
        try {
            $types = (new MerchantType)->get();
            $merchant_types = [];
            if( $types->count() > 0 ) {
                foreach($types as $i) {
                    $merchant_types[] = ['key' => $i->code, 'label' => $i->name];
                }
            }

            return $merchant_types;

        } catch(QueryException $e) {
            abort( 400, $e->getMessage() );
        }
    }

    /**
     * Fetch all merchant types
     *
     * @param Request $request
     * @return void
     */
    public function fetchMerchantTypes($request)
    {
        try {
            $types = MerchantType::withCount("merchants")->get();
            return $types;
        } catch(QueryException $e) {
            abort( 400, $e->getMessage() );
        }
    }

    /**
     * Fetch single merchant type
     *
     * @param int $id
     * @return void
     */
    public function fetchMerchantType($id)
    {
        try{
            $model = MerchantType::find($id);
            return $model;
        } catch(QueryException $e) {
            abort( 400, $e->getMessage() );
        }
    }

    /**
     * Create merchant type
     *
     * @param [type] $request
     * @return void
     */
    public function createMerchantType($request)
    {
        try {
            $name = $request->input('name');
            $code = str_slug( strtolower($name), "_");

            $res = MerchantType::create([
              'name' => $name,
              'code' => $code
            ]);

            return [
                'success' => true,
                'messages' => 'Great! new type is successfully created!'
            ];

        } catch(QueryException $e) {
            abort( 400, $e->getMessage() );
        }
    }

    /**
     * Update merchant type
     *
     * @param [type] $model
     * @param [type] $request
     * @return mixed
     */
    public function updateMerchantType($model, $request)
    {
        try {
            $name = $request->input('name');
            $code = str_slug( strtolower($name), "_");

            if (strpos($code, $model->code) === FALSE) {
                abort(400, "Name is not relevant with code, try again!");
            }

            $res = $model->fill($request->only([
                'name'
            ]))->save();

            return [
                'success' => true,
                'messages' => 'Great! the merchant type is successfully updated!'
            ];

        } catch(QueryException $e) {
            abort( 400, $e->getMessage() );
        } catch(\HttpException $e) {
            abort( $e->getStatusCode(), $e->getMessage() );
        }
    }

    /**
     * Delete merchant type
     *
     * @param int $id
     * @return void
     */
    public function deleteMerchantType($code)
    {
        $type = MerchantType::find($code);

        if( ! $type ) {
            abort(400, 'Sorry! no type found');
        }

        if( $type->merchants()->count() > 0 ) {
            abort(400, 'Sorry! the user unable to permanently deleted due to has merchants');
        }

        $type->forceDelete();

        $res = [
            'success' => true,
            'messages' => 'Success Deleted!',
        ];

        return $res;

    }
}
