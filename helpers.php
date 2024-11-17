<?php

/**
 * @file
 * Helper functions for parser.
 */

use GuzzleHttp\Psr7\Request;
use JetBrains\PhpStorm\NoReturn;

/**
 * Parse CLI named params.
 *
 * @param array $argv
 *   CLI params.
 *
 * @return array
 *   Array of param=value pairs.
 */
function parse_args(array $argv): array {
  // Remove the script name from the array.
  array_shift($argv);
  $params = [];

  foreach ($argv as $arg) {
    if (preg_match('/--([^=]+)=(.*)/', $arg, $matches)) {
      $params[$matches[1]] = $matches[2];
    }
  }

  return $params;
}

// Autoload parser classes from the 'parsers' directory.
spl_autoload_register(static function ($className) {
  $file = __DIR__ . "/parsers/" . $className . '.php';
  if (file_exists($file)) {
    require_once $file;
  }
});

/**
 * Guzzle Requests generator.
 *
 * @param array $urls
 *   Array of URLS.
 *
 * @return \Generator
 *   Guzzle response object.
 */
function yield_request($urls): Generator {
  foreach ($urls as $uri) {
    yield new Request('GET', $uri);
  }
}

/**
 * Display help message.
 */
#[NoReturn] function show_help(): void {
  $helpText = <<<EOT
Usage: php parser.php parameters

Description:
Run this script with the name of the parser class to parse URLs from csv file.

Parameters:
--parser: Name of the class that contains 'fulfilled' and 'rejected' methods to handle the responses.
--csv_file: csv file containing the URLs to check
--concurrency: number of async threads
--timeout: timeout for Guzzle requests
--ssl_verify: skip SSL check
--allow_redirects: following redirects

Example:
php parser.php --parser=HttpCodeCheck --csv_file=file.csv --concurrency=10 
--timeout=10 ssl_verify=false --allow_redirects=true

This command will use 'HttpCodeCheck' class from parsers folder for handling the HTTP request responses.

EOT;
  echo $helpText;
}
