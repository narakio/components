<?php namespace Naraki\Blog\Providers;

use Naraki\Core\EloquentProvider;
use Naraki\Blog\Contracts\Source as BlogSourceInterface;
use Naraki\Blog\Models\BlogSource as BlogSourceModel;
use Naraki\Blog\Models\BlogSourceRecord;
use Naraki\Blog\Facades\Blog as BlogProvider;

class Source extends EloquentProvider implements BlogSourceInterface
{
    protected $model = \Naraki\Blog\Models\BlogSourceRecord::class;

    /**
     * @param string $slug
     * @param array $columns
     * @return mixed
     */
    public function buildByBlogSlug($slug, $columns = null)
    {
        if (is_null($columns)) {
            $columns = [
                'blog_source_content as source',
                'blog_source_name as type',
                'blog_source_record_id as record'
            ];
        }
        return $this->buildWithScopes($columns, ['post' => $slug, 'source']);
    }

    /**
     * @param int $type
     * @param string $content
     * @param string $blogSlug
     * @return void
     */
    public function createOne($type, $content, $blogSlug)
    {
        $blogPostId = BlogProvider::select(['blog_posts.blog_post_id'])
            ->where('blog_post_slug', $blogSlug)
            ->value('blog_post_id');
        if (!is_null($blogPostId)) {
            BlogSourceRecord::create([
                'blog_post_id' => $blogPostId,
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