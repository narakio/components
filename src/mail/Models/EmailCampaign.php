<?php namespace Naraki\Mail\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EmailCampaign extends Model
{
    protected $primaryKey = 'email_campaign_id';
    protected $fillable = [
        'email_campaign_name',
        'email_campaign_codename',
        'email_campaign_total_sent',
        'email_list_id',
        'created_at'
    ];
    public $timestamps = false;

    /**
     * @link https://laravel.com/docs/eloquent#query-scopes
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeEmailUserEvent(Builder $query)
    {
        return $query->join('email_user_events', 'email_user_events.email_campaign_id', '=', 'email_campaigns.email_campaign_id');
    }

}