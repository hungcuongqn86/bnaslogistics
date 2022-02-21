<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('shop_id');
            $table->integer('user_id');
            $table->tinyInteger('kiem_hang')->nullable();
            $table->tinyInteger('dong_go')->nullable();
            $table->tinyInteger('bao_hiem')->nullable();
            $table->integer('count_product')->nullable();
            $table->integer('tien_hang')->nullable();
            $table->integer('vip_id')->nullable();
            $table->double('ck_dv')->nullable();
            $table->integer('ck_dv_tt')->nullable();
            $table->double('phi_dat_hang_cs')->nullable();
            $table->integer('phi_dat_hang')->nullable();
            $table->integer('phi_dat_hang_tt')->nullable();
            $table->double('phi_bao_hiem_cs')->nullable();
            $table->integer('phi_bao_hiem_tt')->nullable();
            $table->double('phi_kiem_dem_cs')->nullable();
            $table->integer('phi_kiem_dem_tt')->nullable();
            $table->integer('ti_gia')->nullable();
            $table->tinyInteger('status')->nullable();
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
        Schema::dropIfExists('carts');
    }
}
