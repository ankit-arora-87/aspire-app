<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoanLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_logs', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('loan_id')->unsigned()->index();
            $table->string('action', 50);
            $table->text('description')->nullable();
            $table->datetime('created_at')->default(\DB::raw('CURRENT_TIMESTAMP'));
			$table->integer('created_by')->unsigned()->index();
			$table->foreign('created_by')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');			
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
        Schema::dropIfExists('loan_logs');
    }
}
