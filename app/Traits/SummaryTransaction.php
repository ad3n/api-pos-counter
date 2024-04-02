<?php

namespace App\Traits;

use App\Models\TransactionItem;
use App\Models\Transaction;
use Carbon\Carbon;
use phpDocumentor\Reflection\Types\Integer;
use Log;
trait SummaryTransaction
{
  /**
   * Get last transaction date
   *
   * @author Dian Afrial
   * @return Array
   */
  public function getTotalTransactionByDate($request) : Array
  {
    $transactionItemModel = new TransactionItem;

    $date = $request->input('date');
    if (empty($date) || $date == 'latest') {
      $date = $this->getWorkDate() ? $this->getWorkDate() : date('Y-m-d');
    }

    $debit = $transactionItemModel->getLastRecords([
      'date'        => $date,
      'merchant_id' => $this->merchant_id,
      'employee_id' => $request->input('employee_id') ? $request->input('employee_id') : null,
      'type'        => 'debit',
    ])->sum("debit");

    $debit_count = $transactionItemModel->getLastRecords([
      'date'        => $date,
      'merchant_id' => $this->merchant_id,
      'type'        => 'debit',
      'employee_id' =>  $request->input('employee_id') ? $request->input('employee_id') : null
    ])->count();

    $credit = $transactionItemModel->getLastRecords([
      'date'        => $date,
      'merchant_id' => $this->merchant_id,
      'type'        => 'credit',
      'employee_id' => $request->input('employee_id') ? $request->input('employee_id') : null
    ])->sum("credit");

    $credit_count = $transactionItemModel->getLastRecords([
      'date'        => $date,
      'merchant_id' => $this->merchant_id,
      'type'        => 'credit',
      'employee_id' =>  $request->input('employee_id') ? $request->input('employee_id') : null
    ])->count();

    return [
      'all' => $credit - $debit,
      'debit' => [
        'count' => $debit_count,
        'total' => $debit
      ],
      'credit' => [
        'count' => $credit_count,
        'total' => $credit
      ]
    ];
  }

  /**
   * Get transaction list by date
   *
   * @author Dian Afrial
   * @return object
   */
  public function getTransactionListByDate($date = '', array $args) : Object
  {
    $defaults = [
      'offset'        => 0,
      'per_page'      => 20,
      'type'          => null,
      'employee_id'   => null
    ];

    $args = array_merge($defaults, $args);

    if (empty($date)) {
      $date = date('Y-m-d');
    }

    $transactionModel = new Transaction;

    $listModel = $transactionModel->getDateRecords($date)
        ->where("merchant_id", $this->merchant_id)
        ->orderBy("created_at", 'desc');

    if ($args['employee_id']) {
        $listModel = $listModel->where("employee_id",  $args['employee_id']);
    }

    if ($args['type'] !== null) {
        $listModel = $listModel->where("type", $args['type']);
    }

    if (isset($args['category_id'])) {
        $cat_id = $args['category_id'];
        $listModel = $listModel->whereHas("transactionItem", function($query) use ($cat_id) {
            $query->where("category_id", $cat_id );
        });
    }

    if( $args['per_page'] > -1 ) {
        if ($args['offset'] > -1) {
            $listModel = $listModel->offset($args['offset']);
        }

        $listModel = $listModel->take($args['per_page'] > 0 ? $args['per_page'] : 10);
    }

    return $listModel->get();
  }

  /**
   * Get transaction list by status
   *
   * @author Dian Afrial
   * @return object
   */
  public function getTransactionListByStatus(array $args) : Object
  {
    $defaults = [
      'offset'    => 0,
      'per_page'  => 20,
      'type'      => null,
      'employee_id'  => null,
      'payment_status' => null,
      'customer_id' => null,
    ];

    $args = array_merge($defaults, $args);

    $transactionModel = new Transaction;

    $listModel = $transactionModel->where("merchant_id", $this->merchant_id)
      ->orderBy("created_at", 'desc');

    if ($args['payment_status']) {
      $listModel = $listModel->where("payment_status",  $args['payment_status']);
    }

    if ($args['employee_id']) {
      $listModel = $listModel->where("employee_id",  $args['employee_id']);
    }

    if ($args['type'] !== null) {
      $listModel = $listModel->where("type",  $args['type']);
    }

    if ($args['customer_id'] !== null) {
      $listModel = $listModel->where("customer_id",  $args['customer_id']);
    }

    if ($args['offset'] > -1) {
      $listModel = $listModel->offset($args['offset']);
    }

    $listModel = $listModel->take($args['per_page'] > 0 ? $args['per_page'] : 10);

    return $listModel->get();
  }

  /**
   * Get last transaction date
   *
   * @author Dian Afrial
   * @return object
   */
  public function getCreditTransactionsByDate($date = '', $request) : Object
  {
    $transactionModel = new Transaction;

    $listModel = $transactionModel->getDateRecords($date)
      ->where("merchant_id", $this->merchant_id)
      ->where("payment_status", 'credit')
      ->where("type", 'income')
      ->whereNull("paid_at");

    if ($request->input('employee_id')) {
      $listModel = $listModel->where('employee_id', $request->input('employee_id'));
    }

    if ($request->input('category_id')) {
        $cat_id = $request->input('category_id');
        $listModel = $listModel->whereHas("transactionItem", function($query) use ($cat_id) {
            $query->where("category_id", $cat_id );
        });
    }

    return $listModel;
  }

  /**
   * Get debit transaction by date
   *
   * @author Dian Afrial
   * @return mixed
   */
  public function getIncomeTransactionsByDate($date = '', $request) : Object
  {
    $transactionModel = new Transaction;

    // $cat_id = $request->input('category_id');
    // $listModel = $transactionModel->whereHas("transactionItem", function($query) use ($cat_id) {
    //     $query->where("category_id", $cat_id );
    // });

    $listModel = $transactionModel->where("work_date", $date)
      ->where("merchant_id", $this->merchant_id)
      ->where("payment_status", 'paid')
      ->where("type", 'income')
      ->whereNotNull("paid_at");

    if ($request->input('employee_id')) {
        $listModel = $listModel->where('employee_id', $request->input('employee_id'));
    }

    if ($request->input('category_id')) {
        $cat_id = $request->input('category_id');
        $listModel = $listModel->whereHas("transactionItem", function($query) use ($cat_id) {
            $query->where("category_id", $cat_id );
        });
    }

    return $listModel;
  }

  public function getIncomeTransactionsByCategory($date = '', $request, $cat_ids) : Object
  {
    $transactionModel = new Transaction;

    $listModel = $transactionModel->where("work_date", $date)
        ->where("merchant_id", $this->merchant_id)
        ->where("payment_status", 'paid')
        ->where("type", 'income')
        ->whereNotNull("paid_at");

    if( $cat_ids ) {
        $listModel = $listModel->whereHas("transactionItem", function($query) use ($cat_ids) {
            $query->where("category_id", $cat_ids );
        });
    }

    if ($request->input('employee_id')) {
      $listModel = $listModel->where('employee_id', $request->input('employee_id'));
    }

    return $listModel;
  }

  public function getSummaryIncomeTransactionsByCategory($date = '', $request, $cat_ids)
  {
    return [
        'count' => $this->getIncomeTransactionsByCategory($date, $request, $cat_ids)->count(),
        'total' => $this->getIncomeTransactionsByCategory($date, $request, $cat_ids)->get()->sum(function ($collection) {
            return $collection->getItems()->sum('total');
        })
    ];
  }

  /**
   * Get debit transaction by date
   *
   * @author Dian Afrial
   * @return mixed
   */
  public function getExpensePaidTransactionsByDate($date = '', $request) : Object
  {
    $transactionModel = new Transaction;

    $listModel = $transactionModel->getDateRecords($date)
      ->where("merchant_id", $this->merchant_id)
      ->where("payment_status", 'paid')
      ->where("type", 'expense')
      ->whereNotNull("paid_at");

    if ($request->input('employee_id')) {
      $listModel = $listModel->where('employee_id', $request->input('employee_id'));
    }

    return $listModel;
  }

   /**
   * Get withdrawal expense transaction by date
   *
   * @author Dian Afrial
   * @return mixed
   */
  public function getExpenseTransactionsByDate($date = '', $request, $type = "tarik_tunai") : Object
  {
    $transactionModel = new Transaction;

    $listModel = $transactionModel->getDateRecords($date)
        ->where("merchant_id", $this->merchant_id)
        ->where("payment_status", 'paid')
        ->where("type", "expense")
        ->where("expense_type", $type)
        ->whereNotNull("paid_at");

    if ($request->input('employee_id')) {
        $listModel = $listModel->where('employee_id', $request->input('employee_id'));
    }

    return $listModel;
  }

  /**
   * Get last transaction date
   *
   * @author Dian Afrial
   * @return object
   */
  public function getTransactionGroupByDate(array $args): Object
  {
    $defaults = [
      'employee_id' => null,
      'offset'    => 0,
      'per_page'  => 10,
      'month'     => date('m'),
      'year'      => date('Y'),
    ];

    $args = array_merge($defaults, $args);

    $transactionModel = (new Transaction)->where("merchant_id", $this->merchant_id)
        ->groupBy("work_date")
        ->orderBy("created_at", "desc");

    if (isset($args['days']) && $args['days'] > 0) {
      $date = Carbon::today()->subDays($args['days']);
      $transactionModel = $transactionModel->where("work_date", '>=', $date);
    } else {
      if (abs($args['month']) > 0 && abs($args['year']) > 2018) {
        $transactionModel = $transactionModel->whereMonth("work_date", $args['month'])
          ->whereYear("work_date", $args['year']);
      }
    }

    if ($args['employee_id'] > 0) {
      $transactionModel = $transactionModel->where("employee_id", $args['employee_id']);
    }

    if ($args['offset'] > -1) {
      $transactionModel = $transactionModel->offset($args['offset']);
    }

    $transactionModel = $transactionModel->take($args['per_page'] > 0 ? $args['per_page'] : 10);
    return $transactionModel;
  }

  /**
   * Get last transaction date
   *
   * @author Dian Afrial
   * @return void
   */
  public function getCreditTransactionGroupCountByDate(array $args) : Integer
  {
    $transactionModel = (new Transaction)->where("merchant_id", $this->merchant_id)
      ->groupBy("work_date")
      ->orderBy("created_at", "desc");

    if (abs($args['month']) > 0 && abs($args['year']) > 2017) {
      $transactionModel = $transactionModel->whereMonth("work_date", $args['month'])
        ->whereYear("work_date", $args['year']);
    }

    $transactionModel = $transactionModel->where("payment_status", "credit");

    return $transactionModel->count();
  }

    public function getTopList($request)
    {
        $transactionItemModel = new TransactionItem;

        $defaults = [
            'employee_id' => null,
            'month'       => date('m'),
            'year'        => date('Y'),
            'merchant_id' => $this->getUserMerchant()->id
        ];

        $args = array_merge($defaults, $request->all());

        return $transactionItemModel->getGroupProduct($args);
    }

    public function getExpenseWithdrawal($date, $request)
    {
        return [
            'count' => $this->getExpenseTransactionsByDate($date, $request, 'tarik_tunai')->count(),
            'total' => $this->getExpenseTransactionsByDate($date, $request, 'tarik_tunai')->get()->sum(function ($collection) {
                return $collection->getItems()->sum('total');
            }),
            'profit' => $this->getExpenseTransactionsByDate($date, $request, 'tarik_tunai')->get()->sum(function ($collection) {
                return $collection->getItems()->sum('credit');
            })
        ];
    }

    public function getExpenseSpending($date, $request)
    {
        return [
            'count' => $this->getExpenseTransactionsByDate($date, $request, 'belanja')->count(),
            'total' => $this->getExpenseTransactionsByDate($date, $request, 'belanja')->get()->sum(function ($collection) {
                return $collection->getItems()->sum('total');
            })
        ];
    }

    public function getExpenseLoan($date, $request)
    {
        return [
            'count' => $this->getExpenseTransactionsByDate($date, $request, 'pinjaman')->count(),
            'total' => $this->getExpenseTransactionsByDate($date, $request, 'pinjaman')->get()->sum(function ($collection) {
                return $collection->getItems()->sum('total');
            })
        ];
    }

    public function getExpenseCashbon($date, $request)
    {
        return [
            'count' => $this->getExpenseTransactionsByDate($date, $request, 'kasbon')->count(),
            'total' => $this->getExpenseTransactionsByDate($date, $request, 'kasbon')->get()->sum(function ($collection) {
                return $collection->getItems()->sum('total');
            })
        ];
    }
}
