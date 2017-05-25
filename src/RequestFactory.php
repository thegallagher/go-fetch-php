<?php

namespace TheGallagher\GoFetch;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\Psr7\UriResolver;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * Create HTTP requests to the GoFetch API
 */
class RequestFactory
{
  /**
   * Production server URI
   */
  const PRODUCTION_URI = 'https://go-fetch.com.au';

  /**
   * Testing server URI
   */
  const TESTING_URI = 'http://test.go-fetch.com.au';

  /**
   * Session email
   *
   * @var string|null
   */
  protected $email;

  /**
   * Session token
   *
   * @var string|null
   */
  protected $token;

  /**
   * Base URI
   *
   * @var UriInterface
   */
  protected $baseUri;

  /**
   * RequestFactory constructor.
   *
   * @param string|null $email
   * @param string|null $token
   * @param UriInterface|string $baseUri
   */
  public function __construct(string $email = null, string $token = null, $baseUri = self::PRODUCTION_URI)
  {
    $this->setEmail($email);
    $this->setToken($token);
    $this->setBaseUri($baseUri);
  }

  /**
   * Get the session email
   *
   * @return null|string
   */
  public function getEmail()
  {
    return $this->email;
  }

  /**
   * Set the session email
   *
   * @param null|string $email
   */
  public function setEmail($email)
  {
    $this->email = $email;
  }

  /**
   * Get the session token
   *
   * @return string|null
   */
  public function getToken()
  {
    return $this->token;
  }

  /**
   * Set the session token
   *
   * @param string|null $token
   */
  public function setToken($token)
  {
    $this->token = $token;
  }

  /**
   * Get the base URI
   *
   * @return UriInterface
   */
  public function getBaseUri() : UriInterface
  {
    return $this->baseUri;
  }

  /**
   * Set the base URI
   *
   * @param UriInterface|string $baseUri
   */
  public function setBaseUri($baseUri)
  {
    $this->baseUri = \GuzzleHttp\Psr7\uri_for($baseUri);;
  }

  /**
   * Create a request
   *
   * @param string $method
   * @param UriInterface|string $uri
   *
   * @return RequestInterface
   */
  public function createRequest(string $method, $uri) : RequestInterface
  {
    $uri = \GuzzleHttp\Psr7\uri_for($uri);
    $baseUri = $this->getBaseUri();
    if ($baseUri) {
      $uri = UriResolver::resolve($baseUri, $uri);
    }

    $headers = [];
    if (strtolower($method) !== 'get') {
      $headers['Content-Type'] = 'application/json';
    }

    return new Request($method, $uri, $headers);
  }

  /**
   * Create the request to get a session token
   *
   * @param string $email
   * @param string $password
   *
   * @return RequestInterface
   *
   * @see https://github.com/GoFetchDeliveries/gofetch-api/blob/master/sessions.md
   */
  public function createSessionRequest(string $email, string$password) : RequestInterface
  {
    $body = $this->prepareBody([
      'email' => $email,
      'password' => $password,
    ]);
    return $this->createRequest('post', '/public_api/v1/sessions')->withBody($body);
  }

  /**
   * Create a test request
   *
   * @return RequestInterface
   *
   * @see https://github.com/GoFetchDeliveries/gofetch-api/blob/master/hello_world.md
   */
  public function createHelloWorldRequest() : RequestInterface
  {
    return $this->createRequest('get', '/public_api/v1/hello_world');
  }

  /**
   * Create the request to sign in
   *
   * @param string $email
   * @param string $password
   *
   * @return RequestInterface
   *
   * @see https://github.com/GoFetchDeliveries/gofetch-api/blob/master/v1/users.md
   */
  public function createSignInRequest(string $email, string $password) : RequestInterface
  {
    $body = $this->prepareBody([
      'user' => [
        'email' => $email,
        'password' => $password,
      ]
    ]);
    return $this->createRequest('post', '/api/v1/users.json')->withBody($body);
  }

  /**
   * Create a request for item types
   *
   * @return RequestInterface
   *
   * @see https://github.com/GoFetchDeliveries/gofetch-api/blob/master/v1/item_types.md
   */
  public function createItemTypesRequest() : RequestInterface
  {
    $request = $this->createRequest('get', '/api/v1/item_types.json');
    return $this->authenticateRequest($request);
  }

  /**
   * Create a request for job price calculation
   *
   * @param array $query
   *
   * @return RequestInterface
   *
   * @see https://github.com/GoFetchDeliveries/gofetch-api/blob/master/v2/jobs.md
   */
  public function createJobCalculationRequest(array $query) : RequestInterface
  {
    $uri =  \GuzzleHttp\Psr7\uri_for('/api/v2/jobs/calculate.json')->withQuery($query);
    $request = $this->createRequest('get', $uri);
    return $this->authenticateRequest($request);
  }

  /**
   * Create a request to create a new job
   *
   * @param array $body
   *
   * @return RequestInterface
   *
   * @see https://github.com/GoFetchDeliveries/gofetch-api/blob/master/v2/my/customer/jobs.md
   */
  public function createJobCreateRequest(array $body) : RequestInterface
  {
    $request = $this->createRequest('post', '/api/v1/my/customer/jobs.json');
    $body = $this->prepareBody($body);
    return $this->authenticateRequest($request)->withBody($body);
  }

  /**
   * Create a stream for the request body
   *
   * @param mixed $body
   *
   * @return Stream
   */
  protected function prepareBody($body)
  {
    return \GuzzleHttp\Psr7\stream_for(\GuzzleHttp\json_encode($body));
  }

  /**
   * Add authentication headers to a request
   *
   * @param RequestInterface $request
   *
   * @return RequestInterface
   */
  public function authenticateRequest(RequestInterface $request)
  {
    return $request->withHeader('X-User-Email', $this->getEmail())->withHeader('X-User-Token', $this->getToken());
  }
}