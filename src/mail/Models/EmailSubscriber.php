<?php namespace Naraki\Mail\Models;

use Naraki\Core\Models\Entity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;

class EmailSubscriber extends Model
{
    protected $primaryKey = 'email_subscriber_id';
    protected $fillable = [
        'email_subscriber_target_id',
        'email_subscriber_source_id',
        'email_list_id'
    ];
    public $timestamps = false;

    /**
     * @link https://laravel.com/docs/eloquent#query-scopes
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecipientEntityType(Builder $builder): Builder
    {
        return $builder->join('entity_types', 'entity_types.entity_type_id', '=',
            'email_subscribers.email_subscriber_target_id');
    }

    /**
     * @link https://laravel.com/docs/eloquent#query-scopes
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param int $userID
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUser(Builder $builder, $userID = null): Builder
    {
        return $builder->join('users', function (JoinClause $q) use ($userID) {
            $q->on('users.user_id','=','people.user_id');
            if (!is_null($userID)) {
                $q->where('users.user_id', '=', $userID);
            }
        });
    }

    /**
     * @link https://laravel.com/docs/eloquent#query-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopePerson(Builder $builder)
    {
        return $builder->join('people',
            'entity_types.entity_type_target_id', '=', 'people.person_id')
            ->where('entity_types.entity_id', '=', Entity::PEOPLE);
    }

    /**
     * @link https://laravel.com/docs/eloquent#query-scopes
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEmailList(Builder $builder): Builder
    {
        $builder->join('email_lists', 'email_lists.email_list_id', '=', 'email_subscribers.email_list_id');
        return $builder;
    }

}
