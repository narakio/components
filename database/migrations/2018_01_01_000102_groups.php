<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Groups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->smallIncrements('group_id');

            $table->string('group_name', 60)->nullable();
            $table->string('group_slug', 75)->nullable();
            $table->smallInteger('group_mask')->unsigned();
        });

        Schema::create('group_members', function (Blueprint $table) {
            $table->unsignedSmallInteger('group_id')->unsigned();
            $table->integer('user_id')->unsigned();

            $table->foreign('group_id')
                ->references('group_id')->on('groups')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('user_id')->on('users')
                ->onDelete('cascade');

            $table->unique(['group_id', 'user_id'], 'group_members_idx');
        });
    }
}
