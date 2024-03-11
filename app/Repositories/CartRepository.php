<?php
namespace App\Repositories;

use DB;
use Log;
use Auth;
use Cache;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\CategorySelection;
use App\Traits\Authentication;
use App\Traits\CacheStore;
use App\Interfaces\Constants;
use App\Interfaces\CacheStoreInterface;
use App\Repositories\SaldoRepository;
use Illuminate\Validation\ValidateException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CartRepository implements Constants, CacheStoreInterface {

  	use Authentication, CacheStore;

	/**
	 * Transaction Model
	 *
	 * @author Dian Afrial
	 * @return void
	 */
	protected $transaction;

	/**
	 * Product Model
	 *
	 * @author Dian Afrial
	 * @return void
	 */
	protected $product;

    /**
	 * Product Model
	 *
	 * @author Dian Afrial
	 * @return mixed
	 */
	protected $saldo;

	/**
	 * __constructor
	 *
	 * @author Dian Afrial
	 * @return void
	 */
	public function __construct( Transaction $transaction, Product $product, SaldoRepository $saldo )
	{
		// injecion class
		$this->transaction = $transaction;
		$this->product = $product;
		$this->saldo = $saldo;

		$this->cache = Cache::store( config('global.cart.driver') );
		$this->configCreateExpiry = config('global.cart.create_expiry');
		$this->configPutExpiry = config('global.cart.put_expiry');
		$this->messageEmptyGet = 'Cart has been expired!';
  }

	/**
	 * Create draft omzet transaction
	 *
	 * @author Dian Afrial
	 * @return mixed
	 */
	public function omzetDraftTransaction()
	{
		try {
			// genertate order no
			$order_no = $this->generateCacheNo();

			$data = [
				'no' 				=> time(),
				'order_no'			=> $order_no,
				'type'				=> 'omzet'
			];

			// cache add
			$this->put( $order_no, $data );

			return [
				'success'   => true,
				'order_no'  => $order_no
			];

		} catch( HttpException $e ) {

			Log::info( "Create temp transaction HttpException : " . $e->getMessage() );

			abort( 400, $e->getMessage() );
		}

	}

	/**
	 * Create draft expense transaction
	 *
	 * @author Dian Afrial
	 * @return mixed
	 */
	public function expenseDraftTransaction()
	{
		try {
			// genertate order no
			$order_no = $this->generateCacheNo();

			$data = [
				'no' 				=> time(),
				'order_no'			=> $order_no,
				'type'				=> 'expense'
			];

			// cache add
			$this->put( $order_no, $data );

			return [
				'success'   => true,
				'order_no'  => $order_no
			];

		} catch( HttpException $e ) {

			Log::info( "Create temp transaction HttpException : " . $e->getMessage() );

			abort( 400, $e->getMessage() );
		}
	}

	/**
	 * Procedure add items to Cache
	 *
	 * @author Dian Afrial
	 * @return array|null
	 */
	public function addItems($order_no, $request)
	{
		if( ! $request->input('items') || empty($request->input('items')) ) {
			return;
		}

		if( ! ($stored = $this->getItems($order_no)) ) {
			$order_no = $this->omzetDraftTransaction();
			$stored = $this->getItems($order_no);
		}

		$items = [];
		foreach ( $request->input('items') as $item ) {
			$product = $this->product->findOrFail($item['id']);

			if( isset($stored['items']) ) {
				$key = array_search( $item['id'], array_column($stored['items'], 'id', 'no') );
				// dd($stored['items'][$key]);
			}

			if( isset($key) && isset($stored['items'][$key]) ) {
				$stored['items'][$key]['qty'] = intval($item['qty']);
			} else {
				$no = time();
				$items[$no]['no'] = $no;
				$items[$no]['id'] = $product->id;
				$items[$no]['qty'] = $item['qty'];
				$items[$no]['name'] = $product->name;
				$items[$no]['price'] = $product->regular_price;
				$items[$no]['added_at'] = now()->toDateTimeString();
			}
		}

		$items = collect($items);

		$storedItems = isset($stored['items']) ? $items->merge($stored['items']) : $items;

		$sorted = $storedItems->sortBy('no');

		$stored['items'] = $sorted->keyBy('no');

		$this->put( $order_no, $stored );

		return collect($stored)->all();
	}

	/**
	 * Procedure Delete cart item from Cache by Order ID and Item Cart ID
	 *
	 * @author Dian Afrial
	 * @return array
	 */
	public function deleteItem($id, $order_no)
	{
		$stored = $this->getItems($order_no);

		$items = collect($stored['items']);

		$items->pull($id);

		$newItems = $items->all();

		$stored['items'] = $newItems;

		$this->put( $order_no, $stored );

		return $stored;
	}

	/**
	 * Cart additioanals
	 *
	 * @author Dian Afrial
	 * @return array
	 */
	public function getCartProperties()
	{
		return [
			'payment_methods' => [
				[
					'key' 		=> static::PAYMENT_METHOD_CASH,
					'label' 	=> __('user.payment_methods.cash')
				],
				[
					'key' 		=> static::PAYMENT_METHOD_DEBIT,
					'label' 	=> __('user.payment_methods.debit_card')
				],
				[
					'key' 		=> static::PAYMENT_METHOD_CREDIT,
					'label'		=> __('user.payment_methods.credit_card')
				]
			],
			'payment_statuses' => [
				[
					'key' 		=> static::PAYMENT_STATUS_PAID,
					'label'		=> __('user.payment_statuses.paid')
				],
				[
					'key'			=> static::PAYMENT_STATUS_CREDIT,
					'label'		=>  __('user.payment_statuses.credit')
				]
			]
		];
	}

	/**
	 * Backwards compability and check existing On DB
	 * If no exists, create one
	 * If exists loop until no exists
	 *
	 * @return string
	 */
	public function generateCacheNo()
	{
		$order_no = generate_order_no();
		while( null !== Transaction::where("order_no", $order_no)->first() ) {
			$order_no = generate_order_no();
		}

		return $order_no;
	}

	/**
	 * backwards compability Cart ID
	 *
	 * @author Dian Afrial
	 * @return string
	 */
	public function getKeyTransactionId($id = null)
	{
		return 'cart_' . str_replace("#", "", $this->getUserMerchant()->number);
	}
}
