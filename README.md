# Infinitum SDK for PHP

## Installation

The [Infinitum](https://packagist.org/packages/infinitum/php-sdk) SDK can be installed via [Composer](https://getcomposer.org/).

```
composer require infinitum/php-sdk
```

## Usage

---

### API Initialization

To use the Inifnitum you need to provide the `API Key`, `API Secret`, `API Token` and the corresponding `workspace`.

```php
$infinitum = new \Fyi\Infinitum\Infinitum($workspace, $token, $key, $secret);
```

### Setting the Access Token

To use the modules, you first must call the `init()` method or set the access token via `setAccessToken()` if you already have an access token persisted/stored.

Example:

```php
if (Session::get('access_token')) {
  $access_token = Session::get('access_token');
  $infinitum->setAccessToken($access_token);
} else {
  try {
    $response = $infinitum->init();
    $access_token = $response["access_token"];
    Session::put('access_token', $access_token);
  } catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) { } catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) { }
}
```

### Modules

To user modules, you must first fetch the module accessing the `\Fyi\Infinitum\Infinitum` object

```php
use \Fyi\Infinitum\Infinitum;

$infinitum = new Infinitum($workspace, $token, $key, $secret);
$infinitum->init();

$userAPI = $infinitum->user();
```

and then the methods are accessible (methods listed further on).

All API methods snippets show an example `data` object and the expected `response` structure.

There are 2 exceptions to be caught: `\Fyi\Infinitum\Exceptions\InfinitumSDKException` and `\Fyi\Infinitum\Exceptions\InfinitumAPIException` both explained [here]().

#### User

The User API object can be retrieved by calling it from the `\Fyi\Infinitum\Infinitum` object

```php
$userAPI = $infinitum->user();
```

##### Register new User

The required parameters for registering a new user rely on the chosen Infinitum API configuration, like so, all SDK parameters are considered `optional`.

```php
$data = [
  "name"  => "SDK User name",
  "password" => "password123",
  "email" => "sdkuser@infinitum.app",
  "photo" => File,
  "photo64" => "data:image/png;base64,.....",
  "birthdate" => "1/1/1970",
  "language"  => "en-US"
];
$response = $userAPI->register($data);
```

Response:

```
{
  "id": 1
}
```

There are also optional arrays (json string encoded) related to additional user information. Refer the [Infinitum API docs]() for more information.

##### Get User by Face

Fetch a user by providing a picture of the User's face.

```php
$data = [
  "photo"  => File,
  "photo64" => "data:image/png;base64,.....",
];
$response = $userAPI->face($data);
```

Response:

```json
{
  "id": 1,
  "name": "SDK User",
  "email": "sdkuser@infinitum.app",
  "reference_token": "v71eELUThEPA2yzPBkxjPxWwbPgHANxF",
  "state_id": 1,
  "roles": [
    {
      "id": 1,
      "name": "Admin",
      "alias": "admin",
      "permissions": ["all"],
      "backoffice": 1,
      "deleted_at": null,
      "pivot": {
        "user_id": 4,
        "role_id": 1
      }
    }
  ],
  "info": {
    "birthdate": "01/01/2019",
    "language": null,
    "photo": "./storage/public/users/photos/example.png"
  }
}
```

##### Get User by Email address

Fetch a user by providing its email address.

```php
$data = [
  "email"  => "sdkuser@infinitum.app",
];
$response = $userAPI->getByEmail($data);
```

Response:

```json
{
  "id": 1,
  "name": "SDK User",
  "email": "sdkuser@infinitum.app",
  "reference_token": "v71eELUThEPA2yzPBkxjPxWwbPgHANxF",
  "state_id": 1,
  "roles": [
    {
      "id": 1,
      "name": "Admin",
      "alias": "admin",
      "permissions": ["all"],
      "backoffice": 1,
      "deleted_at": null,
      "pivot": {
        "user_id": 4,
        "role_id": 1
      }
    }
  ],
  "info": {
    "birthdate": "01/01/2019",
    "language": null,
    "photo": "./storage/public/users/photos/example.png"
  }
}
```

##### Get all Users

Fetch a user by providing its email address.

```php
$response = $userAPI->getUsers();
```

Response:

```json
[
  {
    "id": 1,
    "name": "SDK User"
    // (...)
  },
  {
    "id": 2,
    "name": "SDK User2"
    // (...)
  }
  // (...)
]
```

##### Delete User

Delete a user by providing its unique ID.

```php
$data = [
  "id" => 1
];

$response = $userAPI->deleteUser($data);
```

Response:

```json
["success"]
```

#### Device

The Device API object can be retrieved by calling it from the `\Fyi\Infinitum\Infinitum` object

```php
$deviceAPI = $infinitum->device();
```

##### Register new Device

Register a new device in the Infinitum API.

```php
$data = [
  "name" => "SDK Device",
  "mac_address"  => "AA:BB:CC:DD:EE:FF",
  "ip" => "192.168.1.2",
  "identity" => "device-unique-identity",
  "app_id" => "1",
  "device_type" => "PC",
  "licensed" => "",
  "app_version" => ""
];

$response = $deviceAPI->registerDevice($data);
```

There are also optional arrays (json string encoded) related to additional user information. Refer the [Infinitum API docs](http://infinitum.fyi.pt/developer) for more information.

```json
{
  "id": 1
}
```

##### Register new Device User

Register a new association between a Device and a User.

```php
$data = [
  "device_mac_address"  => "AA:BB:CC:DD:EE:FF",
  "user_email" => "sdkuser@infinitum.app",
  "device_id" => "1",
  "user_id" => "1"
];

$response = $deviceAPI->registerDeviceUser($data);
```

The data provided can either contain the `device_mac_address` or `device_id` along with the `user_email` or `user_id`, in order to search for a device/user with those properties or directly reference their unique IDs.

Response:

```json
["success"]
```

##### License a Device

Update a device to either be licensed or revoke license to control access to the Infinitum API.

```php
$data = [
  "mac_address"  => "AA:BB:CC:DD:EE:FF",
  "app_id" => "1",
  "licensed" => "1", # can be 0, 1, true or false
];

$response = $deviceAPI->license($data);
```

Response:

```json
["success"]
```

##### Validate a Device

Validate whether or not a device is able/licensed to access the Infinitum API, and retrieve its information.

```php
$data = [
  "mac_address"  => "AA:BB:CC:DD:EE:FF",
];

$response = $deviceAPI->validate($data);
```

Response:

```json
{
  "id": 7,
  "name": "SDK Device",
  "ip": "192.168.1.2",
  "mac_address": "AA:BB:CC:DD:EE:FF",
  "identity": "device-unique-identity",
  "app_version": "1.0.0",
  "licensed": 1,
  "app": {
    // (...)
  },
  "users": [
    // (...)
  ]
}
```

##### Delete Device

Delete a device by providing its unique ID.

```php
$data = [
  "id" => 1
];

$response = $deviceAPI->deleteDevice($data);
```

Response:

```json
["success"]
```

#### Auth

The Auth API object can be retrieved by calling it from the `\Fyi\Infinitum\Infinitum` object

```php
$authAPI = $infinitum->auth();
```

All Auth API requests have the same response:

```json
{
  "id": 1,
  "name": "SDK User",
  "token": "(...)",
  "email": "sdkuser@infinitum.app"
}
```

##### Biometric Auth

Authenticate a user against the Infinitum API using its face properties.
Along with the user photo, additional parameters can be provided to specify the device and method used in the request.

```php
$data = [
  "photo" => $file,
  "photo64" => "data:image/png;base64,....",
  "device" => 1,
  "device_ip" => "192.168.1.2",
  "device_mac_address" => "AA:BB:CC:DD:EE:FF",
  "device_mac_address_value" => "extended-parameter",
  "action" => "entrance",
  "proximity" => "near",
  "data" => [] # additional custom parameters
];

$response = $deviceAPI->biometric($data);
```

##### Login

Authenticate a user against the Infinitum API using regular login parameters: `email` and `password`.

```php
$data = [
  "email" => "sdkuser@fyi.pt",
  "password" => "password123"
];

$response = $deviceAPI->login($data);
```

##### Code

Authenticate a user against the Infinitum API using a unique code from to the user's possible codes.

```php
$data = [
  "used_codes" => [
    ["code" => "abc1", "date" => "01-01-2019 18:00:00"],
    ["code" => "abc12", "date" => "01-01-2019 18:00:00"]
  ],
  "device_mac_address" => "AA:BB:CC:DD:EE:FF"
];

$response = $deviceAPI->code($data);
```

### Exceptions

Both exceptions extend the base PHP `Exception` class so all related [methods](https://www.php.net/manual/en/class.exception.php) are available.

#### InfinitumAPIException

This exception is thrown when an API error occurrs either due to malformed requests, server errors or any other error related to the Infinitum API.
In addtition to the methods provided by the `Exception` class, a `getBody()` method is also available, to fetch the response body sent by the Infinitum API containing the error information (message, type and status code).`

Error body structure:

```php
[
  "message" => "The provided email has already been taken.",
  "type"    => "VALIDATOR_ERROR",
  "status"  => 400
]
```

Usage example:

```php
try {
  $response = $deviceAPI->biometric([
                "photo" => $file,
              ]);
  // (...)
} catch (\Fyi\Infinitum\Exceptions\InfinitumAPIException $exc) {
  return $exc->getBody();
}
```

#### InfinitumSDKException

The SDK exception is thrown in case of any error during the execution of any SDK method, unrelated to the API calls.
This exception is mostly used when parameters are missing before executing a request that requires any of those parameters.
Example:

and can be caught as any exception:

```php
try {
  $response = $userAPI->getByEmail([]);
  // (...)
} catch (\Fyi\Infinitum\Exceptions\InfinitumSDKException $exc) {
  return $exc->getMessage();
}
```

The return value would be `Missing Email parameter.`.

#### MissingTokenException

This exception has `InfinitumSDKException` as a parent, but it is only used when the access token is missing, meaning that an `init()` or `setAccessToken()` call was never done.
It can be caught either by expecting the `MissingTokenException` class itself or its parent `InfinitumSDKException`.

```php
try {
  $response = $userAPI->register($data);
  // (...)
} catch (\Fyi\Infinitum\Exceptions\MissingTokenException $exc || \Fyi\Infinitum\Exceptions\InfinitumSDKException $exc ) {
  return $exc->getMessage();
}
```
