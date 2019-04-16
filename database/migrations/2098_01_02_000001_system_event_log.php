<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SystemEventLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_event_types', function (Blueprint $table) {
            $table->increments('system_event_type_id');
            $table->string('system_event_type_name', 75);
        });

        \Naraki\System\Models\SystemEventType::insert([
            [
                'system_event_type_id' => \Naraki\System\Models\SystemEventType::BROADCAST,
                'system_event_type_name' => 'broadcast'
            ],
            [
                'system_event_type_id' => \Naraki\System\Models\SystemEventType::EMAIL,
                'system_event_type_name' => 'email'
            ]
        ]);

        Schema::create('system_events', function (Blueprint $table) {
            $table->increments('system_event_id');

            $table->unsignedSmallInteger('entity_id')->default(\Naraki\Core\Models\Entity::SYSTEM);
            $table->string('system_event_name', 75);

            $table->foreign('entity_id')
                ->references('entity_id')->on('entities')
                ->onDelete('cascade');
        });

        Schema::create('system_event_log', function (Blueprint $table) {
            $table->increments('system_event_log_id');

            $table->unsignedInteger('system_event_id');
            $table->text('system_event_log_data')->nullable();
            $table->dateTime('created_at')->nullable();

            $table->foreign('system_event_id')
                ->references('system_event_id')->on('system_events')
                ->onDelete('cascade');
        });

        \Naraki\System\Models\SystemEvent::insert([
            [
                'system_event_id' => 1,
                'system_event_name' => 'Newsletter Subscription',
                'entity_id' => \Naraki\Core\Models\Entity::SYSTEM
            ],
            [
                'system_event_id' => 2,
                'system_event_name' => 'Contact form message',
                'entity_id' => \Naraki\Core\Models\Entity::SYSTEM
            ],
            [
                'system_event_id' => 3,
                'system_event_name' => 'Blog post Comment',
                'entity_id' => \Naraki\Core\Models\Entity::SYSTEM
            ],
            [
                'system_event_id' => 4,
                'system_event_name' => 'Blog post mention',
                'entity_id' => \Naraki\Core\Models\Entity::BLOG_POSTS
            ],
            [
                'system_event_id' => 5,
                'system_event_name' => 'Blog post reply',
                'entity_id' => \Naraki\Core\Models\Entity::BLOG_POSTS
            ],
        ]);

//        Schema::create('system_user_settings', function (Blueprint $table) {
//            $table->increments('system_user_setting_id');
//
//            $table->unsignedInteger('entity_id');
//
//            $table->unsignedInteger('user_id');
//
//        $table->foreign('entity_id')
//            ->references('entity_id')->on('entities')
//            ->onDelete('cascade');
//            $table->foreign('user_id')
//                ->references('user_id')->on('users')
//                ->onDelete('cascade');
//        });

        Schema::create('system_user_subscriptions', function (Blueprint $table) {
            $table->increments('system_user_subscription_id');

            $table->unsignedInteger('user_id');
            $table->unsignedInteger('system_event_type_id');
            $table->unsignedInteger('system_event_id');

            $table->foreign('user_id')
                ->references('user_id')->on('users')
                ->onDelete('cascade');
            $table->foreign('system_event_type_id')
                ->references('system_event_type_id')->on('system_event_types')
                ->onDelete('cascade');
            $table->foreign('system_event_id')
                ->references('system_event_id')->on('system_events')
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
        //
    }
}
