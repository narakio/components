<?php namespace Naraki\Core\Support\Vendor;

class GoogleRecaptcha
{
    private static $siteVerifyUrl =
        "https://www.google.com/recaptcha/api/siteverify?";
    private $googleResponse;
    private $clientIP;
    private $secret;
    private $payload;

    public static function check($googleResponse, $clientIP, $secret)
    {
        if (!$googleResponse || empty($googleResponse)) {
            return false;
        } else {
            $validator = new static($googleResponse, $clientIP, $secret);
            $response = json_decode($validator->submitClientValidationRequest(), true);
            if (!isset($response ['success']) || trim($response ['success']) != true) {
                $errors = "";
                if (isset($response['error-codes'])) {
                    $errors = implode(",", $response['error-codes']);
                }

                return $errors;
            }
        }
        return true;
    }

    public function __construct($googleResponse, $clientIP, $secret)
    {
        $this->googleResponse = $googleResponse;
        $this->clientIP = $clientIP;
        $this->secret = $secret;

        $this->payload = $this->buildQueryString([
            'secret' => $secret,
            'remoteip' => $clientIP,
            'response' => $googleResponse
        ]);
    }

    private function buildQueryString(array $data)
    {
        $qs = "";
        foreach ($data as $key => $val) {
            $qs .= $key . '=' . urlencode(stripslashes($val)) . '&';
        }
        // Remove the last '&' from the string & submit
        return substr($qs, 0, strlen($qs) - 1);
    }

    public function submitClientValidationRequest()
    {
        $response = file_get_contents(static::$siteVerifyUrl . $this->payload);
        return $response;
    }

    public function getGoogleVerifyURL()
    {
        return static::$siteVerifyUrl . $this->payload;
    }

    /**
     * Method called by laravel's validator that allows to execute internal methods
     * without writing google code in our request classes.
     *
     * @see \Naraki\Sentry\Requests\Frontend\CreateUser
     * @param $attribute
     * @param $value
     * @param $parameters
     * @param $validator
     * @return bool
     */
    public static function validate($attribute, $value, $parameters, $validator)
    {
        $result = static::check($value, 'localhost', env('RECAPTCHA_SECRET_KEY'));
        if ($result === true) {
            return true;
        }
        $validator->errors()->add('recaptcha', '');
        return false;
    }

}