<?php
namespace App\Repositories;

use Log;
use DB;
use App\Models\Saldo;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\TransactionSaldo;
use App\Traits\Authentication;
use App\Interfaces\Constants;
use App\Repositories\SaldoCacheRepository;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SaldoRepository implements Constants {

  	use Authentication;

	/**
	 * Saldo Model
	 *
	 * @author Dian Afrial
	 * @return void
	 */
	protected $saldo;

	/**
	 * Product Model
	 *
	 * @author Dian Afrial
	 * @return void
	 */
	protected $merchant;

	/**
	 * Cache Repository
	 *
	 * @author Dian Afrial
	 * @return void
	 */
	protected $cache;

	/** Default cost */
	private $backwardCost = 10;

	/**
	 * __constructor
	 *
	 * @author Dian Afrial
	 * @return void
	 */
	public function __construct( Saldo $saldo, Merchant $merchant, SaldoCacheRepository $cache )
	{
		// injecion class
		$this->saldo = $saldo;
		// injecion class
		$this->merchant = $merchant;
		$this->cache = $cache;
	}

	public function fetchSaldoByReceiptNo($no)
	{
		$model = Saldo::where("receipt_no", $no)->first();

		return $model;
	}

	/**
	 * Get merchant saldo
	 *
	 * @author Dian Afrial
	 * @return void
	 */
	public function getSaldoMerchant($id = null)
	{
		try {
			if( ! $id ) {
				$id = $this->getUserMerchant()->id;
			}

			$list = $this->saldo->where("merchant_id", $id)
								->orderBy("created_at", "desc")
								->get();

			return $list;

		} catch(ModelNotFoundException $e) {
			abort(400, $e->getMessage());
		}
	}

	/**
	 * Get Curent Saldo
	 *
	 * @author Dian Afrial
	 * @return void
	 */
	public function getCurrentSaldo()
	{
		$saldo = $this->getUsageSaldo();

		if( $saldo === FALSE ) {
			return null;
		}

		return $saldo->first();
	}

		/**
	 * Get Curent Saldo
	 *
	 * @author Dian Afrial
	 * @return void
	 */
	public function getSaldoTotal()
	{
		$saldo = $this->getUsageSaldo();

		if( $saldo === FALSE ) {
			return null;
		}

		return $saldo->sum("amount");
	}

	/**
	 * Get transaction saldo
	 *
	 * @author Dian Afrial
	 * @return void
	 */
	public function getTransactionSaldo($saldo_id)
	{
		try {
			return TransactionSaldo::where("saldo_id", $saldo_id)->get();
		} catch(ModelNotFoundException $e) {
			abort(400, $e->getMessage());
		}
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $saldo
	 * @return void
	 */
	public function getWarningUsage($saldo)
	{
		$warning = config('global.transaction.cost') * config("global.saldo.warning_usage");
		$danger = config('global.transaction.cost') * config("global.saldo.danger_usage");

		if( $saldo > $danger && $warning >= $saldo )  {
			return static::SALDO_USAGE_WARNING;
		} else if( $saldo == $danger ) {
			return static::SALDO_USAGE_DANGER;
		}

		return TRUE;
	}

	/**
	 * Top-up Saldo Procedure
	 *
	 * @author Dian Afrial
	 * @return void
	 */
	public function topup($request, $no)
	{
		$merchant = $this->merchant->find($request->input('merchant_id'));

		if( ! $merchant ) {
			abort(400, "Sorry! Merchant not found");
		}

		if( $this->fetchSaldoByReceiptNo($no) ) {
			abort(400, "Sorry! saldo with receipt no:{$no} is already exists in database");
		}

		$receipt_no = $this->cache->getItems($no);
		$admin_by = auth("admin")->user()->id;

		$data = [
			'amount' 			=> $request->input('amount'),
			'merchant_id' 		=> $request->input('merchant_id'),
			'payment_method' 	=> $request->input('method'),
			'receipt_no' 		=> $no,
			'type' 				=> $request->input('type'),
			'admin_ok_by' 		=> $admin_by
		];

		if( $request->input('method') === static::PAYMENT_METHOD_BANK ) {
			$data['status'] = 'pending';
		} else {
			$data['status'] = 'ok';
		}

		if( $request->input('note') ) {
			$data['note'] = $request->input('note');
		}

		if( $request->input('paid_date') ) {
			$data['paid_at'] = format_datetime($request->input('paid_date'));
		}

		$payment_data = [];

		if( $request->input('bank_name') ) {
			$payment_data['bank_name'] = $request->input('bank_name');
		}

		if( $request->input('bank_code') ) {
			$payment_data['bank_code'] = $request->input('bank_code');
		}

		if( $request->input('bank_swift') ) {
			$payment_data['bank_swift'] = $request->input('bank_swift');
		}

		if( $request->input('acc_holder') ) {
			$payment_data['acc_holder'] = $request->input('acc_holder');
		}

		if( $request->input('acc_number') ) {
			$payment_data['acc_number'] = $request->input('acc_number');
		}

		if( ! empty($payment_data) ) {
			$data['payment_data'] = collect($payment_data)->toJson();
		}

		$this->saldo->fill($data)->save();

		return true;
	}

	/**
	 * Add record transaction saldo
	 *
	 * @author Dian Afrial
	 * @return void
	 */
	public function addTransactionSaldo($transaction_id, $saldo_id, $amount = null)
	{
		return TransactionSaldo::create([
			'saldo_id' 				=> $saldo_id,
			'transaction_id' 		=> $transaction_id,
			'amount'				=> $amount ? $amount : config('config.transaction.cost')
		]);
	}

	/**
	 * Get latest saldo
	 *
	 * @author Dian Afrial
	 * @return integer|Exception
	 */
	public function latestSaldo()
	{
		$saldo = $this->getUsageSaldo();

		if( $saldo === FALSE ) {
			throw new HttpException(422, __('exception.no_saldo') );
		}

		if( ( $saldoWillUse = $this->isLimitTransaction( $saldo->first() ) ) !== FALSE ) {
			return $saldoWillUse;
		}

		throw new HttpException(422, __('exception.no_enough_saldo') );
	}

	public function checkSaldo()
	{
		$saldo = $this->getUsageSaldo();

		if( $saldo !==FALSE ) {
			if( ( $saldoWillUse = $this->isLimitTransaction( $saldo->first() ) ) !== FALSE ) {
				return $saldoWillUse;
			}
		}

		throw new HttpException(422, __('exception.no_enough_saldo') );
	}

	/**
	 * Check whether saldo limit or not
	 *
	 * @author Dian Afrial
	 * @return boolean
	 */
	public function isLimitTransaction($latestTopUp)
	{
		$lastUsageSaldo = $latestTopUp->usage;

		if( $latestTopUp->amount === $lastUsageSaldo ) {
			if( $this->getNextUsageSaldo() !== FALSE ) {
				return $this->getNextUsageSaldo()->first();
			} else {
				return FALSE;
			}
		}

		$enoughCheck = doubleval( $latestTopUp->amount - $lastUsageSaldo );

		if( $enoughCheck >= config('global.transaction.cost') ) {
			return $latestTopUp;
		}

		if( $enoughCheck > 0 && $enoughCheck < config('global.transaction.cost') )
		{
			if( $this->getNextUsageSaldo([$latestTopUp->id]) !== FALSE ) {
				return $latestTopUp;
			}
		}

		return FALSE;
	}

	/**
	 * Get top usage saldo
	 *
	 * @author Dian Afrial
	 * @return FALSE|Object
	 */
	public function getUsageSaldo()
	{
		$usageSaldo = $this->saldo
						->where("merchant_id", $this->getUserMerchant()->id)
						->whereNull('closed_at')
						->orderBy("created_at", "asc");

		return $usageSaldo->exists() ? $usageSaldo : false;
	}

	/**
	 * Get next usage saldo if current is limit
	 *
	 * @author Dian Afrial
	 * @return FALSE|Object
	 */
	public function getNextUsageSaldo( $except_ids = [] )
	{
		$nextUsageSaldo = $this->saldo
							->where("merchant_id", $this->getUserMerchant()->id)
							->where("usage", "=", "0.00" )
							->whereNotNull("closed_at");

		if( ! empty($except_ids) ) {
			$nextUsageSaldo = $nextUsageSaldo->whereNotIn('id', $except_ids);
		}

		$nextUsageSaldo = $nextUsageSaldo->orderBy("id", "desc");

		return $nextUsageSaldo->exists() ? $nextUsageSaldo : false;
	}

	/**
	 * Add usage saldo of Current saldo use
	 *
	 * @param \App\Models\Saldo $saldo
	 * @param int $amount
	 * @return mixed
	 */
	public function addUsageSaldo($saldo, $amount)
	{
		if( ! $saldo instanceof Saldo ) {
			throw new HttpException(400, 'Wrong Saldo Model');
		}

		// e.g. usage 110 + 10 = 120
		$usage = doubleval($saldo->usage + $amount);

		Log::info("start add usage saldo of Saldo ID : " .  $saldo->id . " usage : " . ( $saldo->amount % $usage ) );

		// test whether usage saldo enough or not
		// e.g. saldo amount 120 - 120 > 10
		if( ( $saldo->amount - $usage ) >= $amount ) {
			$saldo->usage = $usage;
			$saldo->save();

			Log::info("Current saldo enough Saldo ID : " .  $saldo->id . " usage : " . $usage);

		} else if ( ( $saldo->amount - $usage ) == 0 ) {

			$saldo->usage = $saldo->amount;
			$saldo->closed_at = current_datetime();
			$saldo->save();

			Log::info("Close saldo with 0 Saldo ID : " .  $saldo->id);

		// e.g. 120 - 120 > 0 && 120 - 120 < 10
		} else if ( ( $saldo->amount - $usage ) > 0 AND ( $saldo->amount - $usage ) < $amount ) {

			$saldo->usage = $usage;
			$saldo->save();

			Log::info("After usage saldo with 0 Saldo ID : " .  $saldo->id);

			// e.g. 120 - 114 - 5
			$leftovers = ( $saldo->amount - $usage ) - $amount;

			if( $leftovers > 0 ) {
				if( $this->getNextUsageSaldo()->first() ) {
					$nextSaldo = $this->getNextUsageSaldo()->first();
					$nextSaldo->usage = abs($leftovers);
					$nextSaldo->save();
				}
				Log::info("No enough saldo, point next saldo with next Saldo ID : " .  $nextSaldo->id);
			}
		}

		return TRUE;

	}

	public function getReceiptNo()
	{
		$no = 'R-' . mt_rand();
		while( null !== Saldo::where("receipt_no", $no)->first() ) {
			$no = 'R-' . mt_rand();
		}

		return $no;
	}

}
