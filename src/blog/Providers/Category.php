<?php namespace Naraki\Blog\Providers;

use Naraki\Core\EloquentProvider;
use Naraki\Blog\Contracts\Category as BlogCategoryInterface;
use Naraki\Blog\Models\BlogLabel;
use Naraki\Blog\Models\BlogLabelRecord;
use Naraki\Blog\Models\BlogLabelType;

class Category extends EloquentProvider implements BlogCategoryInterface
{
    protected $model = \Naraki\Blog\Models\BlogCategory::class;

    public function createOne($label, $parentSlug)
    {
        $newLabelType = BlogLabelType::create(
            ['blog_label_id' => BlogLabel::BLOG_CATEGORY]
        );
        $newCat = $this->createModel(
            [
                'blog_category_name' => $label,
                'blog_label_type_id' => $newLabelType->getKey()
            ]);
        if (!is_null($parentSlug) && !empty($parentSlug)) {
            $parentCategory = $this->getCat($parentSlug);
            if (!is_null($parentCategory)) {
                $newCat->appendToNode($parentCategory);
            } else {
                return null;
            }
        }
        $newCat->save();
        return $newCat;
    }

    /**
     * @param string|array $codename
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getByCodename($codename)
    {
        $builder = $this->build();
        if (is_array($codename)) {
            $builder->whereIn('blog_category_slug', $codename);
        } else {
            $builder->where('blog_category_slug', '=', $codename);
        }
        return $builder;
    }

    /**
     * @param array $categories
     * @param \Naraki\Blog\Models\BlogPost $post
     * @return void
     */
    public function attachToPost($categories, $post)
    {
        if (empty($categories)) {
            return;
        }
        $labelTypes = $this->getByCodename($categories)->labelType()
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
     * @param array $updated
     * @param \Illuminate\Database\Eloquent\Model $post
     * @return void
     */
    public function updatePost(?array $updated, $post)
    {
        if (is_null($updated)) {
            return;
        }
        $inStore = $this->getSelected($post->getKey());
        $toBeRemoved = array_diff($inStore, $updated);
        if (!empty($toBeRemoved)) {
            $entries = $this->getByCodename($toBeRemoved)->labelType()
                ->select(['blog_label_types.blog_label_type_id'])
                ->get()->pluck('blog_label_type_id')->toArray();
            BlogLabelRecord::query()
                ->whereIn('blog_label_type_id', $entries)
                ->where('blog_post_id', $post->getKey())
                ->delete();
        }

        $toBeAdded = array_diff($updated, $inStore);
        if (!empty($toBeAdded)) {
            $this->attachToPost($toBeAdded, $post);
        }
    }

    /**
     * @param int $postId
     * @return array
     */
    public function getSelected($postId)
    {
        return $this
            ->select(['blog_category_slug'])
            ->labelType()
            ->labelRecord($postId)
            ->get()->pluck('blog_category_slug')->toArray();
    }

    /**
     * @param string $slug
     * @return \Naraki\Blog\Models\BlogCategory|null
     */
    public function getCat($slug)
    {
        return $this->build()
            ->where('blog_category_slug', $slug)->first();
    }

    /**
     * @param string $slug
     * @return array
     */
    public function getHierarchy($slug)
    {
        return \DB::select('
            select bct.label,bct.id,bct.lvl from blog_category_tree, blog_category_tree as bct
            where blog_category_tree.id=?
            and blog_category_tree.lft between bct.lft and bct.rgt
        ', [$slug]);

    }

}