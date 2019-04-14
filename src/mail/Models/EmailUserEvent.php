<?php namespace Naraki\Mail\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EmailUserEvent extends Model
{
    protected $primaryKey = 'email_user_event_id';
    protected $fillable = [
        'email_user_event_type_id',
        'user_id'
    ];
    public $timestamps = false;

    /**
     * @link https://laravel.com/docs/eloquent#query-scopes
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeEmailCampaign(Builder $query)
    {
        return $query->join('email_campaigns', 'email_campaigns.email_campaign_id', '=',
            'email_user_events.email_campaign_id');
    }

    /**
     * @link https://laravel.com/docs/eloquent#query-scopes
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeEmailUserEventClick(Builder $query)
    {
        return $query->join('email_user_event_clicks', 'email_user_event_clicks.email_user_event_id', '=',
            'email_user_events.email_user_event_id');
    }

}