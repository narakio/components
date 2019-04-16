<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Forum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forum_boards', function (Blueprint $table) {
            $table->increments('forum_board_id');
            $table->unsignedInteger('language_id');

            $table->string('forum_board_name')->nullable();
            $table->string('forum_board_slug')->nullable();

            $table->index(array('forum_board_id', 'forum_board_slug', 'language_id'), 'idx_board_language');
            $table->foreign('language_id')
                ->references('language_id')->on('languages')
                ->onDelete('cascade');
        });

        Schema::create('forum_threads', function (Blueprint $table) {
            $table->increments('forum_thread_id');
            $table->unsignedInteger('forum_board_id');
            $table->unsignedInteger('forum_thread_first_post_id');
            $table->unsignedInteger('thread_user_id')->nullable();

            $table->string('forum_thread_title')->nullable();
            $table->dateTime('forum_thread_last_post');

            $table->timestamps();
            $table->index(array('forum_thread_id', 'thread_user_id'), 'idx_thread_user');

            $table->foreign('forum_board_id')
                ->references('forum_board_id')->on('forum_boards')
                ->onDelete('cascade');
            $table->foreign('thread_user_id')
                ->references('user_id')->on('users')
                ->onDelete('set null');
        });

        Schema::create('forum_posts', function (Blueprint $table) {
            $table->increments('forum_post_id');
            $table->unsignedInteger('entity_type_id');
            $table->unsignedInteger('post_user_id')->nullable();

            $table->unsignedInteger('parent_id')->nullable();
            $table->unsignedInteger('lft')->default(0);
            $table->unsignedInteger('rgt')->default(0);

            $table->text('forum_post')->nullable();
            $table->string('forum_post_slug', 64)->nullable();
            $table->unsignedInteger('forum_post_fav_cnt')->default(0);

            $table->timestamps();
            $table->index(array('lft', 'rgt', 'parent_id'));
            $table->index(array('forum_post_id', 'post_user_id'), 'idx_post_user');

            $table->foreign('entity_type_id')
                ->references('entity_type_id')->on('entity_types')
                ->onDelete('cascade');
            $table->foreign('post_user_id')
                ->references('user_id')->on('users')
                ->onDelete('set null');
        });
        $this->createView();
        $db = app()->make(\Naraki\Core\Contracts\RawQueries::class);

        if (App::environment() !== 'testing') {
            $db->triggerUsersDelete();
            $this->incrementFavoriteCounterProcedure();
            $this->decrementFavoriteCounterProcedure();
        }
    }

    private function createView()
    {
        $sql = <<<SQL
create view forum_post_tree as
SELECT
  node.forum_post_id as id,
  (COUNT(parent.forum_post_id) - 1) AS lvl,
  node.lft,
  node.rgt,
  node.forum_post_slug as slug
FROM forum_posts AS node, forum_posts AS parent
WHERE node.lft BETWEEN parent.lft AND parent.rgt
GROUP BY node.forum_post_slug;
SQL;
        if (env('APP_ENV') == 'testing') {
            DB::unprepared($sql);
            return;
        }
        \DB::connection('mysql_seed')->unprepared($sql);

    }

    private function incrementFavoriteCounterProcedure()
    {
        $sql = <<<SQL
CREATE PROCEDURE sp_increment_forum_post_favorite_count(IN in_slug VARCHAR(64))
MODIFIES SQL DATA
BEGIN
update forum_posts set forum_post_fav_cnt = forum_post_fav_cnt+1 where forum_post_slug=in_slug;
END;
SQL;
        \DB::connection()->getPdo()->exec($sql);
    }

    private function decrementFavoriteCounterProcedure()
    {
        $sql = <<<SQL
CREATE PROCEDURE sp_decrement_forum_post_favorite_count(IN in_slug VARCHAR(64))
MODIFIES SQL DATA
BEGIN
update forum_posts set forum_post_fav_cnt = forum_post_fav_cnt-1 where forum_post_slug=in_slug;
END;
SQL;
        \DB::connection()->getPdo()->exec($sql);
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
