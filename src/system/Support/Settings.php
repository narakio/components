<?php namespace Naraki\System\Support;

use Illuminate\Support\Facades\Redis;

/**
 * Static method calls are handled by __handleStatic
 *
 * @method static array|self general($asObject = null)
 * @method static array|self social($asObject = null)
 * @method static array|self sitemap($asObject = null)
 */
class Settings
{
    /**
     * @var array
     */
//    protected $formattedSettings = [];
    protected $settings = [];

    /**
     * @param string $type
     * @return self
     */
    public static function getSettings(string $type): self
    {
        return unserialize(Redis::get(sprintf('settings_%s', $type))) ?: new self();
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        return $this->settings[$key] ?? null;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key)
    {
        return isset($this->settings[$key])&&!is_null($this->settings[$key]);
    }


    /**
     * @param string $key
     * @param mixed $value
     */
    public function put(string $key, $value)
    {
        $this->settings[$key] = $value;
    }

    /**
     * @param string $key
     * @param $value
     */
    public static function saveSettings(string $key, $value)
    {
        $instance = new self();
        Redis::set(sprintf('settings_%s', $key), serialize($instance->setSettings($value)));
    }

    /**
     * @param string $key
     */
    public function save(string $key)
    {
        $this->saveSettings($key, $this->settings);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->settings;
    }

    /**
     * @param string $name
     * @param mixed $args
     * @return array|\Naraki\System\Support\Settings
     */
    public static function __callStatic($name, $args)
    {
        $settings = self::getSettings($name);
        return !empty($args) ? $settings->toArray() : $settings;
    }

    /**
     * @param array $settings
     * @return \Naraki\System\Support\Settings
     */
    public function setSettings(array $settings): self
    {
        $this->settings = $settings;
        return $this;
    }

    /**
     * @return array
     */
    public function getArraySettings()
    {
        return $this->settings;
    }

}