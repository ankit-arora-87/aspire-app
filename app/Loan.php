<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'application_no', 'user_id', 'type', 'requested_amount', 'duration', 'repayment_frequency', 'interest_rate', 'status', 'created_by', 'updated_by'
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
    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function loanDocuments()
    {
        return $this->hasMany(\App\LoanDocuments::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function loanLogs()
    {
        return $this->hasMany(\App\LoanLogs::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function loanRepayments()
    {
        return $this->hasMany(\App\LoanRepayments::class);
    }

}
