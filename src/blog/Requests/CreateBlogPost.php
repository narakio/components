<?php namespace Naraki\Blog\Requests;

use Naraki\Blog\Models\BlogStatus;
use Naraki\Core\Support\Requests\FormRequest;
use Illuminate\Support\Facades\Validator;

class CreateBlogPost extends FormRequest
{
    protected $activateTagStrippingFilter = false;

    /**
     * @var string
     */
    private $personSlug;
    /**
     * @var array
     */
    private $categories = [];
    /**
     * @var array
     */
    private $tags = [];

    public function rules()
    {
        return [
            'blog_post_title' => 'required|max:255',
            'blog_status' => 'required|status',
            'published_at' => 'date_format:YmdHi'
        ];
    }

    public function filters()
    {
        return [
            'blog_post_title' => 'strip_tags',
        ];
    }

    public function afterValidation()
    {
        $input = $this->input();

        if (isset($input['blog_post_person'])) {
            $this->personSlug = $input['blog_post_person'];
            unset($input['blog_post_person']);
        }

        if (isset($input['categories'])) {
            $this->categories = $input['categories'];
            unset($input['categories']);
        } else {
            $this->categories = null;
        }

        if (isset($input['tags'])) {
            $this->tags = array_unique($input['tags']);
            unset($input['tags']);
        } else {
            $this->tags = null;
        }

        if (isset($input['blog_status'])) {
            $input['blog_status_id'] = BlogStatus::getConstant($input['blog_status']);
            unset($input['blog_status']);
        }

        if (isset($input['published_at'])) {
            //Taking in a date format which we set manually in javascript.
            // This ensures we get a consistent format we can convert easily as opposed to locale based date formats
            $input['published_at'] = date_create_from_format('YmdHi', $input['published_at'])
                ->format('Y-m-d H:i:s');
        }

        $this->replace($input);
    }

    public function prepareForValidation()
    {
        Validator::extend('status', function ($attribute, $value, $parameters, $validator) {
            return BlogStatus::isValidName($value);
        });
        parent::prepareForValidation();
    }

    /**
     * @return mixed
     */
    public function getPersonSlug()
    {
        return $this->personSlug;
    }

    /**
     * @return mixed
     */
    public function getCategories(): ?array
    {
        return $this->categories;
    }

    /**
     * @return array
     */
    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function setPersonSlug($id)
    {
        $this->merge(['person_id' => $id]);
    }


}
