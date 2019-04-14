<?php namespace Naraki\Sentry\Controllers\Ajax;

use Illuminate\Http\Response;
use Naraki\Core\Controllers\Admin\Controller;
use Naraki\Sentry\Facades\User as UserProvider;

class Person extends Controller
{
    /**
     * @param string $search
     * @param int $limit
     * @return \illuminate\http\response
     */
    public function search($search, $limit)
    {
        return response(
            UserProvider::person()->search(
                preg_replace('/[^\w\s\-\']/', '', strip_tags($search)),
                intval($limit)
            )->get(), response::HTTP_OK);
    }

}
