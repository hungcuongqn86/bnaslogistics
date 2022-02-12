<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransportFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transport_fees', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('type');
            $table->integer('warehouse_id');
            $table->string('title');
            $table->string('note')->nullable();
            $table->integer('min_r')->nullable();
            $table->integer('max_r')->nullable();
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
        Schema::dropIfExists('transport_fees');
    }
}
