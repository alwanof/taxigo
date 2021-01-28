<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('session');
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('from_address');
            $table->double('from_lat');
            $table->double('from_lng');
            $table->string('to_address')->nullable();
            $table->double('to_lat')->nullable();
            $table->double('to_lng')->nullable();
            $table->unsignedFloat('offer')->nullable();
            $table->integer('status')->default(0);
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->string('block')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('parent');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
