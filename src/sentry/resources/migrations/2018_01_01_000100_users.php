<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Users extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::enableForeignKeyConstraints();
        Schema::create('users', function (Blueprint $table) {
            $table->increments('user_id');
            $table->string('username', 25)->unique()->nullable();
            $table->string('password')->nullable();
            $table->boolean('activated')->default(false);
            $table->rememberToken()->nullable();
            $table->index('remember_token', 'idx_users_remember_token');
//            $table->unique(['user_id','email'],'idx_user_id_email');
            $table->unique(['user_id', 'username'], 'idx_user_id_username');
        });

        Schema::create('stat_users', function (Blueprint $table) {
            $table->increments('stat_user_id');

            $table->unsignedInteger('user_id');
            $table->timestamp('stat_user_last_visit')->nullable();
            $table->mediumInteger('stat_user_timezone')->nullable()->comment('The time zone difference, in minutes, from host system settings to UTC. UTC plus 1 makes it plus sixty.');

            $table->foreign('user_id')
                ->references('user_id')->on('users')
                ->onDelete('cascade');
        });

        Schema::create('user_activations', function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->string('activation_token', 32);

            $table->foreign('user_id')
                ->references('user_id')->on('users')
                ->onDelete('cascade');

            $table->unique(['user_id', 'activation_token'], 'idx_activations_remember_token');
        });

        Schema::create('system_oauth_providers', function (Blueprint $table) {
            $table->increments('oauth_provider_id');

            $table->unsignedInteger('user_id');

            $table->string('provider');
            $table->string('provider_user_id')->index();
            $table->string('access_token')->nullable();
            $table->string('refresh_token')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
//        Schema::dropIfExists('users');
    }
}
