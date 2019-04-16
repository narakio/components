<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Views extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_page_views', function (Blueprint $table) {
            $table->increments('system_page_view_id');
            $table->unsignedInteger('entity_type_id');
            $table->string('visitor', 32)->nullable();
            $table->timestamp('viewed_at')->useCurrent();

            $table->foreign('entity_type_id')
                ->references('entity_type_id')->on('entity_types')
                ->onDelete('cascade');
            $table->index(['entity_type_id'], 'idx_page_views_entity');
        });

        Schema::create('stat_page_views', function (Blueprint $table) {
            $table->unsignedInteger('entity_type_id');

            $table->unsignedInteger('cnt')->default(0);
            $table->unsignedInteger('unq')->default(0);

            $table->timestamp('period_start')->nullable();
            $table->timestamp('period_end')->nullable();

            $table->foreign('entity_type_id')
                ->references('entity_type_id')->on('entity_types')
                ->onDelete('cascade');
            $table->unique(['entity_type_id'], 'idx_stat_page_views_entity');
        });

        \DB::unprepared('create view stat_page_views_view as 
        select entity_type_id, sum(cnt) as count, sum(unq) as uniq from stat_page_views
group by entity_type_id;
        ');
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
