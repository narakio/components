<?php namespace Naraki\Core\Composers\Frontend;

use Naraki\Core\Composer;
use Naraki\System\Support\Settings;

class Home extends Composer
{
    /**
     * @var array
     */
    private $data;
    /**
     * @var \Naraki\System\Support\Settings
     */
    private $settings;

    /**
     * @param \Illuminate\View\View $view
     */
    public function compose($view)
    {
        $this->settings = Settings::getSettings('general_formatted');
        $this->setVar('meta_description');
        $this->setVar('meta_jsonld');
        $this->setVar('meta_robots');
        $this->setVar('meta_keywords');
        $this->data['title'] = $this->settings->get('meta_title');

        $this->settings = Settings::getSettings('social_formatted');
        $this->setVar('meta_facebook');
        $this->setVar('meta_twitter');
        if (!is_null($this->data)) {
            $this->addVarsToView($this->data, $view);
        }
    }

    public function setVar($var)
    {
        $cVar = $this->settings->get($var);
        if (!is_null($cVar) && !empty($cVar)) {
            $this->data[$var] = $cVar;
        }
    }
}