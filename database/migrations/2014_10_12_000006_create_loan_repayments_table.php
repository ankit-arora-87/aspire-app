<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoanRepaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_repayments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('loan_id')->unsigned()->index();
            $table->string('type', 50);
            $table->decimal('amount_paid', 8,2);
			$table->integer('payment_month')->unsigned();
            $table->datetime('payment_date')->default(\DB::raw('CURRENT_TIMESTAMP'));
            $table->text('description');
            $table->string('transaction_detail', 100)->nullable();
			$table->foreign('loan_id')->references('id')->on('loans')->onUpdate('cascade')->onDelete('cascade');
			
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_repayments');
    }
}
