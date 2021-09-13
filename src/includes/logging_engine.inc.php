<?php
/**
 * Logging engine with monolog and system event logger .
 */

use sgoettsch\monologRotatingFileHandler\Handler\monologRotatingFileHandler;
use Monolog\Logger;

try {
	// Create a log channel
	$GLOBALS['log'] = new Logger('center');
	
	// Instantiate handler
	$handler = new monologRotatingFileHandler($GLOBALS['config']['cms']['logs'] . '/app.log');
	// Set handler
	$GLOBALS['log']->pushHandler($handler);
} catch (\Exception $e) {}