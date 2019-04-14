<?php namespace Naraki\Blog\Providers;

use Naraki\Core\EloquentProvider;
use Naraki\Blog\Contracts\Source as BlogSourceInterface;
use Naraki\Blog\Models\BlogSource as BlogSourceModel;
use Naraki\Blog\Models\BlogSourceRecord;

class Source extends EloquentProvider implements BlogSourceInterface
{
    protected $model = \Naraki\Blog\Models\BlogSourceRecord::class;

    public function buildByBlogSlug($slug, $columns = null)
    {
        if (is_null($columns)) {
            $columns = [
                'blog_source_content as source',
                'blog_source_name as type',
                'blog_source_record_id as record'
            ];
        }
        return $this->select($columns)
            ->post($slug)->source();
    }

    public function createOne($type, $content, $blogSlug)
    {
        $blogPost = $this->select(['blog_posts.blog_post_id'])
            ->post($blogSlug)->first();
        if (!is_null($blogPost)) {
            BlogSourceRecord::create([
                'blog_post_id' => $blogPost->getAttribute('blog_post_id'),
                'blog_source_id' => $type,
                'blog_source_content' => $content
            ]);
        }
    }

    public function deleteOne($id)
    {
        $model = $this->select(['blog_source_record_id'])
            ->where('blog_source_record_id', $id)->first();
        if (!is_null($model)) {
            return $model->delete();
        }
        return false;
    }

    public static function listTypes()
    {
        return BlogSourceModel::getPresentableConstants();
    }

}