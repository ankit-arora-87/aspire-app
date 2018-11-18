<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoanDocuments extends Model
{
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'loan_id', 'document_id', 'created_by', 'updated_by'
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function loan()
    {
        return $this->belongsTo(\App\Loan::class);
    }
}
