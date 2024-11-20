<?php

/**
 * Check the http response codes.
 */
class HttpCodeCheck {

  /**
   * Logger instance.
   *
   * @var mixed
   */
  private mixed $logger;

  public function __construct($logger) {
    $this->logger = $logger;
  }

  /**
   * Executed on successfully responses.
   */
  public function fulfilled($response, $index, $urls): void {
    $this->logger->info($urls[$index] . ';' . $response->getStatusCode());
  }

  /**
   * Executed on error responses.
   */
  public function rejected($reason, $index, $urls): void {
    $this->logger->info($urls[$index] . ';ERROR');
  }

}
