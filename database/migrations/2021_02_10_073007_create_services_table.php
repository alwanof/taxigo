<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('plan')->default('NONE');
            $table->double('const')->default(0);
            $table->double('distance')->default(0);
            $table->double('time')->default(0);
            $table->boolean('active')->default(1);
            $table->unsignedBigInteger('vehicle_id');
            $table->string('qtitle')->default('Untitled#N');
            $table->tinyInteger('qactive')->default(0);
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
        Schema::dropIfExists('services');
    }
}
