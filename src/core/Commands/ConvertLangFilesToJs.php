<?php namespace Naraki\Core\Commands;

use Illuminate\Console\Command;

class ConvertLangFilesToJs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lang:js';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert all language files named lang/{language}/"ajax" into javascript and place the converted files in resources/assets/js/lang ';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $origDir = "resources/lang/";

        $dir = opendir($origDir);
        if (!$dir) {
            die(sprintf("%s could not be read.", $origDir));
        }

        $this->info('The following files were converted:');
        $routes = [];
        while (($languageDir = readdir($dir)) !== false) {
            $langDir = $origDir . $languageDir;
            if (is_dir($langDir)) {
                $fileBackend = sprintf('%s/js-backend.php', $langDir);
                $fileFrontend = sprintf('%s/js-frontend.php', $langDir);
                $fileCommon = sprintf('%s/js-common.php', $langDir);
                if (is_file($fileBackend) && is_file($fileFrontend) && is_file($fileCommon)) {
                    $contents = include($fileBackend);
                    $varsCommon = include($fileCommon);
                    foreach ($varsCommon as $k => $v) {
                        if (isset($contents[$k])) {
                            $contents[$k] = array_merge($contents[$k], $v);
                        } else {
                            $contents[$k] = $v;
                        }
                    }
                    $fh = fopen(sprintf('resources/assets/backend/js/lang/%s.json', $languageDir), 'w');
                    fwrite($fh, json_encode($contents));
                    fclose($fh);
                    $this->info('    - Backend ' . $languageDir);

                    $contents = include($fileFrontend);
                    foreach ($varsCommon as $k => $v) {
                        if (isset($contents[$k])) {
                            $contents[$k] = array_merge($contents[$k], $v);
                        } else {
                            $contents[$k] = $v;
                        }
                    }
                    $fh = fopen(sprintf('resources/assets/frontend/js/lang/%s.json', $languageDir), 'w');
                    fwrite($fh, json_encode($contents));
                    fclose($fh);
                    $this->info('    - Frontend ' . $languageDir);
                }
                $fileBackend = sprintf('%s/routes-admin.php', $langDir);
                if (is_file($fileBackend)) {
                    $routes[$languageDir] = include($fileBackend);
                    $this->info('    - (routes)' . $languageDir);
                }

            }
        }
        $fh = fopen('resources/assets/backend/js/lang/routes.json', 'w');
        fwrite($fh, json_encode($routes));
        fclose($fh);

        closedir($dir);
    }
}
