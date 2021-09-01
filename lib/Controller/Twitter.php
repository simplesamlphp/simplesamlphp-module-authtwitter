<?php

declare(strict_types=1);

namespace SimpleSAML\Module\authtwitter\Controller;

use Exception;
use SimpleSAML\Auth;
use SimpleSAML\Configuration;
use SimpleSAML\Error;
use SimpleSAML\HTTP\RunnableResponse;
use SimpleSAML\Module\authtwitter\Auth\Source\Twitter as TwitterSource;
use SimpleSAML\Session;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller class for the authtwitter module.
 *
 * This class serves the different views available in the module.
 *
 * @package simplesamlphp/simplesamlphp-module-twitter
 */
class Twitter
{
    /** @var \SimpleSAML\Configuration */
    protected Configuration $config;

    /** @var \SimpleSAML\Session */
    protected Session $session;


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
        Configuration $config,
        Session $session
    ) {
        $this->config = $config;
        $this->session = $session;
    }


    /**
     * Linkback.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The current request.
     *
     */
    public function linkback(Request $request)
    {
        $authState = $request->get('AuthState');
        if ($authState === null) {
            throw new Error\BadRequest('Missing state parameter on twitter linkback endpoint.');
        }

        $state = Auth\State::loadState($authState, TwitterSource::STAGE_INIT);

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
            if ($request->request->has('denied')) {
                throw new Error\UserAborted();
            }
            $source->finalStep($state, $request);
        } catch (Error\Exception $e) {
            Auth\State::throwException($state, $e);
        } catch (Exception $e) {
            Auth\State::throwException(
                $state,
                new Error\AuthSource($sourceId, 'Error on authtwitter linkback endpoint.', $e)
            );
        }

        return new RunnableResponse([Auth\Source::class, 'completeAuth'], [$state]);
    }
}
