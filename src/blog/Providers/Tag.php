<?php namespace Naraki\Blog\Providers;

use Naraki\Core\EloquentProvider;
use Naraki\Blog\Contracts\Tag as BlogTagInterface;
use Naraki\Blog\Models\BlogLabel;
use Naraki\Blog\Models\BlogLabelRecord;
use Naraki\Blog\Models\BlogLabelType;

class Tag extends EloquentProvider implements BlogTagInterface
{
    protected $model = \Naraki\Blog\Models\BlogTag::class;

    public function getByPost($postId)
    {
        return $this
            ->buildWithScopes(['blog_tag_name', 'blog_tag_slug'], ['labelType', 'labelRecord' => $postId])
            ->get()->pluck('blog_tag_name')->toArray();
    }

    /**
     * @param $postId
     * @return \Naraki\Blog\Models\BlogTag[]
     */
    public function getByPostColumns($postId)
    {
        return $this
            ->buildWithScopes(
                ['blog_tag_name as name', 'blog_tag_slug as slug', 'blog_label_record_id as id'],
                ['labelType', 'labelRecord' => $postId])
            ->get();
    }

    public function createMany($names)
    {

        if (empty($names)) {
            return;
        }
        //We create a first label type to get its id
        $newLabelType = BlogLabelType::query()->create(['blog_label_id' => BlogLabel::BLOG_TAG]);
        $firstLabelId = $newLabelType->getKey();
        $nbTags = count($names);

        if ($nbTags > 1) {
            //When we create the rest of the label types we account for the one we created (n-1)
            BlogLabelType::query()->insert(array_fill(
                    0,
                    $nbTags - 1,
                    ['blog_label_id' => BlogLabel::BLOG_TAG])
            );
        }
        $newTags = [];
        foreach ($names as $name) {
            $newTags[] = [
                'blog_tag_name' => $name,
                'blog_tag_slug' => slugify($name),
                'blog_label_type_id' => $firstLabelId++
            ];
        }

        \Naraki\Blog\Models\BlogTag::query()->insert($newTags);
    }

    /**
     * @param string|array $name
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getByName($name)
    {
        $builder = $this->build();
        if (is_array($name)) {
            $builder->whereIn('blog_tag_name', $name);
        } else {
            $builder->where('blog_tag_name', '=', $name);
        }
        return $builder;
    }

    /**
     * @param array $tags
     * @param \Naraki\Blog\Models\BlogPost $post
     * @return void
     */
    public function attachToPost($tags, $post)
    {
        if (empty($tags)) {
            return;
        }
        $this->createMany($this->getUnknownTags($tags));

        $labelTypes = $this->getByName($tags)->labelType()
            ->select(['blog_label_types.blog_label_type_id as id'])
            ->get();
        if (!is_null($labelTypes)) {
            $records = [];
            foreach ($labelTypes as $label) {
                $records[] = [
                    $post->getKeyName() => $post->getKey(),
                    'blog_label_type_id' => $label->getAttribute('id')
                ];
            }
            BlogLabelRecord::insert($records);
        }
    }

    /**
     * We return removed elements by label_record_id because that's what we use in elasticsearch
     * to reference tags.
     *
     * @param array $updated
     * @param \Naraki\Blog\Models\BlogPost $post
     * @return array An array containing added elements (their slugs) and removed elements (their label_record_id)
     */
    public function updatePost(?array $updated, $post)
    {
        if (is_null($updated)) {
            return null;
        }
        $inStore = $this->getByPost($post->getKey());
        $toBeRemoved = array_diff($inStore, $updated);
//        dd($inStore,$updated,$toBeRemoved);
        $labelTypes = null;
        if (!empty($toBeRemoved)) {
            $labelTypes = $this->getByName($toBeRemoved)->labelType()
                ->select(['blog_label_types.blog_label_type_id'])
                ->get()->pluck('blog_label_type_id')->toArray();

            BlogLabelType::query()
                ->whereIn('blog_label_type_id', $labelTypes)
                ->delete();
        }

        $toBeAdded = array_diff($updated, $inStore);
        if (!empty($toBeAdded)) {
            $this->attachToPost($toBeAdded, $post);
        }
        return ['added' => $toBeAdded, 'removed' => $labelTypes];
    }

    public function getUnknownTags($names)
    {
        $tags = $this->getByName($names)->labelType()
            ->select(['blog_tag_name'])
            ->get()
            ->pluck('blog_tag_name')
            ->toArray();
        return array_diff($names, $tags);
    }

    public function countAll()
    {
        $model = $this->createModel();

        $result = \DB::select(sprintf('
            select count(%1$s) as c from blog_tags
            where %1$s in (
            select distinct(%1$s) from blog_tags
            join blog_label_types blt on blog_tags.blog_label_type_id = blt.blog_label_type_id
            join blog_label_records blr on blt.blog_label_type_id = blr.blog_label_type_id)',
                $model->getKeyName()
            )
        );
        return !empty($result) ? $result[0]->c : null;
    }

    /**
     * @param int $n
     * @param string $columns
     * @return string
     */
    public function getNth($n, $columns)
    {
        return $this->select([$columns])
            ->scopes(['labelType', 'labelRecord', 'blogPost'])
            ->groupBy('blog_tag_id', 'blog_posts.updated_at')
            ->orderBy($columns, 'desc')
            ->limit(1)
            ->offset($n)->value('updated_at');
    }

    /**
     * @param int $n
     * @param int $offset
     * @param array $columns
     * @param string $order
     * @param string $direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getN($n, $offset, $columns, $order, $direction = 'desc')
    {
        return $this->select($columns)
            ->scopes(['labelType', 'labelRecord', 'blogPost'])
            ->groupBy(...$columns)
            ->orderBy($order, $direction)
            ->limit($n)
            ->offset($offset);
    }

}