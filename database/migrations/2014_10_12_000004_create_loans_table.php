<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('user_id')->unsigned()->index();
            $table->string('application_no', 50)->unique();
            $table->string('type', 50);
            $table->text('description')->nullable();
            $table->decimal('requested_amount', 8, 2);
            $table->decimal('approved_amount', 8, 2)->nullable();	
			$table->integer('duration')->unsigned();	
			$table->decimal('interest_rate', 4, 2);
			$table->integer('repayment_frequency')->unsigned();
			$table->string('status', 20);	
            $table->datetime('created_at')->default(\DB::raw('CURRENT_TIMESTAMP'));
			$table->datetime('updated_at')->default(\DB::raw('CURRENT_TIMESTAMP'))->onUpdate(\DB::raw('CURRENT_TIMESTAMP'));
			$table->integer('created_by')->unsigned()->index();
			$table->integer('updated_by')->unsigned()->index();
			$table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('created_by')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('updated_by')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
			
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loans');
    }
}
