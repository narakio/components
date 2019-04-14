<?php namespace Naraki\Permission\Jobs;

use Naraki\Core\Job;
use Illuminate\Support\Facades\Log;
use Naraki\Permission\Support\Permission;
use Naraki\Permission\Models\PermissionStore;

class Update extends Job
{

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Throwable
     */
    public function handle()
    {
        try {
            \DB::transaction(function () {
                PermissionStore::query()->delete();
                Permission::assignToAll();
            });
        } catch (\Exception $e) {
            Log::critical($e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
        $this->delete();
    }
}
