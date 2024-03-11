<?php

namespace App\Traits;

use App\Models\Product;
use Carbon\Carbon;
use DB;

trait OutputWrap
{

	/**
	 * Prepare data transaction to output client
	 *
	 * @param Model $item
	 * @return array
	 */
	public static function getPrepareTransaction($item)
	{
		if (!$item) {
			return [];
		}

		$prepare = [
			'id'					=> $item->id,
			'name'					=> $item->name,
			'order_no'				=> $item->order_no,
			'order_name'			=> $item->order_name,
			'status'				=> $item->status,
			'type' => [
				'value'	=> $item->type,
				'label'	=> __("user.transaction_type.{$item->type}")
			],
			'total' 				=> $item->getItems()->sum("total"),
			'qty'  					=> $item->getItems()->sum("qty"),
			'saldo'					=> $item->transactionSaldo()->first() ? $item->transactionSaldo()->first()->amount : 0,
			'work_date'				=> [
				'raw'	=> $item->work_date,
				'long' 	=> local_date($item->work_date)
			],
			'created_date'		=> [
				'raw'	=> Carbon::parse($item->created_at)->toDateTimeString(),
				'long' 	=> local_datetime($item->created_at)
			],
			'payment_method'    => [
				'value'	=> $item->payment_method,
				'label'	=> __("user.payment_methods.{$item->payment_method}")
			],
			'payment_status'	=> [
				'value'	=> $item->payment_status,
				'label'	=> __("user.payment_statuses.{$item->payment_status}")
			],
			'items' => $item->items
		];

		$prepare['paid_date'] = [
			'raw'	=> $item->paid_at ? Carbon::parse($item->paid_at)->toDateTimeString() : null,
			'long' 	=> $item->paid_at ? local_datetime($item->paid_at) : null
		];


		return $prepare;
	}

	/**
	 * Prepare data transaction item / product to output client
	 *
	 * @param Model $item
	 * @return array
	 */
	public static function getPrepareTransactionItem($item)
	{
		if (!$item) {
			return [];
		}

		$prepare = [
			'id'					=> $item->id,
			'qty'  					=> $item->qty,
			'price'                 => $item->price,
			'total'                 => $item->total,
			'created_date'		=> [
				'raw'	=> $item->created_at,
				'long' 	=> local_datetime($item->created_at)
			]
		];

		if ($item->product_id) {
			//$product = Product::find($item->product_id);
			$product = DB::table("products")->where("id", $item->product_id)->first();
			$prepare['product'] = [
				'id'   => $item->product_id,
				'code' => $product->code,
				'name' => $product->name,
			];
		}

		return $prepare;
	}
}
