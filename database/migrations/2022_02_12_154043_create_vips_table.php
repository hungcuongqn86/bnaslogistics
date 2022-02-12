<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vips', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('note')->nullable();
            $table->integer('min_tot_tran')->nullable();
            $table->integer('max_tot_tran')->nullable();
            $table->double('ck_dv')->nullable()->default(0);
            $table->double('ck_vc')->nullable()->default(0);
            $table->integer('deposit')->nullable()->default(90);
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
        Schema::dropIfExists('vips');
    }
}
