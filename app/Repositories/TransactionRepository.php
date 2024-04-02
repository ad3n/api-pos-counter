<?php

namespace App\Repositories;

use Auth;
use Cache;
use Log;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\TransactionSaldo;
use App\Models\Provider;
use App\Models\Customer;
use App\Models\PaymentCreditLog;
use App\Traits\SummaryTransaction;
use App\Traits\Authentication;
use App\Traits\OutputWrap;
use App\Repositories\CartRepository;
use App\Repositories\SaldoRepository;
use App\Interfaces\Constants;
use Illuminate\Validation\ValidateException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TransactionRepository implements Constants
{
	use SummaryTransaction, Authentication, OutputWrap;

	/**
	 * Cart Repository
	 *
	 * @author Dian Afrial
	 * @return void
	 */
	protected $cart;

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
	 * Merchant ID
	 *
	 * @author Dian Afrial
	 * @return int
	 */
	protected $merchant_id;

	/**
	 * Cache Driver
	 *
	 * @author Dian Afrial
	 * @return object
	 */
	protected $cache;

	/**
	 * Count of items
	 *
	 * @var int
	 */
	protected $count_items;

    /**
	 * Saldo instance
	 *
	 * @author Dian Afrial
	 * @return mixed
	 */
	protected $saldo;

     /**
	 * Saldo instance
	 *
	 * @author Dian Afrial
	 * @return int
	 */
	protected $max_items;

	/**
	 * __constructor
	 *
	 * @author Dian Afrial
	 * @return void
	 */
	public function __construct(
		Transaction $transaction,
		Product $product,
		CartRepository $cart,
		SaldoRepository $saldo
	) {
		$this->transaction = $transaction;
		$this->product = $product;
		$this->cart = $cart;
		$this->saldo = $saldo;

		$this->cache = Cache::store(config('global.date.driver'));
		$this->max_items = config('global.transaction.count_items_max');
	}

	/**
	 * Get latest transaction
	 *
	 * @author Dian Afrial
	 * @return void
	 */
	public function latestTransaction($request)
	{
		$this->merchant_id = $this->getUserMerchant()->id;

		try {
			$res = $this->getTotalTransactionByDate($request);

			$creditArgs = ['payment_status' => 'credit', 'type' => static::TRANSACTION_TYPE_OMZET];

			if ($this->getUser()->role == 'staff') {
				$creditArgs['employee_id'] = $this->getUser()->id;
			}

			$res['transactions_credit'] = [
				'all' => [
					'count' => $this->getTransactionListByStatus($creditArgs)->count(),
					'total' => $this->getTransactionListByStatus($creditArgs)->sum(function ($collection) {
						return $collection->getItems()->sum('total');
					})
				],
				'today' => [
					'count' => $this->getCreditTransactionsByDate($this->getWorkDate(), $request)->count(),
					'total' => $this->getCreditTransactionsByDate($this->getWorkDate(), $request)->get()->sum(function ($collection) {
						return $collection->getItems()->sum('total');
					})
				]
			];

			$res['expenses'] = $this->getSummaryTotalByDate($request,$this->getWorkDate())['expense_debit'];

			$res['state'] = [
				'work_date' 	=> $this->getWorkDate(),
				'open'			=> $this->getWorkDate() ? true : false
			];
		} catch (QueryException $e) {
			Log::error("Fetch latest transaction Error SQL Query : " . $e->getMessage());
			abort($e->getCode(), $e->getMessage());
		}

		return $res;
	}

    /**
     * Provider data list
     *
     * @return mixed
     */
    public function getProvider()
    {
        try {
            $data = Provider::get();

            return $data;
        } catch (QueryException $e) {
            abort(400, $e->getMessage());
        }
    }

	/**
	 * Get All transaction
	 *
	 * @param mixed $request
	 * @return void
	 */
	public function allTransaction($request)
	{
		try {
			$this->merchant_id = $this->getUserMerchant()->id;
			// find models by request
			$models = $this->getTransactionGroupByDate($request->only(['per_page', 'offset', 'month', 'year', 'days', 'employee_id']));

			$data = [
				'filter' => [
					'year' 	=> fetch_years_increment(optional($this->getUser())->created_at),
					'month' => fetch_months()
				]
			];

			$y = $request->input('year') ? $request->input('year') : date('Y');
			$m = $request->input('month') ? $request->input('month') : date('m');
			$month_date = date("{$y}-{$m}-d");

			$data['state'] = [
				'year' => $y,
				'month' => $m,
				'display_month' => local_month($month_date, false)
			];

			if ($models->count() == 0) {
				$data['items'] = [];
				return $data;
			}

			$total_debit = $total_credit = 0;
			foreach ($models->get() as $item) {
				$transaction = Transaction::where("work_date", $item->work_date)
					->where("merchant_id", $this->merchant_id);

				if ($request->input('employee_id')) {
					$transaction = $transaction->where("employee_id", $request->input('employee_id'));
				}

				$transaction = $transaction->get();

				$credit = $this->getSummaryTotalByDate($request, $item->work_date)['omzet_credit'];
				$debit = $this->getSummaryTotalByDate($request, $item->work_date)['omzet_debit'];

				$data['items'][] = [
					'work_date'			=> [
						'raw' 	=> $item->work_date,
						'short'	=> local_date($item->work_date, true, true),
						'long'	=> local_date($item->work_date, false, true)
					],
					'credit'=> $credit,
					'debit' => $debit,
					'total' => $this->getSummaryTotalByDate($request, $item->work_date)['total_omzet'],
					'expenses' => $this->getSummaryTotalByDate($request, $item->work_date)['expense_debit']
				];

				$total_debit = $total_debit + $debit['total'];
				$total_credit = $total_credit + $credit['total'];
			}

			$data['month_total'] = [
				'credit' => $total_credit,
				'debit'	=> $total_debit,
				'total' => $total_credit - $total_debit
			];

			return $data;
		} catch (QueryException $e) {
			Log::error("Fetch all transaction Error SQL Query : " . $e->getMessage());
			abort($e->getCode(), $e->getMessage());
		}
	}

	/**
	 * Transaction list by Date
	 *
	 * @param mixed $request
	 * @return void|mixed
	 */
	public function transactionListByDate($request, $type = null)
	{
		try {
			$this->merchant_id = $this->getUserMerchant()->id;
			$args = array_merge(
                $request->only(['offset', 'per_page', 'employee_id', 'category_id']),
                ['type' => $type]
            );
			$date = $request->input('date') == 'now' ? $this->getWorkDate() : $request->input('date');
			$list = $this->getTransactionListByDate(
				$request->input('date') == 'now' ? $this->getWorkDate() : $request->input('date'),
				$args
			);

			$data = [
				'items' 	=> [],
				'success' 	=> true,
				'state'		=> [
					'date' => local_date($date),
				]
			];

			if ($list->count() == 0) {
				$data['success'] = false;
				return $data;
			}

			foreach ($list as $item) {
				$prepare = static::getPrepareTransaction($item);
                // if( $request->input('category_id') &&
                //     $prepare['items'][0]->category_id !== $request->input('category_id') ) {
                //         continue;
                // }
				$data['items'][] = array_merge(
					$item->only([
						'id', 'order_no', 'order_name', 'status', 'customer'
					]),
					$prepare
				);
			}

            if( $type !== "expense" ) {
                $data['total'] = $this->getSummaryTotalByDate($request, $date)['total_omzet'];
			    $data['transactions_credit'] = $this->getSummaryTotalByDate($request, $date)['omzet_credit'];
			    $data['transactions_debit'] = $this->getSummaryTotalByDate($request, $date)['omzet_debit'];
            }

            if( $type == "income" ) {
                $data['transfer'] = $this->getSummaryIncomeTransactionsByCategory($date, $request, [9]);
                $data['voucher'] = $this->getSummaryIncomeTransactionsByCategory($date, $request, [3]);
                $data['sp'] = $this->getSummaryIncomeTransactionsByCategory($date, $request, [4,5]);
                $data['ewallet'] = $this->getSummaryIncomeTransactionsByCategory($date, $request, [7]);
                $data['accessories'] = $this->getSummaryIncomeTransactionsByCategory($date, $request, [8]);
                $data['elektrik'] = $this->getSummaryIncomeTransactionsByCategory($date, $request, [1,3]);
                $data['withdrawal'] = $this->getExpenseWithdrawal($date, $request);
            }

            if( $type !== "income" ) {
                $data['expenses'] = $this->getSummaryTotalByDate($request, $date)['expense_debit'];
                $data['withdrawal'] = $this->getExpenseWithdrawal($date, $request);
                $data['spending'] = $this->getExpenseSpending($date, $request);
                $data['loan'] = $this->getExpenseLoan($date, $request);
                $data['cashbon'] = $this->getExpenseCashbon($date, $request);
            }

			return $data;

		} catch (QueryException $e) {
			Log::error("Fetch all transaction Error SQL Query : " . $e->getCode());
			abort($e->getCode(), $e->getMessage());
		}
	}

	/**
	 * Get total transaction by date
	 *
	 * @param [type] $request
	 * @param [type] $work_date
	 * @return mixed
	 */
	public function getSummaryTotalByDate($request, $work_date) : Array
	{
		// Get total
		$data = [];
		$total = $this->getTotalTransactionByDate($request);

		$data['omzet_credit'] = [
			'count' => $this->getCreditTransactionsByDate($work_date, $request)->count(),
			'total' => $this->getCreditTransactionsByDate($work_date, $request)->get()->sum(function ($collection) {
				return $collection->getItems()->sum('total');
			})
		];

		$data['omzet_debit'] = [
			'count' => $this->getIncomeTransactionsByDate($work_date, $request)->count(),
			'total' => $this->getIncomeTransactionsByDate($work_date, $request)->get()->sum(function ($collection) {
				return $collection->getItems()->sum('total');
			})
		];

		$data['expense_debit'] = [
			'count' => $this->getExpensePaidTransactionsByDate($work_date, $request)->count(),
			'total' => $this->getExpensePaidTransactionsByDate($work_date, $request)->get()->sum(function ($collection) {
				return $collection->getItems()->sum('total');
			})
		];

		$data['total_omzet'] = $data['omzet_debit']['total'] - $data['expense_debit']['total'];

		return $data;
	}

	public function transactionListByStatus($request)
	{
		try {
			$this->merchant_id = $this->getUserMerchant()->id;

			$args = $request->only(['offset', 'per_page', 'employee_id', 'payment_status', 'customer_id']);
			$list = $this->getTransactionListByStatus($args);

			$data = [
				'items' 	=> [],
				'success' 	=> true
			];

			if ($list->count() == 0) {
				$data['success'] = false;
				return $data;
			}

			$total = 0;
			foreach ($list as $item) {
				$prepare = static::getPrepareTransaction($item);
				$data['items'][] = array_merge(
					$item->only([
						'id', 'order_no', 'order_name', 'status', 'customer'
					]),
					$prepare
				);
				$total += $item->getItems()->sum("total");
			}

			if ($request->input('payment_status') == 'credit') {
				$data['transactions_credit'] = [
					'count' => $list->count(),
					'total' => $total
				];
			} else if ($request->input('payment_status') == 'paid') {
				$data['transactions_debit'] = [
					'count' => $list->count(),
					'total' => $total
				];
			}

			return $data;
		} catch (QueryException $e) {
			Log::error("Fetch all transaction Error SQL Query : " . $e->getMessage());
			abort($e->getCode(), $e->getMessage());
		}
	}

	/**
	 * Get Detail Transaction by passing Order No
	 *
	 * @author Dian Afrial
	 * @return mixed
	 */
	public function getDetail($request, $order_no)
	{
		try {
			$transaction = $this->transaction->where("order_no", $order_no);

			if (!$transaction->exists()) {
				abort(400, __('exception.no_exists'));
			}

			$items = $transaction->first()->getItems();

			// return Array data
			return [
				'success'		=> true,
				'order' 		=> static::getPrepareTransaction($transaction->first()),
				'items'			=> $items->map(function ($item, $key) {
					return static::getPrepareTransactionItem($item);
				})
			];
		} catch (QueryException $e) {

			Log::info("Get detail transaction SQL Query : " . $e->getMessage());

			abort(400, $e->getMessage());
		}
	}

	/**
	 * Create omzet transaction
	 *
	 * @author Dian Afrial
	 * @return mixed
	 */
	public function omzetCreateTransaction($request)
	{
		try {
			// get cart items
			$cartItems = $this->cart->getItems($request->input('order_no'));

			$order_no = $cartItems['order_no'];

			$data = [
				'order_no' 						=> $order_no,
				'order_name' 					=> __('user.defaults.order_name', ['no' => $order_no]),
				'merchant_id'					=> $this->getUserMerchant()->id,
				'status'						=> static::TRANSACTION_STATUS_SUCCESS,
				'type'							=> static::TRANSACTION_TYPE_OMZET,
				'work_date'						=> $this->getCurrentDate(),
				'payment_method' 				=> $request->input('payment_method'),
				'payment_status' 				=> $request->input('payment_status'),
			];

			if ($request->input('employee_id')) {
				$data['employee_id'] = $request->input('employee_id');
			}

			if ($request->input('customer_id')) {
				$customerModel = new Customer;
				$customer = $customerModel->find($request->input('customer_id'));
				if( $customer->credit_limit > 0 ) {
					$this->checkLimitIfAnyCustomer($request->input('items'), $customer);
				}
				$data['customer_id'] = $request->input('customer_id');
			}

			if ($request->input('payment_status') === static::PAYMENT_STATUS_PAID) {
				$data['paid_at'] = current_datetime();
			}

			$transaction = new $this->transaction;
			$transaction->fill($data)->save();

			// add product items
			if( $request->input('just_transaction') ) {
				$this->addTransactionItem($transaction->id, $request->input('items'));
			} else {
				$this->addCreditItems(
					$transaction->id,
					$request->input('items'),
					( $request->input('payment_status') === static::PAYMENT_STATUS_PAID ? 'credit' : 'debit' )
				);
			}

			// return Array data
			return [
				'order' 		=> static::getPrepareTransaction($transaction),
				'items'			=> $transaction->getItems()->map(function ($item, $key) {
					return static::getPrepareTransactionItem($item);
				})
			];
		} catch (QueryException $e) {
			Log::info("Create omzet transaction Error SQL Query : " . $e->getMessage());
			abort(400, $e->getMessage());
		} catch (HttpException $e) {
			Log::info("Create omzet transaction HttpException : " . $e->getMessage());
			abort(400, $e->getMessage());
		}
	}

	/**
	 * Create expense transaction
	 *
	 * @return mixed
	 */
	public function expenseCreateTransaction($request)
	{
		try {
			// get cart items
			$cartItems = $request->input('order_no') ? $this->cart->getItems($request->input('order_no')) : null;

			$order_no = $cartItems ? $cartItems['order_no'] : $this->cart->generateCacheNo();

			$data = [
				'order_no' 						=> $order_no,
				'merchant_id'					=> $this->getUserMerchant()->id,
				'status'						=> static::TRANSACTION_STATUS_SUCCESS,
				'type'							=> static::TRANSACTION_TYPE_EXPENSE,
				'payment_method' 				=> $request->input("payment_method"),
				'payment_status' 				=> $request->input("payment_status"),
			];

			if ($request->input('name')) {
				$data['order_name'] = $request->input('name');
                $data['name'] = $request->input('name');
			}

			if ($request->input('work_date')) {
				$data['work_date'] = $request->input('work_date');
			} else {
				$data['work_date'] = $this->getCurrentDate();
			}

			if ($request->input('supplier_id')) {
				$data['supplier_id'] = $request->input('supplier_id');
			}

            if ($request->input('employee_id')) {
				$data['employee_id'] = $request->input('employee_id');
			}

            if ($request->input('provider_id')) {
				$data['provider_id'] = $request->input('provider_id');
			}

			if ($request->input('due_date')) {
				$data['due_date'] = $request->input('due_date');
			}

            if ($request->input('expense_type')) {
				$data['expense_type'] = $request->input('expense_type');
			}

			if (static::PAYMENT_STATUS_PAID) {
				$data['paid_at'] = current_datetime();
			}

			$transaction = new $this->transaction;
			$transaction->fill($data)->save();

			// add product items
			if ($request->input('name') && $request->input('price')) {
				$this->addDebitItems($transaction->id, [
					'name' 	=> $request->input('name'),
					'qty' 	=> 1,
					'price' => $request->input('price'),
                    'total' => $request->input("total")
				], $request->input('expense_type') );
			}

			// return Array data
			return [
				'order' 		=> static::getPrepareTransaction($transaction),
				'items'			=> $transaction->getItems()->map(function ($item, $key) {
					return static::getPrepareTransactionItem($item);
				})
			];
		} catch (HttpException $e) {

			Log::info("Create expense transaction HttpException : " . $e->getMessage());
			abort(400, $e->getMessage());
		}
	}

	/**
	 * Create omzet transaction
	 *
	 * @author Dian Afrial
	 * @return mixed
	 */
	public function addTransaction($request)
	{
		try {
			// get cart items
			$order_no = $this->cart->generateCacheNo();

			$data = [
				'order_no' 						=> $order_no,
				'order_name' 					=> __('user.defaults.order_name', ['no' => $order_no]),
				'merchant_id'					=> $this->getUserMerchant()->id,
				'status'						=> static::TRANSACTION_STATUS_SUCCESS,
				'work_date'						=> $request->input('work_date') ?? $this->getCurrentDate(),
				'payment_method' 				=> $request->input('payment_method'),
				'payment_status' 				=> $request->input('payment_status'),
			];

			if ($request->input('type') == static::TRANSACTION_TYPE_OMZET ) {
				$data['type'] = static::TRANSACTION_TYPE_OMZET;
			} else {
				$data['type'] = static::TRANSACTION_TYPE_EXPENSE;
			}

			if ($request->input('employee_id')) {
				$data['employee_id'] = $request->input('employee_id');
			} else {
				$data['employee_id'] = config('global.employee.default_id');
			}

			if ($request->input('payment_status') === static::PAYMENT_STATUS_PAID) {
				$data['paid_at'] = current_datetime();
			}

            if ($request->input('provider_id')) {
				$data['provider_id'] = $request->input('provider_id');
			}

			$transaction = new $this->transaction;
			$transaction->fill($data)->save();

			// add product items
			if( $request->input('just_transaction') ) {
				$this->addTransactionItem($transaction->id, $request->input('items'), $request->input('type'));
			} else {
				$this->addCreditItems(
					$transaction->id,
					$request->input('items'),
					( $request->input('payment_status') === static::PAYMENT_STATUS_PAID ? 'credit' : 'debit' )
				);
			}

			// return Array data
			return [
				'order' 		=> static::getPrepareTransaction($transaction),
				'items'			=> $transaction->getItems()->map(function ($item, $key) {
					return static::getPrepareTransactionItem($item);
				})
			];
		} catch (QueryException $e) {
			Log::info("Create omzet transaction Error SQL Query : " . $e->getMessage());
			abort(400, $e->getMessage());
		} catch (HttpException $e) {
			Log::info("Create omzet transaction HttpException : " . $e->getMessage());
			abort(400, $e->getMessage());
		}
	}

	/**
	 * Update transaction by name
	 *
	 * @param [type] $request
	 * @param [type] $order_no
	 * @return mixed
	 */
	public function updateTransaction($request, $order_no)
	{
		$transaction = Transaction::where("order_no", $order_no)->first();

		if (!$transaction) {
			abort(400, 'Not found id transaction');
		}

		if ($transaction->type !== $request->input('type')) {
			abort(400, 'Invalid transaction type request!');
		}

		// update order name
		if ($request->input('name')) {
			$transaction->order_name = $request->input('name');
		}

		$transaction->save();

		// send response
		$res = [
			'success' 	=> true,
			'messages' 	=> 'Berhasil Update Nama Transaksi',
		];

		return $res;
	}


	/**
	 * Update transaction item by total and qty
	 *
	 * @param [type] $request
	 * @param [type] $order_no
	 * @return mixed
	 */
	public function updateTransactionItem($request, $order_no)
	{
		$transaction = Transaction::where("order_no", $order_no)->first();

		if (!$transaction) {
			abort(400, 'Not found id transaction');
		}

		if ($transaction->type !== $request->input('type')) {
			abort(400, 'Invalid transaction type request!');
		}

		// update order name

		if ($request->input('name')) {
			$transaction->name = $request->input('name');
			$transaction->save();

			// send response
			$res = [
				'success' 	=> true,
				'messages' 	=> 'Berhasil Update Nama Transaksi',
			];

			return $res;
		}

		if ($request->input('id')) {
			$item = TransactionItem::find($request->input('id'));
			if (!$item || $item->transaction_id !== $transaction->id) {
				abort(400, 'Invalid transaction request!');
			}
		}

		$data = [];
		if ($request->input('qty') > 0) {
			$data['qty'] = $request->input('qty');
			$data['total'] = $request->input('qty') * $item->price;
			if ($request->input('type') === static::TRANSACTION_TYPE_EXPENSE) {
				$data['debit'] = $request->input('qty') * $item->price;
			} else if ($request->input('type') === static::TRANSACTION_TYPE_OMZET) {
				$data['credit'] = $request->input('qty') * $item->price;
			}
		} else if ($request->input('total') && $request->input('total') > 0) {
			$data['total'] = $request->input('total');

			if ($request->input('type') === static::TRANSACTION_TYPE_EXPENSE) {
				$data['debit'] = $request->input('total');
			} else if ($request->input('type') === static::TRANSACTION_TYPE_OMZET) {
				$data['credit'] = $request->input('total');
			}
		}

		if (!empty($data)) {
			$item->fill($data)->save();
		}

		$res = [
			'success' 	=> true,
			'messages' 	=> 'Berhasil Update Data Transaksi',
			'item' 		=> static::getPrepareTransactionItem($item::find($item->id))
		];

		return $res;
	}

	/**
	 * Update transaction by order name, total
	 *
	 * @param mixed $request
	 * @param string $order_no
	 * @return mixed
	 */
	public function updateExpense($request, $order_no)
	{
		$transaction = Transaction::where("order_no", $order_no)->first();

		if (!$transaction) {
			abort(400, 'Not found id transaction');
		}

		if ($transaction->type !== 'expense') {
			abort(400, 'Invalid transaction type request!');
		}

		if ($transaction->order_name !== $request->input('name')) {
			$transaction->order_name = $request->input('name');
			$transaction->save();
		}

		if ($request->input('payment_status') && $transaction->payment_status !== $request->input('payment_status')) {
			$transaction->payment_status = $request->input('payment_status');
			$transaction->save();
		}

		$item = TransactionItem::where('transaction_id', $transaction->id)->first();

		if (!$item || $item->transaction_id !== $transaction->id) {
			abort(400, 'Invalid transaction item!');
		}

		$data = [
			'total' 	=> doubleval($request->input('price')),
			'debit'	=> doubleval($request->input('price')),
			'price'		=> doubleval($request->input('price'))
		];

		$item->fill($data)->save();

		// create response
		$res = [
			'success' 	=> true,
			'messages' 	=> 'Berhasil update expense',
		];

		return $res;
	}

	/**
	 * Delete transaction
	 *
	 * @return mixed
	 */
	public function deleteTransaction($request, $order_no)
	{
		try {
			// check saldo first
			$transaction = Transaction::where("order_no", $order_no)->first();

			if (!$transaction) {
				abort(400, 'Order Number not found');
			}

			// genertate order no
			$transaction->forceDelete();

			return [
				'success' => true,
				'messages' => 'Berhasil Hapus Order Transaksi'
			];
		} catch (HttpException $e) {
			Log::info("Delete Expense Transaction HttpException : " . $e->getMessage());
			abort(400, $e->getMessage());
		}
	}

	/**
	 * Delete transaction item
	 *
	 * @return mixed
	 */
	public function deleteTransactionItem($request, $order_no)
	{
		$model = Transaction::where("order_no", $order_no);

		if (!$model->exists()) {
			abort(400, 'Not found id transaction');
		}

		$transaction = $model->first();
		$item = TransactionItem::find($request->input('id'));

		if ($item->product_id) {
			$qty = $item->qty;
			$product = Product::find($item->product_id);
		}

		if (!$item || $item->transaction_id !== $transaction->id) {
			abort(400, 'Invalid transaction request!');
		}

		if ($transaction->type !== $request->input('type')) {
			abort(400, 'Invalid transaction type request!');
		}


		if( $model->first()->getItems()->count() === 1 ) {
			$model->first()->forceDelete();
		} else {
			$item->forceDelete();

			if ($item->product_id) {
				$product->qty = $product->qty + $qty;
				$product->save();
			}
		}

		$res = [
			'success' 	=> true,
			'messages' 	=> 'Berhasil Hapus item transaksi'
		];

		return $res;
	}

	/**
	 * Set payment status
	 *
	 * @param mixed $request
	 * @param string $order_no
	 * @return mixed
	 */
	public function setPaymentStatus($request, $order_no)
	{
		try {
			$status = $request->input('status');
			$models = Transaction::where("order_no", $order_no);

			if (!$models->exists()) {
				abort(400, 'Not found id transaction');
			}

			$model = $models->first();

			//copy attributes from original model
			$newRecord = $model->replicate();
			$newRecord->payment_status = $status;
			$newRecord->paid_at = current_datetime();
			$newRecord->save();

			// move to PaymentCreditLog
			$newLog = PaymentCreditLog::create([
				'merchant_id' => $model->merchant_id,
				'order_no' => $model->order_no,
				'customer_id' => $model->customer_id,
				'employee_id' => $model->employee_id,
				'type' => static::TRANSACTION_TYPE_OMZET,
				'total' => $model->getItems()->sum("total"),
				'payment_status' => static::PAYMENT_STATUS_PAID,
				'payment_method' => static::PAYMENT_METHOD_CASH,
				'purchased_at' => $model->created_at,
				'paid_at' => current_datetime()
			]);

			if( $model->getItems()->count() > 0 ) {
				foreach($model->getItems() as $item) {
					$newItem = $item->replicate();
					$newItem->transaction_id = $newRecord->id;
					$newItem->credit = $newItem->debit;
					$newItem->debit = 0;
					$newItem->save();
				}
			}

			if( $newLog->id ) {
				$model->forceDelete();
			}

			return [
				'success' => true,
				'messages' => 'Status Transaksi telah diubah ke ' . ucfirst($status)
			];

		} catch (HttpException $e) {
			Log::info("Change payment status transaction HttpException : " . $e->getMessage());
			abort(400, $e->getMessage());
		}
	}

	/**
	 * Create draft transaction
	 *
	 * @author Dian Afrial
	 * @return string
	 */
	public function getOrderNo()
	{
		try {
			// check saldo first
			$this->saldo->latestSaldo();

			// genertate order no
			$order_no = generate_order_no();

			return $order_no;
		} catch (HttpException $e) {

			Log::info("Create temp transaction HttpException : " . $e->getMessage());
			abort(400, $e->getMessage());
		}
	}

	protected function addTransactionItem($id, $items, $type='omzet')
	{
		foreach ($items as $item) {

			if (!isset($item['total'])) {
				continue;
			}

			$args = [
				'transaction_id' => $id,
				'price'			 => $item['total'],
				'qty'			 => 1,
				'total'		 	 => $item['total'],
				'name'			 => $item['name']
			];

			if( $type == 'expense' ) {
				$args['debit'] = $item['total'];
			} else {
				$args['credit'] = $item['total'];
			}

			TransactionItem::create($args);
		}
	}

	/**
	 * Add credit items
	 *
	 * @param int $id
	 * @param array $items
	 * @return void
	 */
	protected function checkLimitIfAnyCustomer($items, $customer)
	{
		$total = 0;
		foreach ($items as $item) {
			if (!isset($item['id'])) {
				continue;
			}

			$product2 = Product::find($item['id']);

			if ($product2 && $item['qty'] > 0) {

                $price = match ($item['custom_price']) {
                    $item['custom_price'] > 0 => 'server error',
                    default => $product2->price(),
                };

				$qty = intval($item['qty']);
				$total = $total + ($price * $qty);
			}
		}

		if( ($total + $customer->credits_total) > $customer->credit_limit ) {
			abort(400, 'Transaksi melebihi Limit yang ditentukan');
		}
	}


	/**
	 * Add credit items
	 *
	 * @param int $id
	 * @param array $items
	 * @return mixed
	 */
	protected function addCreditItems($id, $items, $type = 'credit')
	{
		foreach ($items as $item) {

			if (!isset($item['id']))
				continue;

			$product = Product::find($item['id']);

			if ($product && $item['qty'] > 0) {
				$qty = intval($item['qty']);
				$args = [
					'transaction_id' => $id,
					'product_id'	 => $item['id'],
					'qty'			 => $qty,
					'total'		 	 => $product->price() * $item['qty'],
					'name'			 => isset($item['name']) ? $item['name'] : null
				];

                if( isset($item['category_id']) && $item['category_id'] > 0 ) {
                    $args['category_id'] = $item['category_id'];
                }

				if ( isset($item['custom_price']) && $item['custom_price'] > 0 ) {
					$price = $product->type === static::PRODUCT_TYPE_PC ? $product->price() :
                        ($product->type === static::PRODUCT_TYPE_VOLUME ?
                            (intval($item['custom_price']) + $product->price()) : intval($item['custom_price']));
				} else {
					$price = $product->price();
				}

				if( isset(
                    $item['custom_price']) && $item['custom_price'] > 0 && $product->type === static::PRODUCT_TYPE_PC ) {
					$args['total'] = $price;
				} else {
					$args['total'] = $price * $item['qty'];
				}

				$args['price'] = $price;

				if( $type == 'credit' ) {
					$args['credit'] = $args['total'];
				} else if( $type == 'debit' ) {
					$args['debit'] = $args['total'];
				}

				TransactionItem::create($args);
			}

			if ($product->qty > 0 && $item['qty'] > 0) {
				$product->qty = ($product->qty - $qty);
				$product->save();
			}

			$this->count_items = $this->count_items + intval($item['qty']);
			//Log::info("Count product items " . $this->count_items);
		}
	}

	/**
	 * Add debit items
	 *
	 * @param int $id
	 * @param array $items
	 * @return void
	 */
	protected function addDebitItems($id, $items, $type)
	{
        $params = [
			'transaction_id'    => $id,
            'name'              => $items['name'],
			'qty'			    => $items['qty'],
			'price'			    => $items['price'],
			'debit'			    => $items['total'],
			'total'		 	    => $items['total'],
		];

        if( $type === "tarik_tunai" ) {
            $params['debit'] = $items['total'];
            $params['credit'] = $items['price'];
            $params['total'] =  $items['total'] - $items['price'];
        }

		TransactionItem::create($params);
	}

	/**
	 * Deduct saldo process
	 *
	 * @author Dian Afrial
	 * @return mixed
	 */
	protected function deductSaldo($id)
	{
		$saldo = $this->saldo->latestSaldo();

		$amount = $this->calculateAmount();

		$model = $this->saldo->addTransactionSaldo($id, $saldo->id, $amount);

		return $this->saldo->addUsageSaldo($saldo, $amount);
	}

	/**
	 * Backwards Calcualte amount
	 *
	 * @return integer
	 */
	protected function calculateAmount()
	{
		$multiplier = $this->count_items > $this->max_items ? ceil($this->count_items / $this->max_items) : 1;
		$amount = $multiplier * config('global.transaction.cost');

		return $amount;
	}
}
