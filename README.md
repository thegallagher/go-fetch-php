# GoFetch API Wrapper for PHP

This library should be considered alpha and could change at any time. It is recommended that you specify a commit when using composer.

## Installation

```
composer require thegallagher/gofetch:dev-master@dev
```

## Usage Examples

```php
<?php
// Include composer autoloader
require 'vendor/autoload.php';

use TheGallagher\GoFetch\Client as GoFetchClient;
use TheGallagher\GoFetch\RequestFactory;
use GuzzleHttp\Client as GuzzleClient;

$email = 'user@test.com';
$password = 'P@ssw0rd';

// Create GoFetch client
$guzzleClient = new GuzzleClient();
$goFetchClient = new GoFetchClient($guzzleClient);

// Create a session
// See: https://github.com/GoFetchDeliveries/gofetch-api/blob/master/sessions.md
$requestFactory = new RequestFactory(null, null, RequestFactory::TESTING_URI);
$sessionRequest = $requestFactory->createSessionRequest($email, $password);
$sessionResponse = $goFetchClient->send($sessionRequest);
$token = $sessionResponse->authentication_token; // You should store this token for future requests

// Calculate Job Price
// See: https://github.com/GoFetchDeliveries/gofetch-api/blob/master/v2/jobs.md
$requestFactory = new RequestFactory($email, $token, RequestFactory::TESTING_URI);
$calculateRequest = $requestFactory->createJobCalculationRequest([
    'distance_meters' => 802,
    'item_weight' => 12,
    'lat' => -37.8278185,
    'lon' => 144.9666907,
]);
$calculateResponse = $goFetchClient->send($calculateRequest);
echo '$' . number_format($calculateResponse->price_cents / 100, 2); // $9.66

// Other requests
// See: https://github.com/GoFetchDeliveries/gofetch-api/blob/master/hello_world.md
$helloRequest = $requestFactory->createHelloWorldRequest();

// See: https://github.com/GoFetchDeliveries/gofetch-api/blob/master/v1/users.md
$signInRequest = $requestFactory->createSignInRequest($email, $password);
 
// See: https://github.com/GoFetchDeliveries/gofetch-api/blob/master/v1/item_types.md
$itemTypesRequest = $requestFactory->createItemTypesRequest(); 

// See https://github.com/GoFetchDeliveries/gofetch-api/blob/master/v2/my/customer/jobs.md
$createJobRequest = $requestFactory->createJobCreateRequest([/* job body */]);

// For undocumented, new or unimplemented request types
$undocumentedRequest = $requestFactory->createRequest('get', '/api/undocumented');
// To add authentication headers to a request
$authenticatedRequest = $requestFactory->authenticateRequest($undocumentedRequest);

// Use the production server
$requestFactory = new RequestFactory($email, $token, RequestFactory::PRODUCTION_URI);
?>
```

## License

The library is open-sourced software licensed under the MIT license.