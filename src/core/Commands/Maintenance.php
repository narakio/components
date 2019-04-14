<?php namespace Naraki\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class Maintenance extends Command
{
    use DispatchesJobs;
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'app:maintain';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Used to perform maintenance tasks.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        //Used images when uploading an avatar. Should be deleted as a result of the cropping process,
        // but we do some cleanup because those might accumulate over time.
        $this->deleteUnusedTmpImages();

    }

    private function deleteUnusedTmpImages()
    {
        $cacheFiles = [];
        if (\Cache::has('temporary_avatars')) {
            $cacheFiles = \Cache::pull('temporary_avatars')->map(function ($v) {
                return $v->getHddFilename();
            })->flip();
        }
        $path = 'public/media/tmp/';
        $dir = opendir($path);
        if (!$dir) {
            die(sprintf("%s could not be read.", $path));
        }
        while (($file = readdir($dir)) !== false) {
            $fullPath = $path . $file;
            if (is_file($fullPath)) {
                if (!isset($cacheFiles[$file])) {
                    \File::delete($fullPath);
                }
            }
        }
        closedir($dir);
    }
}
