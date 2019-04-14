<?php namespace Naraki\Sentry\Requests\Admin;

use Naraki\Core\Support\Requests\FormRequest;
use Naraki\Permission\Traits\ProcessesPermissions;

class UpdateUser extends FormRequest
{
    use ProcessesPermissions;

    /**
     * @var array
     */
    private $groups;

    public function rules()
    {
        return [
            'email' => 'email|unique:people,email',
            'username' => 'regex:/^\w+$/|min:5|max:25|unique:users,username'
        ];
    }

    public function afterValidation()
    {
        $input = $this->input();

        if (isset($input['permissions'])) {
            $this->processPermissions($input['permissions']);
        }

        if (isset($input['groups'])) {
            $this->groups = $input['groups'];
        }

        unset($input['permissions']);
        $this->replace($input);
    }

    /**
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @return bool
     */
    public function hasGroups()
    {
        return !is_null($this->groups);
    }

}
