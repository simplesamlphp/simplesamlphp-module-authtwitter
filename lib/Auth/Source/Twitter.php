<?php

namespace SimpleSAML\Module\authtwitter\Auth\Source;

use League\OAuth1\Client\Server\Twitter as TwitterServer;
use SimpleSAML\Assert\Assert;
use SimpleSAML\Auth;
use SimpleSAML\Configuration;
use SimpleSAML\Error;
use SimpleSAML\Logger;
use SimpleSAML\Module;
use SimpleSAML\Utils;
use Symfony\Component\HttpFoundation\Request;

/**
 * Authenticate using Twitter.
 *
 * @package simplesamlphp/simplesamlphp-module-authtwitter
 */

class Twitter extends Auth\Source
{
    /**
     * The string used to identify our states.
     */
    public const STAGE_INIT = 'twitter:init';

    /**
     * The key of the AuthId field in the state.
     */
    public const AUTHID = 'twitter:AuthId';

    /** @var string */
    private string $key;

    /** @var string */
    private string $secret;

    /** @var string */
    private string $scope;

    /** @var bool */
    private bool $force_login;

    /**
     * Constructor for this authentication source.
     *
     * @param array $info  Information about this authentication source.
     * @param array $config  Configuration.
     */
    public function __construct(array $info, array $config)
    {
        // Call the parent constructor first, as required by the interface
        parent::__construct($info, $config);

        $configObject = Configuration::loadFromArray(
            $config,
            'authsources[' . var_export($this->authId, true) . ']'
        );

        $this->key = $configObject->getString('key');
        $this->secret = $configObject->getString('secret');
        $this->scope = $configObject->getString('scope', null);
        $this->force_login = $configObject->getBoolean('force_login', false);
    }


    /**
     * Log-in using Twitter platform
     *
     * @param array &$state  Information about the current authentication.
     */
    public function authenticate(array &$state): void
    {
        $this->temporaryCredentials($state);
    }


    /**
     * Retrieve temporary credentials
     *
     * @param array &$state  Information about the current authentication.
     */
    private function temporaryCredentials(array &$state): void
    {
        // We are going to need the authId in order to retrieve this authentication source later
        $state[self::AUTHID] = $this->authId;

        $stateId = base64_encode(Auth\State::saveState($state, self::STAGE_INIT));

        $server = new TwitterServer(
            [
                'identifier' => $this->key,
                'secret' => $this->secret,
                'callback_uri' => Module::getModuleURL('authtwitter/linkback')
                    . '?AuthState=' . $stateId . '&force_login=' . strval($this->force_login),
                'scope' => $this->scope,
            ]
        );

        // First part of OAuth 1.0 authentication is retrieving temporary credentials.
        // These identify you as a client to the server.
        $temporaryCredentials = $server->getTemporaryCredentials();

        $state['authtwitter:authdata:requestToken'] = serialize($temporaryCredentials);
        Auth\State::saveState($state, self::STAGE_INIT);

        $server->authorize($temporaryCredentials);
        exit;
    }


    /**
     * @param array &$state
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function finalStep(array &$state, Request $request): void
    {
        $requestToken = unserialize($state['authtwitter:authdata:requestToken']);

        $oauth_token = $request->get('oauth_token');
        if ($oauth_token === null) {
            throw new Error\BadRequest("Missing oauth_token parameter.");
        }

        if ($requestToken->getIdentifier() !== $oauth_token) {
            throw new Error\BadRequest("Invalid oauth_token parameter.");
        }

        $oauth_verifier = $request->get('oauth_verifier');
        if ($oauth_verifier === null) {
            throw new Error\BadRequest("Missing oauth_verifier parameter.");
        }

        $server = new TwitterServer(
            [
                'identifier' => $this->key,
                'secret' => $this->secret,
            ]
        );

        $tokenCredentials = $server->getTokenCredentials(
            $requestToken,
            $request->get('oauth_token'),
            $request->get('oauth_verifier')
        );

        $state['token_credentials'] = serialize($tokenCredentials);
        $userdata = $server->getUserDetails($tokenCredentials);

        $attributes = [];

        foreach ($userdata->getIterator() as $key => $value) {
            if (is_string($value) && (strlen($value) > 0)) {
                $attributes['twitter.' . $key] = [$value];
            } else {
                // Either the urls or the extra array
            }
        }

        $state['Attributes'] = $attributes;
    }
}
