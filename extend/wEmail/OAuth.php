<?php

namespace wEmail;
class OAuth
{
    protected $provider;
    protected $oauthToken;
    protected $oauthUserEmail = '';
    protected $oauthClientSecret = '';
    protected $oauthClientId = '';
    protected $oauthRefreshToken = '';
    public function __construct($options)
    {
        $this->provider = $options['provider'];
        $this->oauthUserEmail = $options['userName'];
        $this->oauthClientSecret = $options['clientSecret'];
        $this->oauthClientId = $options['clientId'];
        $this->oauthRefreshToken = $options['refreshToken'];
    }

    protected function getGrant()
    {
        /** @noinspection PhpUndefinedClassInspection */
        return new RefreshToken();
    }

    protected function getToken()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->provider->getAccessToken(
            $this->getGrant(),
            ['refresh_token' => $this->oauthRefreshToken]
        );
    }
    public function hasExpired()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getOauth64()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        if (null === $this->oauthToken or $this->oauthToken->hasExpired()) {
            $this->oauthToken = $this->getToken();
        }

        return base64_encode(
            'user=' .
            $this->oauthUserEmail .
            "\001auth=Bearer " .
            $this->oauthToken .
            "\001\001"
        );
    }
}
