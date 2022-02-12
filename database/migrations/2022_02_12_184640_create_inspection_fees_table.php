<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInspectionFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inspection_fees', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('note')->nullable();
            $table->integer('min_count')->nullable();
            $table->integer('max_count')->nullable();
            $table->integer('val')->nullable()->default(0);
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
        Schema::dropIfExists('inspection_fees');
    }
}
