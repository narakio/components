<?php namespace Naraki\Core\Support\Viewable\Jobs;

use CyrildeWit\EloquentViewable\Contracts\Viewable;
use Naraki\Core\Job;

class ProcessPageView extends Job
{

    /**
     * @var Viewable
     */
    private $model;

    /**
     *
     * @param \CyrildeWit\EloquentViewable\Contracts\Viewable $model
     */
    public function __construct(Viewable $model)
    {
        $this->model = $model;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            viewable($this->model)->delayInSession((now()->addMinutes(30)))->record();
        } catch (\Exception $e) {
            \Log::critical($e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
        $this->delete();
    }
}
