<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoanRepayments extends Model
{
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'loan_id', 'type', 'amount_paid', 'payment_month', 'payment_date', 'description', 'transaction_detail'
   ];

   protected $guard_name = 'api';
   /**
    * The attributes that should be hidden for arrays.
    *
    * @var array
    */
   protected $hidden = [
   ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function loan()
    {
        return $this->belongsTo(\App\Loan::class);
    }
}
