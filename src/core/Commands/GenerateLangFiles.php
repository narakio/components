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
    protected $description = 'Translates the project language files in directory resources/lang/en';

    private $languages;

    /**
     * The directory containing the original language files that will serve as a template
     *
     */
    protected $origDir = "resources/lang/en/";

    protected $origLang = "en";

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array(
                'l',
                'l',
                InputOption::VALUE_REQUIRED,
                'Generate an empty set of language files using the language code of your choice (i.e app::lang -l zh_cn).'
            ),
        );
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->comment('=====================================');
        $this->comment('');
        $this->info('  App language file creation');
        $this->comment('');
        $this->comment('=====================================');

        if ($this->option('l')) {
            $this->languages = array(
                "" => ['name' => $this->option('l'), 'code' => $this->option('l')],
            );
        } else {
            $this->languages = array(
                "" => ['name' => 'language', 'code' => "empty"]
            );
        }

        $dir = opendir($this->origDir);
        if (!$dir) {
            die(sprintf("%s could not be read.", $this->origDir));
        }
        $this->translate($dir, $this->origDir);
        closedir($dir);

        $this->comment('');
        $this->comment('=====================================');
        $this->comment('');
        $this->info('    App language file creation complete.');
        $this->comment('');
        $this->comment('=====================================');
    }

    private function translate($dir, $origDir)
    {
        while (($file = readdir($dir)) !== false) {
            if (is_file($origDir . $file) && substr($file, -4) == ".php") {
                //Importing the contents as a php array
                $langArray = include($origDir . $file);

                if (!$langArray) {
                    die("The file " . $origDir . $file . "could not be read.");
                }

                $this->info('  Copying ' . $origDir . $file);
                foreach ($this->languages as $langCode => $langInfo) {
                    $this->info('     ------> ' . str_replace(
                            sprintf('/%s/', $this->origLang),
                            sprintf('/%s/', $this->languages[$langCode]['code']),
                            $origDir . $file)
                    );
                    $result = $this->translateArray($langArray);


                    if (!empty($result)) {
                        $langFolderName = str_replace(
                            sprintf('/%s/', $this->origLang),
                            sprintf('/%s/', $this->languages[$langCode]['code']),
                            $origDir
                        );
                        if (!is_dir($langFolderName)) {
                            mkdir($langFolderName, 0751, true);
                        }

                        if (!is_file($langFolderName . $file)) {
                            file_put_contents($langFolderName . $file,
                                sprintf("<?php return [\n%s\n];", $this->arrayToString(($result))));
                        } else {
                            $existingLangArray = include($langFolderName . $file);

                            file_put_contents($langFolderName . $file,
                                sprintf("<?php return [\n%s\n];",
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
            } else {
                if (is_dir($origDir . $file) && strpos($origDir . $file, '/.') === false) {
                    $tmpOrigDir = $origDir . $file . "/";
                    $tmpDir = opendir($tmpOrigDir);
                    $this->translate($tmpDir, $tmpOrigDir, $this->languages);
                    closedir($tmpDir);
                }
            }
        }
    }

    private function compareNCopy($old, $new)
    {
        $result = [];
        foreach ($old as $k => $v) {
            if (!isset($new[$k])) {
                if (!is_array($v)) {
                    $result[$k] = '__'.$v;
                } else {
                    $result[$k] = $this->compareNCopy($v, []);
                }
            } else {
                if (!is_array($v)) {
                    $result[$k] = $new[$k];
                } else {
                    $z = array_diff_key($new[$k], $v);
                    if (!empty($z)) {
                        $result[$k] = array_merge($z, $this->compareNCopy($v, $new[$k]));
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
    public static function translateArray($langArray, $toEmptyArray = true)
    {
        $result = [];
        foreach ($langArray as $key => $value) {
            if (is_array($value)) {
                $result[$key] = static::translateArray($value, $toEmptyArray);
            } else {
                if (!empty($value) && $toEmptyArray === false) {
                    $result[$key] = sprintf("'%s'", str_replace("'", "\\'", $value));
                } else {
                    $result[$key] = "''";

                }
            }
        }

        return $result;
    }

    public static function arrayToString($data, $lvl = 1, $tabSize = 3)
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


}
