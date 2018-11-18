<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoanDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('loan_id')->unsigned()->index();
			$table->integer('document_id')->unsigned()->index();
			$table->datetime('created_at')->default(\DB::raw('CURRENT_TIMESTAMP'));
			$table->datetime('updated_at')->default(\DB::raw('CURRENT_TIMESTAMP'))->onUpdate(\DB::raw('CURRENT_TIMESTAMP'));
			$table->integer('created_by')->unsigned()->index();
			$table->integer('updated_by')->unsigned()->index();
			$table->foreign('loan_id')->references('id')->on('loans')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('document_id')->references('id')->on('documents')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('loan_documents');
    }
}
