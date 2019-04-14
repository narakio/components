<?php namespace Naraki\Oauth\Socialite;

use Carbon\Carbon;
use Naraki\Oauth\Exceptions\EmailNotVerified;

/**
 * Class GoogleUser
 * @package Naraki\Oauth\Socialite
 * @see https://openid.net/specs/openid-connect-core-1_0.html#IDToken
 * @see https://tools.ietf.org/html/rfc7519
 */
class GoogleUser
{
    /**
     * The values as returned by Google after the token has been verified
     *
     * @var array
     */
    private $rawApiLoginTicket;
    /**
     * Issuer Identifier for the Issuer of the response. The iss value is a case sensitive URL
     * using the https scheme that contains scheme, host, and optionally, port number and path components
     * and no query or fragment components.
     *
     * @var string
     */
    private $issuer;
    /**
     * Time at which this token can be used, i.e. this token can't be used before this timestamp.
     *
     * @var int
     */
    private $notBefore;
    /**
     * Audience(s) that this ID Token is intended for. It MUST contain the OAuth 2.0 client_id of the Relying Party
     * as an audience value. It MAY also contain identifiers for other audiences.
     * In the general case, the aud value is an array of case sensitive strings.
     * In the common special case when there is one audience, the aud value MAY be a single case sensitive string.
     *
     * As of the time of this writing, seems to be set 5 minutes before issuance date.
     *
     * @var string
     */
    private $audience;
    /**
     * Subject Identifier. A locally unique and never reassigned identifier within the Issuer for the End-User,
     * which is intended to be consumed by the Client, e.g., 24400320 or AItOawmwtWwcT0k51BayewNvutrJUqsvl6qs7A4.
     * It MUST NOT exceed 255 ASCII characters in length. The sub value is a case sensitive string.
     *
     * @var string
     */
    private $subject;
    /**
     * User email used for authentication
     *
     * @var string
     */
    private $email;
    /**
     * Whether the email has been verified.
     *
     * @var bool
     */
    private $emailVerified;
    /**
     * Authorized party - the party to which the ID Token was issued.
     * If present, it MUST contain the OAuth 2.0 Client ID of this party.
     * This Claim is only needed when the ID Token has a single audience value
     * and that audience is different than the authorized party.
     * It MAY be included even when the authorized party is the same as the sole audience.
     * The azp value is a case sensitive string containing a StringOrURI value.
     *
     * @var string
     */
    private $authorizedParty;
    /**
     * Authenticated user full name, i.e. first name AND last name
     *
     * @var string
     */
    private $fullName;
    /**
     * URL to the user avatar.
     *
     * @var string
     */
    private $avatar;
    /**
     * Authenticated user first name.
     *
     * @var string
     */
    private $firstName;
    /**
     * Authenticated user last name.
     * @var string
     */
    private $lastName;
    /**
     * Issuer Identifier for the Issuer of the response.
     * The iss value is a case sensitive URL using the https scheme that contains scheme, host,
     * and optionally, port number and path components and no query or fragment components.
     *
     * @var int
     */
    private $issuedAt;
    /**
     * Expiration time on or after which the ID Token MUST NOT be accepted for processing.
     * The processing of this parameter requires that the current date/time MUST be before the expiration date/time
     * listed in the value. Implementers MAY provide for some small leeway, usually no more than a few minutes,
     * to account for clock skew. Its value is a JSON number representing the number of seconds
     * from 1970-01-01T0:0:0Z as measured in UTC until the date/time.
     *
     * @var int
     */
    private $expiresAt;
    /**
     * A unique identifier for the token, which can be used to prevent reuse of the token.
     * These tokens MUST only be used once, unless conditions for reuse were negotiated between the parties;
     * any such negotiation is beyond the scope of this specification.
     *
     * As of the time of this writing, seems to be set one hour after issuance date.
     *
     * @var string
     */
    private $jwtId;
    /**
     * @var array
     */
    private $keyMap = [
        'iss' => 'issuer',
        'nbf' => 'notBefore',
        'aud' => 'audience',
        'sub' => 'subject',
        'email' => 'email',
        'email_verified' => 'emailVerified',
        'azp' => 'authorizedParty',
        'name' => 'fullName',
        'picture' => 'avatar',
        'given_name' => 'firstName',
        'family_name' => 'lastName',
        'iat' => 'issuedAt',
        'exp' => 'expiresAt',
        'jti' => 'jwtId'
    ];

    /**
     *
     * @param array $rawApiLoginTicket
     * @throws \Naraki\Oauth\Exceptions\EmailNotVerified
     */
    public function __construct(array $rawApiLoginTicket)
    {
        $this->rawApiLoginTicket = $rawApiLoginTicket;
        $this->parseTicket();
    }

    /**
     * @throws \Naraki\Oauth\Exceptions\EmailNotVerified
     */
    private function parseTicket()
    {
        foreach ($this->rawApiLoginTicket as $key => $value) {
            if (isset($this->keyMap[$key])) {
                $this->{$this->keyMap[$key]} = call_user_func([$this, 'process' . ucfirst($this->keyMap[$key])],
                    $value);
            }
        }
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getAttribute($name)
    {
        if (isset($this->{$name})) {
            return $this->{$name};
        }
        return null;
    }

    /**
     * @param int $value
     * @return string
     */
    public function processIssuedAt($value): string
    {
        return Carbon::createFromTimestamp($value)->format('Y-m-d H:i:s');
    }

    /**
     * @param int $value
     * @return string
     */
    public function processNotBefore($value): string
    {
        return Carbon::createFromTimestamp($value)->format('Y-m-d H:i:s');
    }

    /**
     * @param int $value
     * @return string
     */
    public function processExpiresAt($value): string
    {
        return Carbon::createFromTimestamp($value)->format('Y-m-d H:i:s');
    }

    /**
     * @param $value
     * @return bool
     * @throws \Naraki\Oauth\Exceptions\EmailNotVerified
     */
    public function processEmailVerified($value): bool
    {
        if ($value === false) {
            throw new EmailNotVerified();
        }
        return $value;
    }

    /**
     * @param $name
     * @param $value
     * @return mixed
     */
    public function __call($name, $value)
    {
        return $value[0];
    }

    public function __get($name)
    {
        return null;
    }


}