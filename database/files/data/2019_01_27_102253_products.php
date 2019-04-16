<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Products extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_packagers', function (Blueprint $table) {
            $table->increments('product_packager_id');
            $table->string('product_packager_name', 150);
            $table->string('product_packager_code', 20)->nullable();
            $table->string('product_packager_siret', 20)->nullable();
            $table->string('product_packager_address')->nullable();
            $table->string('product_packager_postcode', 20)->nullable();
            $table->string('product_packager_town')->nullable();
            $table->string('product_packager_category', 75)->nullable();
            $table->string('product_packager_activity', 75)->nullable();
            $table->string('product_packager_species', 75)->nullable();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->increments('product_id');
            $table->unsignedInteger('product_packager_id');

            $table->string('product_name', 150);
            $table->string('product_identifier', 50)->nullable();

            $table->timestamps();

            $table->foreign('product_packager_id')
                ->references('product_packager_id')->on('product_packagers')
                ->onDelete('cascade');
        });

        Schema::create('product_nutrition_info', function (Blueprint $table) {
            $table->increments('product_nutrition_info_id');
            $table->unsignedInteger('product_id');

            $table->string('product_nova_group', 20)->nullable();
            $table->text('product_ingredients_text')->nullable();
            $table->string('product_nutrition_grade', 5)->nullable();

            $table->foreign('product_id')
                ->references('product_id')->on('products')
                ->onDelete('cascade');
        });

        Schema::create('product_brands', function (Blueprint $table) {
            $table->increments('product_brand_id');
            $table->string('product_brand_name', 150);
        });

        Schema::create('product_brand_records', function (Blueprint $table) {
            $table->increments('product_brand_record_id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('product_brand_id');

            $table->foreign('product_id')
                ->references('product_id')->on('products')
                ->onDelete('cascade');
            $table->foreign('product_brand_id')
                ->references('product_brand_id')->on('product_brands')
                ->onDelete('cascade');
            $table->unique(['product_id','product_brand_id'],'idx_product_brand_id');
        });

        Schema::create('product_categories', function (Blueprint $table) {
            $table->increments('product_category_id');
            $table->unsignedInteger('language_id');

            $table->string('product_category_name', 100);

            $table->foreign('language_id')
                ->references('language_id')->on('languages')
                ->onDelete('cascade');
        });

        Schema::create('product_category_records', function (Blueprint $table) {
            $table->increments('product_category_record_id');

            $table->unsignedInteger('product_id');
            $table->unsignedInteger('product_category_id');

            $table->foreign('product_category_id')
                ->references('product_category_id')->on('product_categories')
                ->onDelete('cascade');

            $table->foreign('product_id')
                ->references('product_id')->on('products')
                ->onDelete('cascade');
            $table->unique(['product_id','product_category_id'],'idx_product_category_id');
        });

        Schema::create('product_ingredients', function (Blueprint $table) {
            $table->increments('product_ingredient_id');
            $table->unsignedInteger('language_id');

            $table->string('product_ingredient_name', 100);

            $table->foreign('language_id')
                ->references('language_id')->on('languages')
                ->onDelete('cascade');
        });

        Schema::create('product_ingredient_records', function (Blueprint $table) {
            $table->increments('product_ingredient_record_id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('product_ingredient_id');

            $table->unsignedTinyInteger('product_ingredient_rank');

            $table->foreign('product_ingredient_id')
                ->references('product_ingredient_id')->on('product_ingredients')
                ->onDelete('cascade');

            $table->foreign('product_id')
                ->references('product_id')->on('products')
                ->onDelete('cascade');
            $table->unique(['product_id','product_ingredient_id'],'idx_product_ingredient_id');
        });

        Schema::create('product_nutrient_levels', function (Blueprint $table) {
            $table->increments('product_nutrient_level_id');
            $table->string('product_nutrient_level_name', 100);
        });

        Schema::create('product_nutrient_level_records', function (Blueprint $table) {
            $table->increments('product_nutrient_level_record_id');

            $table->unsignedInteger('product_id');
            $table->unsignedInteger('product_nutrient_level_id');

            $table->foreign('product_nutrient_level_id')
                ->references('product_nutrient_level_id')->on('product_nutrient_levels')
                ->onDelete('cascade');

            $table->foreign('product_id')
                ->references('product_id')->on('products')
                ->onDelete('cascade');
            $table->unique(['product_id','product_nutrient_level_id'],'idx_product_nut_level_id');
        });

        Schema::create('product_packages', function (Blueprint $table) {
            $table->increments('product_package_id');
            $table->string('product_package_name', 100);
        });

        Schema::create('product_package_records', function (Blueprint $table) {
            $table->increments('product_package_record_id');

            $table->unsignedInteger('product_id');
            $table->unsignedInteger('product_package_id');

            $table->foreign('product_package_id')
                ->references('product_package_id')->on('product_packages')
                ->onDelete('cascade');

            $table->foreign('product_id')
                ->references('product_id')->on('products')
                ->onDelete('cascade');
            $table->unique(['product_id','product_package_id'],'idx_product_package_id');
        });

        Schema::create('product_nutriments', function (Blueprint $table) {
            $table->increments('product_nutriment_id');
            $table->string('product_nutriment_name', 100);
        });

        Schema::create('product_nutriment_records', function (Blueprint $table) {
            $table->increments('product_nutriment_record_id');

            $table->unsignedInteger('product_id');
            $table->unsignedInteger('product_nutriment_id');

            $table->foreign('product_nutriment_id')
                ->references('product_nutriment_id')->on('product_nutriments')
                ->onDelete('cascade');

            $table->foreign('product_id')
                ->references('product_id')->on('products')
                ->onDelete('cascade');
            $table->unique(['product_id','product_nutriment_id'],'idx_product_nutriment_id');
        });

    }
}