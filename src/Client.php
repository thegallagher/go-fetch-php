<?php

namespace TheGallagher\GoFetch;

use GuzzleHttp\ClientInterface as GuzzleClient;
use GuzzleHttp\Exception\ClientException as GuzzleClientException;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use Psr\Http\Message\RequestInterface as HttpRequest;
use Psr\Http\Message\ResponseInterface as HttpResponse;
use TheGallagher\GoFetch\Exception\NotFoundException;
use TheGallagher\GoFetch\Exception\RequestException;
use TheGallagher\GoFetch\Exception\UnauthorizedException;
use TheGallagher\GoFetch\Exception\UnprocessableEntityException;

/**
 * GoFetch Client
 */
class Client
{
  /**
   * Guzzle client used to send requests
   *
   * @var GuzzleClient
   */
  protected $httpClient;

  /**
   * Client constructor.
   *
   * @param GuzzleClient $httpClient
   */
  public function __construct(GuzzleClient $httpClient)
  {
    $this->httpClient = $httpClient;
  }

  /**
   * Get the Guzzle client used to send requests
   *
   * @return GuzzleClient
   */
  public function getHttpClient() : GuzzleClient
  {
    return $this->httpClient;
  }

  /**
   * Set the Guzzle client used to send requests
   *
   * @param GuzzleClient $httpClient
   */
  public function setHttpClient(GuzzleClient $httpClient)
  {
    $this->httpClient = $httpClient;
  }

  /**
   * Send a request and return the decoded request body
   *
   * @param HttpRequest $request
   *
   * @return mixed
   *
   * @throws RequestException
   */
  public function send(HttpRequest $request)
  {
    try {
      $response = $this->httpClient->send($request, ['http_errors' => true]);
    } catch (GuzzleRequestException $e) {
      throw $this->prepareException($e);
    }

    return $this->prepareResponse($response);
  }

  /**
   * Decode the body of the response
   *
   * @param HttpResponse $response
   *
   * @return mixed
   */
  protected function prepareResponse(HttpResponse $response)
  {
    try {
      $data = \GuzzleHttp\json_decode($response->getBody());
    } catch (\InvalidArgumentException $e) {
      throw $this->prepareException($e);
    }

    return $data;
  }

  /**
   * Transform an exception into a GoFetch request exception
   *
   * @param \Exception $e
   *
   * @return RequestException
   */
  protected function prepareException(\Exception $e) : RequestException
  {
    if (!($e instanceof GuzzleClientException)) {
      return new RequestException($e->getMessage(), $e->getCode(), $e);
    }

    $body = $this->prepareResponse($e->getResponse());
    $message = $body->error ?? $e->getMessage();

    switch ($e->getCode()) {
      case 401:
        return new UnauthorizedException($message, $e);

      case 404:
        return new NotFoundException($message, $e);

      case 422:
        return new UnprocessableEntityException($message, $e);
    }

    return new RequestException($message, $e->getCode(), $e);
  }
}