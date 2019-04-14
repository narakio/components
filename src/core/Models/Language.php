<?php namespace Naraki\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    const DB_LANGUAGE_ENGLISH_ID = 1;
    const DB_LANGUAGE_FRENCH_ID = 44;

    public $primaryKey = 'product_id';
    public $timestamps = false;

    /**
     * @return int
     */
    public static function getAppLanguageId(): int
    {
        switch (app()->getLocale()) {
            case "fr":
                return self::DB_LANGUAGE_FRENCH_ID;
                break;
            default:
            case "en":
                return self::DB_LANGUAGE_ENGLISH_ID;
                break;
        }
    }

    public static function getLanguageName($int, $iso639 = true)
    {
        switch ($int) {
            case self::DB_LANGUAGE_FRENCH_ID:
                return $iso639 ? 'fr_FR' : 'fr';
                break;
            default:
            case self::DB_LANGUAGE_ENGLISH_ID:
                return $iso639 ? 'en_US' : 'en';
                break;
        }
    }

    public static function getAppLanguageISO639()
    {
        switch (app()->getLocale()) {
            case "fr":
                return 'fr_FR';
                break;
            default:
            case "en":
                return 'en_US';
                break;
        }
    }

    /**
     * @return array
     */
    public static function getOtherLocales()
    {
        $locales = config('app.locales');
        unset($locales[app()->getLocale()]);
        return array_keys($locales);
    }

}
