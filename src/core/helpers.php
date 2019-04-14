<?php

if (!function_exists('media_entity_path')) {
    /**
     * Retrieves the path of an image relative to the public folder
     *
     * @param string|int $entity
     * @param string|int $media_type
     * @param string $image The image file name
     *
     * @return string
     * @see \Naraki\Core\Models\Entity
     * @see \Naraki\Media\Models\MediaCategory
     *
     */
    function media_entity_path($entity, $media_type, $image)
    {
        if (is_numeric($entity)) {
            $entity = \Naraki\Core\Models\Entity::getConstantName($entity);
        }
        if (is_numeric($media_type)) {
            $media_type = \Naraki\Media\Models\Media::getConstantName($media_type);
        }

        if (is_null($image)) {
            return placeholder_image();
        }

        return sprintf('/media/%s/%s/%s', $entity, $media_type, $image);
    }

}

if (!function_exists('media_entity_root_path')) {
    /**
     * Retrieves the path of an image relative to the server root
     *
     * @param int $entity
     * @param int $media_type
     * @param string $image The image file name
     *
     * @return string
     */
    function media_entity_root_path($entity, $media_type, $image = null)
    {
        if (is_numeric($entity)) {
            $entity = \Naraki\Core\Models\Entity::getConstantName($entity);
        }
        if (is_numeric($media_type)) {
            $media_type = \Naraki\Media\Models\Media::getConstantName($media_type);
        }

        return sprintf('%s/media/%s/%s/%s', public_path(), $entity, $media_type, $image);
    }

}

if (!function_exists('encrypt')) {
    /**
     * Encrypt the given value.
     *
     * @param string $value
     *
     * @return string
     */
    function encrypt($value)
    {
        return app('encrypter')->encrypt($value);
    }
}

if (!function_exists('makeHexUuid')) {
    /**
     *
     * @return string The 32 character UUID
     */
    function makeHexUuid()
    {
        try {
            return \Ramsey\Uuid\Uuid::uuid4()->getHex();
        } catch (\Exception $e) {
        }
    }
}

if (!function_exists('makeHexHashedUuid')) {
    /**
     *
     * @return string The 32 character UUID
     * @throws \Exception
     */
    function makeHexHashedUuid()
    {
        return \Ramsey\Uuid\Uuid::uuid5(\Ramsey\Uuid\Uuid::NAMESPACE_DNS,
            \Ramsey\Uuid\Uuid::uuid4()->toString())->getHex();
    }
}

if (!function_exists('slugify')) {
    function slugify($title, $separator = '-', $language = null)
    {
        return \Illuminate\Support\Str::slug(
            $title,
            $separator,
            !is_null($language) ? $language : app()->getLocale()
        );
    }
}

if (!function_exists('route_i18n')) {
    /**
     * Generate the URL to a named route.
     *
     * @param array|string $name
     * @param mixed $parameters
     * @param bool $absolute
     * @return string
     */
    function route_i18n($name, $parameters = [], $absolute = true)
    {
        $locale = app()->getLocale();
        if ($locale == config('app.fallback_locale')) {
            return app('url')->route($name, $parameters, $absolute);
        }
        return app('url')->route(sprintf('%s.%s', $locale, $name), $parameters, $absolute);
    }
}

if (!function_exists('get_page_id')) {
    function get_page_id()
    {
        $router = app('router')->getCurrentRoute();
        if (!is_null($router)) {
            $locale = app()->getLocale();
            $routeName = $router->getName();
            if (strpos($routeName, $locale) === 0) {
                $routeName = substr($routeName, 3);
            }
            return substr(md5($routeName), 0, 10);
        }
        return 'undefined';
    }
}

if (!function_exists('is_hex_uuid_string')) {
    function is_hex_uuid_string($v)
    {
        return is_string($v) && strlen($v) == 32 && ctype_xdigit($v);
    }
}

if (!function_exists('is_img_uuid_string')) {
    /**
     * Image uuids are made of 32 characters of the entity slug plus a 32 char uuid
     * the entity may be shorter than 32 chars, so we can only make sure that the string is at most 64 chars long.
     * Example UUID: "this-is-the-title-of-my-post_cc6c34c3eaa14649b01ebe3dcda689de"
     *
     * @param $v
     * @return bool
     */
    function is_img_uuid_string($v)
    {
        return is_string($v) && strlen($v) <= 64 && ctype_xdigit(substr($v, -32));
    }
}

if (!function_exists('get_locale_presentable_date_format')) {
    function get_locale_date_format()
    {
        switch (app()->getLocale()) {
            case 'fr':
                return 'm F Y @  h:m';
                break;
            default:
                return 'F jS, Y @ h:m';
                break;
        }
    }
}

if (!function_exists('response_json')) {
    function response_json($content = '', $status = 200, array $headers = [])
    {
        return app(\Illuminate\Contracts\Routing\ResponseFactory::class)
            ->json($content, $status, $headers);
    }

}

if (!function_exists('page_title')) {
    function page_title($title)
    {
        return sprintf(
            '%s - %s',
            $title,
            config('app.name')
        );
    }
}

if (!function_exists('viewable')) {
    function viewable($viewable = null)
    {
        return app(\Naraki\Core\Support\Viewable\View::class)->forViewable($viewable);
    }
}

if (!function_exists('placeholder_image')) {
    function placeholder_image()
    {
        return 'data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=';
    }
}

if (!function_exists('placeholder_image_black')) {
    function placeholder_image_black()
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAQAAAAAYLlVAAAAO0lEQVR42u3OMQEAAAwCIO0feovhAwloLlMVEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQWAce/hlAAXSB6WIAAAAASUVORK5CYII=';
    }
}


if (!function_exists('format_duration')) {
    function format_duration(int $duration): string
    {
        $hours = (int)($duration / 60 / 60 / 1000);
        $minutes = (int)($duration / 60 / 1000) - $hours * 60;
        $seconds = (int)($duration / 1000) - $hours * 60 * 60 - $minutes * 60;
        if ($hours > 0) {
            return ($hours == 0 ? '' : $hours . 'h') .
                ($minutes == 0 ? '00m' : ($minutes < 10 ? '0' . $minutes . 'm' : $minutes . 'm')) .
                ($seconds == 0 ? '' : ($seconds < 10 ? '0' . $seconds . 's' : $seconds . 's'));
        } else {
            if ($minutes > 0) {
                return ($minutes < 10 ? $minutes . 'm' : $minutes . 'm') .
                    ($seconds < 10 ? '0' . $seconds . 's' : $seconds . 's');
            } else {
                if ($seconds > 0) {
                    return number_format($duration / 1000, 2) . 's';
                }
            }
        }
        return round($duration) . 'ms';
    }
}
