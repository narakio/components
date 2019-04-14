<?php namespace Naraki\Core\Support\Viewable;

use Carbon\Carbon;
use Illuminate\Contracts\Session\Session;
use CyrildeWit\EloquentViewable\Contracts\Viewable as ViewableContract;

class ViewSessionHistory
{
    /**
     * The session repository instance.
     *
     * @var \Illuminate\Contracts\Session\Session
     */
    protected $session;

    /**
     * The primary key under which history is stored.
     *
     * @var string
     */
    protected $primaryKey;

    /**
     * Create a new view session history instance.
     *
     * @param \Illuminate\Contracts\Session\Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
        $this->primaryKey = config('eloquent-viewable.session.key');
    }

    /**
     * Push a viewable model with an expiry date to the session.
     *
     * @param  \CyrildeWit\EloquentViewable\Contracts\Viewable $viewable
     * @param \DateTime $delay
     * @return bool
     */
    public function push(ViewableContract $viewable, $delay): bool
    {
        $namespaceKey = $this->createNamespaceKey($viewable);
        $viewableKey = $this->createViewableKey($viewable);

        $this->forgetExpiredViews($namespaceKey);

        if (! $this->has($viewableKey)) {
            $this->session->put($viewableKey, $this->createRecord($viewable, $delay));

            return true;
        }

        return false;
    }

    /**
     * Determine if the given model has been viewed.
     *
     * @param string $viewableKey
     * @return bool
     */
    protected function has(string $viewableKey): bool
    {
        return $this->session->has($viewableKey);
    }

    /**
     * Create a history record from the given viewable model and expiry date.
     *
     * @param  \CyrildeWit\EloquentViewable\Contracts\Viewable  $viewable
     * @param  \DateTime  $expiryDateTime
     * @return array
     */
    protected function createRecord(ViewableContract $viewable, $expiryDateTime): array
    {
        return [
            'viewable_id' => $viewable->getKey(),
            'expires_at' => $expiryDateTime,
        ];
    }

    /**
     * Remove all expired views from the session.
     *
     * @param  string  $key
     * @return void
     */
    protected function forgetExpiredViews(string $key)
    {
        $currentTime = Carbon::now();
        $viewHistory = $this->session->get($key, []);

        foreach ($viewHistory as $record) {
            if ($record['expires_at']->lte($currentTime)) {
                $recordId = array_search($record['viewable_id'], array_column($record, 'viewable_id'));

                $this->session->pull($key.$recordId);
            }
        }
    }

    /**
     * Create a base key from the given viewable model.
     *
     * @param  \CyrildeWit\EloquentViewable\Contracts\Viewable  $viewable
     * @return string
     */
    protected function createNamespaceKey(ViewableContract $viewable): string
    {
        return $this->primaryKey.'.'.strtolower(str_replace('\\', '-', $viewable->getMorphClass()));
    }

    /**
     * Create a unique key from the given viewable model.
     *
     * @param  \CyrildeWit\EloquentViewable\Contracts\Viewable  $viewable
     * @return string
     */
    protected function createViewableKey(ViewableContract $viewable): string
    {
        return $this->createNamespaceKey($viewable).'.'.$viewable->getAttribute('entity_type_id');
    }
}
