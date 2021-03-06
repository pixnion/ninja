<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Check if we always should use CSRF tokens to secure forms
 */
$config['active'] = true;

/**
 * Session key to hold CSRF token
 */
$config['csrf_token'] = 'csrf_token';

/**
 * token generation timestamp
 */
$config['csrf_timestamp'] = 'csrf_timestamp';

/**
 * csrf token lifetime in seconds
 * For how long to we trust the csrf token?
 */
$config['csrf_lifetime']  = 5400;
