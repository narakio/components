<?php namespace Naraki\Core\Support\Frontend\Jsonld\Schemas\Thing\CreativeWork;

use Naraki\Core\Support\Frontend\Jsonld\Schemas\Thing\Organization\Organization;
use Naraki\Core\Support\Frontend\Jsonld\Schemas\Thing\Thing;

class CreativeWork extends Thing
{
    static $websiteList = ['WebSite' => '', 'Blog' => ''];
    static $publisherAuthorThingList = ['Person' => ''];
    protected $about;
    protected $aggregateRating;
    protected $alternativeHeadline;
    protected $author;
    protected $comment;
    protected $commentCount;
    protected $creator;
    protected $dateCreated;
    protected $dateModified;
    protected $datePublished;
    protected $headline;
    protected $inLanguage;
    protected $keywords;
    protected $mainEntity;
    protected $publisher;
    protected $review;
    protected $text;
    protected $thumbnailUrl;
    protected $video;

    public function setPublisher($values, $class = null)
    {
        if (is_null($class)) {
            return $values;
        }
        if (isset(static::$publisherAuthorThingList[$class])) {
            return $this->setValuesDefault(
                sprintf('\Naraki\Core\Support\Frontend\Jsonld\Schemas\Thing\%s', $class), $values);
        } else {
            return $this->setValuesDefault(
                Organization::getClassName($class), $values
            );
        }
    }

    public function setAuthor($values, $class)
    {
        if (isset(static::$publisherAuthorThingList[$class])) {
            return $this->setValuesDefault(
                sprintf('\Naraki\Core\Support\Frontend\Jsonld\Schemas\Thing\%s', $class), $values);
        } else {
            return $this->setValuesDefault(
                Organization::getClassName($class), $values
            );
        }
    }

    public static function getClassName($value)
    {
        if (!isset(static::$websiteList[$value])) {
            throw new \InvalidArgumentException('Invalid website type for structured data.');
        }
        return sprintf('\Naraki\Core\Support\Frontend\Jsonld\Schemas\Thing\CreativeWork\%s', $value);
    }


}