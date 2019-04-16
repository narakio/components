<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Blog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->increments('language_id');
            $table->string('language_name', 50);
            $table->string('language_family', 30);
            $table->string('native_name', 50);
            $table->string('ISO_639_1', 2);
            $table->string('ISO_639_2T', 3);
            $table->string('ISO_639_2B', 3);
            $table->string('ISO_639_3', 10);
            $table->string('ISO_639_6', 10);
        });

        Schema::create('blog_status', function (Blueprint $table) {
            $table->increments('blog_status_id');

            $table->string('blog_status_name', 75)->nullable();
        });

        Schema::create('blog_posts', function (Blueprint $table) {
            $table->increments('blog_post_id');

            $table->unsignedInteger('person_id')->nullable();
            $table->unsignedInteger('blog_status_id')
                ->default(\Naraki\Blog\Models\BlogStatus::BLOG_STATUS_DRAFT);
            $table->unsignedInteger('language_id')->default(1);

            $table->string('blog_post_title')->nullable();
            $table->string('blog_post_slug', 100)->nullable();
            $table->text('blog_post_content')->nullable();
            $table->text('blog_post_excerpt')->nullable();
            $table->boolean('blog_post_is_sticky')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->foreign('person_id')
                ->references('person_id')->on('people')->onDelete('set null');
            $table->foreign('language_id')
                ->references('language_id')->on('languages');
            $table->unique(['blog_post_slug'], 'idx_blog_post_id_slug');
        });

        Schema::create('blog_sources', function (Blueprint $table) {
            $table->increments('blog_source_id');

            $table->string('blog_source_name', 75)->nullable();
        });

        \Naraki\Blog\Models\BlogSource::insert([
            ['blog_source_name' => 'url'],
            ['blog_source_name' => 'img'],
            ['blog_source_name' => 'file'],
        ]);

        Schema::create('blog_source_records', function (Blueprint $table) {
            $table->increments('blog_source_record_id');

            $table->unsignedInteger('blog_post_id');
            $table->unsignedInteger('blog_source_id');
            $table->text('blog_source_content')->nullable();
            $table->text('blog_source_description')->nullable();

            $table->foreign('blog_source_id')
                ->references('blog_source_id')->on('blog_sources')
                ->onDelete('cascade');
            $table->foreign('blog_post_id')
                ->references('blog_post_id')->on('blog_posts')
                ->onDelete('cascade');
        });

        Schema::create('blog_labels', function (Blueprint $table) {
            $table->increments('blog_label_id');

            $table->string('blog_label_name', 75)->nullable();
        });

        Schema::create('blog_label_types', function (Blueprint $table) {
            $table->increments('blog_label_type_id');

            $table->unsignedInteger('blog_label_id')
                ->default(\Naraki\Blog\Models\BlogLabel::BLOG_TAG);
            $table->foreign('blog_label_id')
                ->references('blog_label_id')->on('blog_labels')
                ->onDelete('cascade');
            $table->index(array('blog_label_type_id', 'blog_label_id'), 'idx_blog_labels');
        });

        Schema::create('blog_categories', function (Blueprint $table) {
            $table->increments('blog_category_id');

            $table->unsignedInteger('parent_id')->nullable();
            $table->unsignedInteger('lft')->default(0);
            $table->unsignedInteger('rgt')->default(0);
            $table->unsignedInteger('blog_label_type_id');
            $table->string('blog_category_name', 75)->nullable();
            $table->string('blog_category_slug', 75)->nullable();

            $table->timestamps();

            $table->index(array('lft', 'rgt', 'parent_id'));
            $table->unique(array('blog_category_slug'));

            $table->foreign('blog_label_type_id')
                ->references('blog_label_type_id')->on('blog_label_types')
                ->onDelete('cascade');
        });

        Schema::create('blog_tags', function (Blueprint $table) {
            $table->increments('blog_tag_id');

            $table->unsignedInteger('blog_label_type_id');
            $table->string('blog_tag_name', 50)->nullable();
            $table->string('blog_tag_slug', 75)->nullable();

            $table->foreign('blog_label_type_id')
                ->references('blog_label_type_id')->on('blog_label_types')
                ->onDelete('cascade');
            $table->unique(array('blog_tag_name'));
        });

        Schema::create('blog_label_records', function (Blueprint $table) {
            $table->increments('blog_label_record_id');

            $table->unsignedInteger('blog_post_id');
            $table->unsignedInteger('blog_label_type_id');

            $table->foreign('blog_post_id')
                ->references('blog_post_id')->on('blog_posts')
                ->onDelete('cascade');
            $table->foreign('blog_label_type_id')
                ->references('blog_label_type_id')->on('blog_label_types')
                ->onDelete('cascade');
            $table->index(array('blog_post_id', 'blog_label_type_id'), 'idx_blog_post_type');
        });

        \DB::beginTransaction();
        $label_types = [
            ['blog_label_name' => \Naraki\Blog\Models\BlogLabel::getConstantByID(\Naraki\Blog\Models\BlogLabel::BLOG_TAG)],
            ['blog_label_name' => \Naraki\Blog\Models\BlogLabel::getConstantByID(\Naraki\Blog\Models\BlogLabel::BLOG_CATEGORY)],
        ];
        \Naraki\Blog\Models\BlogLabel::insert($label_types);

        $status = [
            [
                'blog_status_name' => \Naraki\Blog\Models\BlogStatus::getConstantByID(
                    \Naraki\Blog\Models\BlogStatus::BLOG_STATUS_DRAFT
                )
            ],
            [
                'blog_status_name' => \Naraki\Blog\Models\BlogStatus::getConstantByID(
                    \Naraki\Blog\Models\BlogStatus::BLOG_STATUS_REVIEW
                )
            ],
            [
                'blog_status_name' => \Naraki\Blog\Models\BlogStatus::getConstantByID(
                    \Naraki\Blog\Models\BlogStatus::BLOG_STATUS_PUBLISHED
                )
            ]
        ];
        \Naraki\Blog\Models\BlogStatus::insert($status);

        $newLabelType = \Naraki\Blog\Models\BlogLabelType::create(
            ['blog_label_id' => \Naraki\Blog\Models\BlogLabel::BLOG_CATEGORY]
        );
        \Naraki\Blog\Models\BlogCategory::create([
            'blog_category_name' => 'Default',
            'blog_label_type_id' => $newLabelType->getKey()
        ]);
        $this->createViews();
        \DB::commit();
        \DB::beginTransaction();
        $this->extractLanguages();
        \DB::commit();

    }

    public function createViews()
    {
        $view = 'CREATE VIEW blog_category_tree AS
        SELECT
            node.lft,
            node.rgt,
            node.blog_category_id,
            node.blog_category_name as label,
            (COUNT(parent.blog_category_id) - 1) AS lvl,
            node.blog_category_slug as id
          FROM blog_categories AS node, blog_categories AS parent
          WHERE node.lft BETWEEN parent.lft AND parent.rgt
          GROUP BY node.blog_category_slug,node.blog_category_id,node.blog_category_name
          ORDER BY node.lft;
        ';
        if (env('APP_ENV') == 'testing') {
            DB::unprepared($view);
            return;
        }
        DB::connection('mysql_seed')->unprepared($view);
    }

    private function extractLanguages()
    {
        $languageCSV = \League\Csv\Reader::createFromPath(base_path() . '/database/files/data/languages.tsv', 'r');
        //Tab as delimiter
        $languageCSV->setDelimiter(chr(9));
        $languageDBColumns = [
            'language_name',
            'language_family',
            'native_name',
            'ISO_639_1',
            'ISO_639_2T',
            'ISO_639_2B',
            'ISO_639_3',
            'ISO_639_6',
        ];

        $records = $languageCSV->getRecords($languageDBColumns);
        $lang = [];
        foreach ($records as $offset => $record) {
            $lang[] = $record;
        }

        if (App::environment() !== 'testing') {
            $this->seedChunk($lang, \Naraki\Core\Models\Language::class, 5);
        } else {
            \Naraki\Core\Models\Language::insert([
                array_combine($languageDBColumns, [
                    '1',
                    'English',
                    'Indo-European',
                    'English',
                    'en',
                    'eng',
                    'eng',
                    'eng'
                ])
            ]);
        }

    }

    private function seedChunk($data, $model, $nbChunks = 25)
    {
        $chunks = array_chunk($data, $nbChunks);
        foreach ($chunks as $chunk) {
            forward_static_call(sprintf('%s::insert', $model), $chunk);
        }
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
