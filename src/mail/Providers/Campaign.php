<?php namespace Naraki\Mail\Providers;

use Naraki\Core\EloquentProvider;
use Naraki\Mail\Contracts\Campaign as CampaignInterface;
use Naraki\Mail\Contracts\UserEvent as EmailUserEventInterface;

/**
 * @method \Naraki\Mail\Models\EmailCampaign createModel(array $attributes = [])
 */
class Campaign extends EloquentProvider implements CampaignInterface
{
    protected $model = \Naraki\Mail\Models\EmailCampaign::class;
    /**
     * @var \Naraki\Mail\Contracts\UserEvent|\Naraki\Mail\Providers\UserEvent
     */
    private $event;

    /**
     * EmailCampaign constructor.
     *
     * @param \Naraki\Mail\Contracts\UserEvent|\Naraki\Mail\Providers\UserEvent $euei
     * @param string $model
     */
    public function __construct(EmailUserEventInterface $euei, $model = null)
    {
        parent::__construct($model);
        $this->event = $euei;
    }

    /**
     * @return \Naraki\Mail\Contracts\UserEvent|\Naraki\Mail\Providers\UserEvent
     */
    public function event()
    {
        return $this->event;
    }

    /**
     * @param bool $export
     *
     * @return array
     */
    public function getFiltered($export = false)
    {
        $campaigns = $this->createModel()
            ->emailUserEvent()
            ->select([
                \DB::raw('count(user_id) as total'),
                \DB::raw('count(distinct(user_id)) as uniq'),
                'email_user_event_type_id as event',
                'email_campaign_name as name',
                'email_campaign_total_sent as sent',
                'email_campaigns.email_campaign_id as id'
            ])->groupBy('email_campaigns.email_campaign_id')
            ->groupBy('email_user_event_type_id')
            ->orderBy('email_campaigns.email_campaign_id', 'desc')
            ->get();
        $output = [];
        foreach ($campaigns as $campaign) {
            $output[$campaign->getAttribute('name')][$campaign->getAttribute('event')] =
                (object)$campaign->toArray();
            $output[$campaign->getAttribute('name')]['info'] =
                (object)$campaign->toArray();
        }

        return $output;
    }

}