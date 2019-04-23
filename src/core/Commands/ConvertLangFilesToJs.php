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

    protected $frontendFileContents = [];
    protected $backendFileContents = [];
    protected $commonFileContents = [];


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert all language files into javascript and place the converted files in resources/lang';

    public function handle()
    {
        $dirsToExplore = [
            '/',
            '/vendor/naraki/',
            '/vendor/naraki/components/src/'
        ];


        /**
         * We look for a resources/lang folder in the list of above directories.
         * If in those folders we find a folder matching the name of the current locale, we make as many copies of it
         * as there are languages.
         */
        foreach ($dirsToExplore as $dir) {
            $basePath = base_path() . $dir;
            $basePathHandle = @opendir($basePath);
            if ($basePathHandle) {
                while (($basePathFile = readdir($basePathHandle)) !== false) {
                    if (strpos($basePathFile, '.') === 0) {
                        continue;
                    }
                    $langFolder = $basePath . $basePathFile . '/resources/lang/';
                    $langFolderHandle = @opendir($langFolder);
                    if ($langFolderHandle) {
                        while (($langFolderFile = readdir($langFolderHandle)) !== false) {
                            if (strpos($langFolderFile, '.') === 0) {
                                continue;
                            }
                            $langHandle = @opendir($langFolder . $langFolderFile);
                            if ($langHandle && strpos($langFolderFile, '.') !== 0) {
//                                dump($langFolder . $langFolderFile);
                                $this->scanDir($langFolder . $langFolderFile);
                                @closedir($langHandle);
                            }
                        }
                        @closedir($langFolderHandle);
                    }
                }
                @closedir($basePathHandle);
            }
        }

        $jsDir = 'vendor/naraki/components/resources/';
        $this->outputToFile($jsDir);

        $locales = array_keys($this->commonFileContents);
        $routes = [];
        foreach ($locales as $locale) {
            $fileBackend = sprintf('%s/lang/%s/routes-admin.php', $jsDir, $locale);
            if (is_file($fileBackend)) {
                $routes[$locale] = include($fileBackend);
                $this->info('    - (routes)' . $locale);
            }

        }
        $fh = fopen($jsDir . 'backend/js/lang/routes.json', 'w');
        fwrite($fh, json_encode($routes));
        fclose($fh);
    }

    public function outputToFile($jsDir)
    {
        if (!empty($this->backendFileContents) && !empty($this->frontendFileContents) && !empty($this->commonFileContents)) {

            $locales = array_keys($this->commonFileContents);
            foreach ($locales as $locale) {
                foreach ($this->commonFileContents[$locale] as $k => $v) {
                    if (isset($this->backendFileContents[$locale][$k])) {
                        $this->backendFileContents[$locale][$k] = array_merge(
                            $this->backendFileContents[$locale][$k],
                            $v
                        );
                    } else {
                        $this->backendFileContents[$locale][$k] = $v;
                    }
                }
                $fh = fopen(sprintf($jsDir . 'backend/js/lang/%s.json', $locale), 'w');
                fwrite($fh, json_encode($this->backendFileContents[$locale]));
                fclose($fh);
                $this->info('    - Backend ' . $locale);

                foreach ($this->commonFileContents[$locale] as $k => $v) {
                    $this->backendFileContents[$locale][$k] = $v;
                }

                $fh = fopen(sprintf($jsDir . 'frontend/js/lang/%s.json', $locale), 'w');
                fwrite($fh, json_encode($this->backendFileContents[$locale]));
                fclose($fh);
                $this->info('    - Frontend ' . $locale);
            }
        }

    }

    public function scanDir($dir)
    {
        $this->getFileContents($dir, 'jsb', 'backendFileContents');
        $this->getFileContents($dir, 'jsf', 'frontendFileContents');
        $this->getFileContents($dir, 'jsc', 'commonFileContents');

    }

    public function getFileContents($dir, $file, $instanceContentVar)
    {
        $lang = substr($dir, strrpos($dir, '/') + 1);
        $path = sprintf('%s/%s.php', $dir, $file);
        if (is_file($path)) {
            if (!isset($this->{$instanceContentVar}[$lang])) {
                $this->{$instanceContentVar}[$lang] = include($path);
            } else {
                $this->{$instanceContentVar}[$lang] = array_merge($this->{$instanceContentVar}[$lang], include($path));
            }
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle2()
    {

        $jsDir = 'vendor/naraki/components/resources/';
        $origDir = $jsDir . 'lang/';

        $dir = opendir($origDir);
        if (!$dir) {
            die(sprintf("%s could not be read.", $origDir));
        }
        $this->info('The following files were converted:');
        $routes = [];
        while (($languageDir = readdir($dir)) !== false) {
            $langDir = $origDir . $languageDir;
            if (is_dir($langDir)) {
                $fileBackend = sprintf('%s/jsb.php', $langDir);
                $fileFrontend = sprintf('%s/jsf.php', $langDir);
                $fileCommon = sprintf('%s/jsc.php', $langDir);
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
                    $fh = fopen(sprintf($jsDir . 'backend/js/lang/%s.json', $languageDir), 'w');
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
                    $fh = fopen(sprintf($jsDir . 'frontend/js/lang/%s.json', $languageDir), 'w');
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
        $fh = fopen($jsDir . 'backend/js/lang/routes.json', 'w');
        fwrite($fh, json_encode($routes));
        fclose($fh);

        closedir($dir);
    }
}
