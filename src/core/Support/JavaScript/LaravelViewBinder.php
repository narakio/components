<?php namespace Naraki\Core\Support\JavaScript;

use Illuminate\Contracts\Events\Dispatcher;

class LaravelViewBinder implements ViewBinder
{
    /**
     * The event dispatcher implementation.
     *
     * @var Dispatcher
     */
    private $event;

    /**
     * The name of the view to bind JS variables to.
     *
     * @var string
     */
    private $views;

    /**
     * Create a new Laravel view binder instance.
     *
     * @param Dispatcher $event
     * @param string|array $views
     */
    function __construct(Dispatcher $event, Array $views)
    {
        $this->event = $event;
        $this->views = $views;
    }

    /**
     * Bind the given JavaScript to the view.
     *
     * @param string $js
     */
    public function bind($js)
    {
        foreach ($this->views as $view) {
            $this->event->listen("composing: {$view}", function () use ($js) {
                echo sprintf('<script>%s</script>',$js);
            });
        }
    }
}
