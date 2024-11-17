<?php

/**
 * @file
 * Parser URLs from CSV file.
 */

require 'vendor/autoload.php';
require 'helpers.php';

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

if (count($argv) < 2) {
  show_help();
  exit;
}

$args = parse_args($argv);
$concurrency = $args['concurrency'] ?? 5;
$timeout = $args['timeout'] ?? 10;
$ssl_verify = $args['ssl_verify'] ?? FALSE;
$redirects = $args['allow_redirects'] ?? TRUE;
$csv_file = $args['csv_file'];
$parser_name = $args['parser'];

if (empty($csv_file) || !is_file($csv_file)) {
  echo ('Please provide the csv file with URLs');
  exit;
}

if (!isset($args['parser'])) {
  echo "No parser specified. Use --parser=ParserName.\n";
  exit;
}

$logger = new Logger('parser');
$cli_handler = new StreamHandler("php://stdout");
$output = '%message%' . PHP_EOL;
$cli_handler->setFormatter(new LineFormatter($output));
$logger->pushHandler($cli_handler);

$output = '%datetime% > %message%' . PHP_EOL;
$dateFormat = "Y-n-j, g:i a";
$formatter = new LineFormatter(
  $output,
  $dateFormat,
  TRUE,
  TRUE
);
$file_handler = new StreamHandler('./parser.log');
$file_handler->setFormatter($formatter);
$logger->pushHandler($file_handler);

// Check if the specified parser class exists and is instantiable.
if (class_exists($parser_name)
  && method_exists($parser_name, 'fulfilled')
  && method_exists($parser_name, 'rejected')) {
  $parser = new $parser_name($logger);
}
else {
  echo 'Parser ' . $parser_name . ' does not exist or does not have a fulfilled and rejected methods' . PHP_EOL;
  exit;
}

$client = new Client([
  'http_errors' => FALSE,
  'timeout' => (int) $timeout,
  'verify' => (bool) $ssl_verify,
  'allow_redirects' => (bool) $redirects,
]);

$data = file_get_contents($csv_file);
$rows = explode(PHP_EOL, $data);
$urls = [];
foreach ($rows as $row) {
  $csv_arr = str_getcsv($row);
  if (!empty($csv_arr[0])) {
    $urls[] = $csv_arr[0];
  }
}
$logger->info('Processing ' . count($urls) . ' URLs');
$requests = static function ($urls) {
  foreach ($urls as $uri) {
    yield new Request('GET', $uri);
  }
};

$pool = new Pool($client, $requests($urls), [
  'concurrency' => $concurrency,
  'fulfilled' => static function ($response, $index) use ($parser, $urls) {
    $parser->fulfilled($response, $index, $urls);
  },
  'rejected' => [$parser, 'rejected'],
]);

$pool->promise()->wait();
$logger->info(' All ' . count($urls) . ' URLs are processed');
