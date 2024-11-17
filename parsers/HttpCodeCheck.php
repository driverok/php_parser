<?php

/**
 * Check the http response codes.
 */
class HttpCodeCheck {

  /**
   * Executed on successfully responses.
   */
  public function fulfilled($response, $index, $urls): void {
    echo $urls[$index] . ' (' . $response->getStatusCode() . ')' . PHP_EOL;
  }

  /**
   * Executed on error responses.
   */
  public function rejected($reason, $index): void {
    $url = $reason->getRequest()->getUri();
    echo "URL #$index: $url - Error: " . $reason->getMessage() . PHP_EOL;
  }

}
