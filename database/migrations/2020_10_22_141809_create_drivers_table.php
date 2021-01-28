<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('avatar')->nullable();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->string('phone');
            $table->string('taxi');
            $table->string('taxiNo');
            $table->string('taxiColor');
            $table->string('hash')->nullable();
            $table->float('lat')->nullable();
            $table->float('lng')->nullable();
            $table->bigInteger('distanc')->default(0);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('parent');
            $table->boolean('busy')->default(0);
            $table->boolean('active')->default(0);

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
        Schema::dropIfExists('drivers');
    }
}
