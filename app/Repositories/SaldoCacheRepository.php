<?php
namespace App\Repositories;

use DB;
use Log;
use Auth;
use Cache;
use App\Models\Saldo;
use App\Models\Product;
use App\Traits\Authentication;
use App\Traits\CacheStore;
use App\Interfaces\Constants;
use App\Interfaces\CacheStoreInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SaldoCacheRepository implements Constants, CacheStoreInterface {

  	use Authentication, CacheStore;

	/**
	 * Product Model
	 *
	 * @author Dian Afrial
	 * @return object
	 */
	public $product;

	/**
	 * Cache Driver
	 *
	 * @author Dian Afrial
	 * @return object
	 */
    public $saldo;

    /**
     * Unique ID
     *
     * @var [type]
     */
    protected $unique_id = 'sfsdsgs';

	/**
	 * __constructor
	 *
	 * @author Dian Afrial
	 * @return void
	 */
	public function __construct( Product $product )
	{
		// injecion class
		$this->product = $product;

		$this->cache = Cache::store( config('global.saldo.driver') );
		$this->configCreateExpiry = config('global.saldo.create_expiry');
		$this->configPutExpiry = config('global.saldo.put_expiry');
		$this->messageEmptyGet = 'Cart has been expired!';
    }

	/**
	 * Create Top-up Token Key
	 *
	 * @return void
	 */
	public function createTopupToken()
	{
		try {
			// genertate order no
			$order_no = $this->generateCacheNo();

			$data = [
				'no' 				=> time(),
				'receipt_no'		=> $order_no,
			];

			// cache add
			$this->put( $order_no, $data );

			return $order_no;

		} catch( HttpException $e ) {
			Log::info( "Create temp transaction HttpException : " . $e->getMessage() );
			abort( 400, $e->getMessage() );
		}
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
		$order_no = generate_receipt_no();
		while( null !== $this->fetchSaldoByReceiptNo($order_no) ) {
			$order_no = generate_receipt_no();
		}

		return $order_no;
	}

	protected function fetchSaldoByReceiptNo($no)
	{
		$model = Saldo::where("receipt_no", $no)->first();

		return $model;
	}

	/**
	 * Backwards compability Cart ID
	 *
	 * @author Dian Afrial
	 * @return string
	 */
	public function getKeyTransactionId($id = null)
	{
		return 'saldotopup_' . $this->unique_id;
	}
}
