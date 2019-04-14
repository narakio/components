<?php namespace Naraki\Core\Support\Viewable;

use Illuminate\Support\Facades\Cookie;

class VisitorCookieRepository
{
    /**
     * The visitor cookie key.
     *
     * @var string
     */
    protected $key;

    /**
     * Create a new view session history instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->key = config('eloquent-viewable.visitor_cookie_key');
    }

    /**
     * Get the visitor's unique key.
     *
     * @return string
     * @throws \Exception
     */
    public function get()
    {
        if (! Cookie::has($this->key)) {
            Cookie::queue($this->key, $uniqueString = $this->generateUniqueString(), $this->expiration());

            return $uniqueString;
        }

        return Cookie::get($this->key);
    }

    /**
     * Generate a unique visitor string.
     *
     * @return string
     * @throws \Exception
     */
    protected function generateUniqueString(): string
    {
        return makeHexUuid();
    }

    /**
     * Get the expiration in seconds.
     *
     * @return int
     */
    protected function expiration(): int
    {
        return 157680000; // aka 5 years
    }
}
