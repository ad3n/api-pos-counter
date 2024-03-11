<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransactionItem extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'transaction_id',
        'product_id',
        'qty',
        'price',
        'total',
        'debit',
        'credit',
        'name'
    ];

    protected $appends = ['product'];

    /**
     * Transacton relationship
     *
     * @author Dian Afrial
     * @return object
     */
    public function transaction() : BelongsTo
    {
        return $this->belongsTo('App\Models\Transaction');
    }

    /**
     * Get last records
     *
     * @author Dian Afrial
     * @return object
     */
    public function getLastRecords($args)
    {
        extract($args);

        if (empty($date))
            $date = date('Y-m-d');

        $elq = $this->whereHas("transaction", function ($query) use ($date, $merchant_id, $employee_id) {
            $query->where("work_date", $date)->where("payment_status", 'paid');
            if ($employee_id) {
                $query->where('employee_id', $employee_id);
            }
            if ($merchant_id) {
                $query->where('merchant_id', $merchant_id);
            }
        });

        if ($type == 'debit') {
            $elq->where("debit", ">", 0);
        } else if ($type == 'credit') {
            $elq->where("credit", ">", 0);
        }

        return $elq;
    }

    public function getGroupProduct($args)
    {
        extract($args);

        $elq = $this->whereHas(
            "transaction",
            function ($query) use ($month, $year, $merchant_id, $employee_id) {
                $query->whereMonth("work_date", $month)
                    ->whereYear("work_date", $year)
                    ->where("payment_status", 'paid');

                if ($employee_id) {
                    $query->where('employee_id', $employee_id);
                }
                if ($merchant_id) {
                    $query->where('merchant_id', $merchant_id);
                }
            }
        )->groupBy("product_id");

        return $elq;
    }

    public function getProductAttribute() : BelongsTo
    {
        return $this->belongsTo('App\Models\Product', 'product_id', 'id')->first();
    }
}
