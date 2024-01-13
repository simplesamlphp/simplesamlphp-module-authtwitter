<?php

declare(strict_types=1);

namespace SimpleSAML\Module\authtwitter\Controller;

use Exception;
use SimpleSAML\Auth;
use SimpleSAML\Configuration;
use SimpleSAML\Error;
use SimpleSAML\Module\authtwitter\Auth\Source\Twitter as TwitterSource;
use SimpleSAML\Session;
use Symfony\Component\HttpFoundation\{Request, StreamedResponse};

/**
 * Controller class for the authtwitter module.
 *
 * This class serves the different views available in the module.
 *
 * @package simplesamlphp/simplesamlphp-module-twitter
 */
class Twitter
{
    /**
     * Controller constructor.
     *
     * It initializes the global configuration and session for the controllers implemented here.
     *
     * @param \SimpleSAML\Configuration $config The configuration to use by the controllers.
     * @param \SimpleSAML\Session $session The session to use by the controllers.
     *
     * @throws \Exception
     */
    public function __construct(
        protected Configuration $config,
        protected Session $session
    ) {
    }


    /**
     * Linkback.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The current request.
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function linkback(Request $request): StreamedResponse
    {
        $authState = $request->query->get('AuthState');
        if ($authState === null) {
            throw new Error\BadRequest('Missing state parameter on twitter linkback endpoint.');
        }

        $state = Auth\State::loadState(base64_decode($authState), TwitterSource::STAGE_INIT);

        // Find authentication source
        if (is_null($state) || !array_key_exists(TwitterSource::AUTHID, $state)) {
            throw new Error\BadRequest('No data in state for ' . TwitterSource::AUTHID);
        }

        $sourceId = $state[TwitterSource::AUTHID];

        /** @var \SimpleSAML\Module\authtwitter\Auth\Source\Twitter|null $source */
        $source = Auth\Source::getById($sourceId);

        if ($source === null) {
            throw new Error\BadRequest(
                'Could not find authentication source with id ' . var_export($sourceId, true)
            );
        }

        try {
            $source->finalStep($state, $request);
        } catch (Error\Exception $e) {
            Auth\State::throwException($state, $e);
        } catch (Exception $e) {
            Auth\State::throwException(
                $state,
                new Error\AuthSource($sourceId, 'Error on authtwitter linkback endpoint.', $e)
            );
        }

        return new StreamedResponse(
            function () use (&$state): never {
                Auth\Source::completeAuth($state);
            }
        );
    }
}
