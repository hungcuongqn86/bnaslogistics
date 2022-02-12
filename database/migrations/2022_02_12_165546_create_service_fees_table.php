<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_fees', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('note')->nullable();
            $table->integer('min_tot_tran')->nullable();
            $table->integer('max_tot_tran')->nullable();
            $table->double('val')->nullable()->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_fees');
    }
}
