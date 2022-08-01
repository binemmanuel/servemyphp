<?php

/**
 * System configurations.
 * 
 * @package ZerabTECH 
 * @author  Bin Emmanuel https://github.com/binemmanuel
 * @link    https://github.com/
 *
 * @version	1.0
 */

/**
 * For developers: ZerabTech debugging mode.
 *
 * Configure error reporting options
 * Change this to false to enable the display of notices during development.
 */
define('IS_ENV_PRODUCTION', false);

// Turn on error reporting
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', !IS_ENV_PRODUCTION);

// Set error log.
ini_set('error_log', './log/php-error.txt');

// ** Set time zone to use date/time functions without warnings ** //
date_default_timezone_set('Africa/Lagos'); //http://www.php.net/manual/en/timezones.php


define('UPLOADS_DIR', __DIR__ . '/public_html/uploads/');

require __DIR__ . '/../vendor/autoload.php';


// \Dotenv\Dotenv::createImmutable(__DIR__)->load();
