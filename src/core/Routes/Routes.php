<?php namespace Naraki\Core\Routes;

class Routes
{
    /**
     * @param string $locale
     * @param string $name
     * @return string
     */
    protected static function i18nRouteNames($locale, $name)
    {
        if ($locale != null) {
            return sprintf('%s.%s', $locale, $name);
        }
        return $name;
    }
}