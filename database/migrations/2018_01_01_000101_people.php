<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class People extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('people', function (Blueprint $table) {
            $table->increments('person_id');
            $table->unsignedInteger('user_id')->default(0);
            $table->string('email')->unique()->nullable();
            $table->string('first_name', 75)->nullable();
            $table->string('last_name', 75)->nullable();
            $table->string('full_name', 150)->nullable();
            $table->string('person_slug', 150)->nullable();

            $table->timestamps();

            $table->foreign('user_id')
                ->references('user_id')->on('users')
                ->onDelete('cascade');
            $table->unique(['user_id', 'person_id'], 'idx_user_person');
            $table->unique(['person_slug'], 'idx_person_slug');
        });

        Schema::create('people_meta', function (Blueprint $table) {
            $table->increments('person_meta_id');
            $table->unsignedInteger('person_id')->default(0);

            $table->text('person_meta_url')->nullable();

            $table->foreign('person_id')
                ->references('person_id')->on('people')
                ->onDelete('cascade');
            $table->unique(['person_meta_id', 'person_id'], 'idx_person_meta');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
