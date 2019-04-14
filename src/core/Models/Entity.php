<?php namespace Naraki\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Naraki\Blog\Models\BlogPost;
use Naraki\Core\Traits\Enumerable;
use Naraki\Mail\Models\Email;
use Naraki\Media\Models\MediaEntity;
use Naraki\Sentry\Models\Group;
use Naraki\Sentry\Models\GroupMember;
use Naraki\Sentry\Models\Person;
use Naraki\Sentry\Models\User;
use Naraki\System\Models\System;

class Entity extends Model
{
    use Enumerable;

    const BLOG_POSTS = 0x12c;           //300
    const EMAILS = 0x2d0;               //720
    const GROUPS = 0x44c;               //1100
    const GROUP_MEMBERS = 0x44d;        //1101
    const MEDIA = 0x76c;                //1900
    const PEOPLE = 0x8fc;               //2300
    const SYSTEM = 0xaf0;               //2800
    const USERS = 0xc1c;                //3100

    public $timestamps = false;
    protected $table = 'entities';
    protected $primaryKey = 'entity_id';
    protected $fillable = ['entity_id', 'entity_name'];
    /**
     * @var array Used in case a specific model isn't in \App\Models
     */
    public static $classMap = [
        'BlogPost' => BlogPost::class,
        'Email' => Email::class,
        'Group' => Group::class,
        'GroupMember' => GroupMember::class,
        'Medium' => MediaEntity::class,
        'Person' => Person::class,
        'System' => System::class,
        'User' => User::class,
    ];

    /**
     * Creates an instance of the model using its entity_id
     *
     * @param int $entityID
     * @param array $attributes
     * @param string $testContract
     * @return \Illuminate\Database\Eloquent\Model|\Naraki\Permission\Contracts\HasPermissions
     */
    public static function createModel($entityID, array $attributes = [], $testContract = null)
    {
        $class = static::getModelClassNamespace($entityID);
        if (class_exists($class)) {
            $o = new $class($attributes);
            if (!is_null($testContract) && !($o instanceof $testContract)) {
                throw new \UnexpectedValueException(
                    sprintf(
                        'Model %s is supposed to be an instance of %s',
                        $class,
                        $testContract)
                );
            }
            return $o;
        }
        return null;
    }

    /**
     * Returns the model's full namespace using its entity_id
     * Useful for instantiating a model.
     *
     * @param int $entityID
     * @param bool $prefixWithBackslash
     * @return string
     */
    public static function getModelClassNamespace($entityID, $prefixWithBackslash = true)
    {
        $className = static::getModelClass($entityID);
        $classNamespace = static::$classMap[$className] ?? null;

        if (class_exists($classNamespace)) {
            return ($prefixWithBackslash) ? $classNamespace : substr($classNamespace, 1);
        }
        throw new \UnexpectedValueException(sprintf('Class %s does not exist. (%s)', $className, $entityID));
    }

    /**
     * Returns the model's class name using its entity_id
     *
     * @param int $entityID
     * @return string
     */
    public static function getModelClass($entityID)
    {
        $entities = array_flip(static::getConstants());
        if (isset($entities[$entityID])) {
            return ucfirst(Str::camel(Str::singular(strtolower($entities[$entityID]))));
        }
        return null;
    }

    /**
     * Get the model's primary key using its entity_id
     *
     * @param int $entityID
     * @param bool $getQualifiedName Should the table name be included in the result
     * @return mixed
     */
    public static function getModelPrimaryKey($entityID, $getQualifiedName = false)
    {
        $class = self::getModelClassNamespace($entityID);
        /**
         * @var Model $instance
         */
        $instance = new $class();
        if ($getQualifiedName === false) {
            return $instance->getKeyName();
        }

        return $instance->getQualifiedKeyName();
    }

    /**
     * Get the model's presentable name, i.e 'Users', 'Groups', using its entity_id
     *
     * @param int $entityID
     * @return string
     */
    public static function getModelPresentableName($entityID)
    {
        $entities = array_flip(static::getConstants());
        if (isset($entities[$entityID])) {
            return trans(sprintf('general.enumerables.%s', $entities[$entityID]));
        }
        return null;
    }


}
