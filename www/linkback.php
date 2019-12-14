<?php

use Exception;
use SimpleSAML\Auth;
use SimpleSAML\Error;
use SimpleSAML\Module\authtwitter\Auth\Source\Twitter;

/**
 * Handle linkback() response from Twitter.
 */

if (!array_key_exists('AuthState', $_REQUEST) || empty($_REQUEST['AuthState'])) {
    throw new Error\BadRequest('Missing state parameter on twitter linkback endpoint.');
}
$state = Auth\State::loadState($_REQUEST['AuthState'], Twitter::STAGE_INIT);

// Find authentication source
if (is_null($state) || !array_key_exists(Twitter::AUTHID, $state)) {
    throw new Error\BadRequest('No data in state for ' . Twitter::AUTHID);
}
$sourceId = $state[Twitter::AUTHID];

/** @var \SimpleSAML\Module\authtwitter\Auth\Source\Twitter|null $source */
$source = Auth\Source::getById($sourceId);
if ($source === null) {
    throw new Error\BadRequest(
        'Could not find authentication source with id ' . var_export($sourceId, true)
    );
}

try {
    if (array_key_exists('denied', $_REQUEST)) {
        throw new Error\UserAborted();
    }
    $source->finalStep($state);
} catch (Error\Exception $e) {
    Auth\State::throwException($state, $e);
} catch (Exception $e) {
    Auth\State::throwException(
        $state,
        new Error\AuthSource($sourceId, 'Error on authtwitter linkback endpoint.', $e)
    );
}

Auth\Source::completeAuth($state);
