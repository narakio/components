<?php namespace Naraki\Core\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class GenerateLangFiles extends Command
{

    /**
     * The console command name.
     *
     * @var    string
     */
    protected $name = 'lang:tr';

    /**
     * The console command description.
     *
     * @var    string
     */
    protected $description = 'Translates the project language files';

    /**
     * @var array
     */
    protected $availableLocales;
    /**
     * @var bool
     */
    protected $deleteMode = false;


    public function __construct()
    {
        parent::__construct();
        $this->defaultLocale = config('app.locale');
        $locales = config('app.locales');
        unset($locales[$this->defaultLocale]);
        $this->availableLocales = array_keys($locales);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            [
                'delete',
                'd',
                InputOption::VALUE_NONE,
                'Delete language files other than those that match the default language.'
            ]
        ];
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->option('delete')) {
            $this->deleteMode = true;
        }
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
                                if ($langFolderFile === $this->defaultLocale) {
                                    if (!$this->deleteMode) {
                                        $this->translate($langHandle, $langFolder . $langFolderFile);
                                    } else {
                                        foreach ($this->availableLocales as $locale) {
                                            if (is_dir($langFolder . $locale)) {
                                                $this->delTree($langFolder . $locale);
                                            }
                                        }
                                    }
                                }
                                @closedir($langHandle);
                            }
                        }
                        @closedir($langFolderHandle);
                    }
                }
                @closedir($basePathHandle);
            }
        }
    }

    private function translate($dirHandle, $baseDir)
    {
        while (($file = readdir($dirHandle)) !== false) {
            $path = $baseDir . '/' . $file;
            if (is_file($path) && substr($file, -4) == '.php') {
                //Importing the contents as a php array
                $langArray = include($path);

                if (empty($langArray) || is_null($langArray)) {
                    continue;
                }

                $pathString = explode('/', $baseDir);
                $this->info(sprintf('Copying "%s->%s"...', $pathString[count($pathString) - 4], $file));

                foreach ($this->availableLocales as $langCode) {
                    $this->info(sprintf("\tinto the '%s' folder", $langCode));
                    $result = $this->translateArray($langArray, true);

                    if (!empty($result)) {
                        $langFolderName = str_replace(
                            sprintf('/%s', $this->defaultLocale),
                            sprintf('/%s', $langCode),
                            $baseDir
                        );
                        if (!is_dir($langFolderName)) {
                            mkdir($langFolderName, 0751, true);
                        }

                        $destination = $langFolderName . '/' . $file;
                        //If the file doesn't already exist, we write our copied contents into the new file
                        if (!is_file($destination)) {
                            file_put_contents($destination,
                                sprintf(
                                    "<?php\n\nreturn [\n%s\n];",
                                    $this->arrayToString(
                                        $result
                                    )
                                )
                            );
                        } else {
                            //If the file exist, it may contain translated strings that we want to keep,
                            //but also strings that we need to add because they were created sometime
                            //after that existing file was created.
                            $existingLangArray = include($destination);
                            file_put_contents($destination,
                                sprintf("<?php\n\nreturn [\n%s\n];",
                                    $this->arrayToString(
                                        $this->translateArray(
                                            $this->compareNCopy($langArray, $existingLangArray),
                                            false
                                        )
                                    )
                                )
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * @param array $old
     * @param array $new
     * @return array
     */
    private function compareNCopy($old, $new)
    {
        $result = [];
        foreach ($old as $k => $v) {
            if (!isset($new[$k])) {
                if (!is_array($v)) {
                    $result[$k] = '__' . $v;
                } else {
                    $result[$k] = $this->compareNCopy($v, []);
                }
            } else {
                if (!is_array($v)) {
                    $result[$k] = $new[$k];
                } else {
                    $z = array_diff_key($new[$k], $v);
                    if (!empty($z)) {
                        //using '+' because array merge does not preserve keys
                        $result[$k] = $z + $this->compareNCopy($v, $new[$k]);
                    } else {
                        $result[$k] = $this->compareNCopy($v, $new[$k]);
                    }
                    $z = array_diff_key($v, $new[$k]);
                    if (!empty($z)) {
                        //using '+' because array merge does not preserve keys
                        foreach ($z as $key => $f) {
                            unset($new[$key]);
                        }
                        $result[$k] = $z + $this->compareNCopy($v, $new[$k]);
                    } else {
                        $result[$k] = $this->compareNCopy($v, $new[$k]);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Converts array values in a way that makes it convertible to text.
     * Empty values are converted to "''" so they appear as values when the array is converted to text.
     *
     * @param $langArray
     * @param bool $toEmptyArray
     *
     * @return array
     */
    public static function translateArray($langArray, $toTransposedArray = true)
    {
        $result = [];
        foreach ($langArray as $key => $value) {
            if (is_array($value)) {
                $result[$key] = static::translateArray($value, $toTransposedArray);
            } else {
                if (!empty($value) && $toTransposedArray === false) {
                    $result[$key] = sprintf("'%s'", str_replace("'", "\\'", $value));
                } else {
                    $result[$key] = sprintf("'__%s'", str_replace("'", "\\'", $value));
                }
            }
        }

        return $result;
    }

    /**
     * @param array $data
     * @param int $lvl
     * @param int $tabSize
     * @return string
     */
    public static function arrayToString($data, $lvl = 1, $tabSize = 4): string
    {
        $string = "";
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $string .= str_repeat(" ", $lvl * $tabSize) . "'" . $key . "' => [\n" . static::arrayToString($value,
                        ++$lvl, $tabSize);
                $lvl--;
                $string .= str_repeat(" ", $lvl * $tabSize) . "],\n";
            } else {
                $string .= str_repeat(" ", $lvl * $tabSize) . "'" . $key . "' => " . $value . ",\n";
            }
        }

        return $string;
    }

    public function delTree($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? delTree("$dir/$file") : @unlink("$dir/$file");
        }
        return @rmdir($dir);
    }

    public function __call($name, $args)
    {
        return $this->{$name}(...$args);

    }

}
