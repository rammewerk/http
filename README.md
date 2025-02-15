Rammewerk Http
======================

This package aims to provide a super easy starting point for building web applications, seamlessly integrating support
for both traditional web requests and HTMX interactions.

It simplifies development with a clean, intuitive API, and
offers type-safe input handling for requests and queries. A key feature is its powerful entity decoder, which leverages
the fast and efficient Rammewerk Hydrator to effortlessly convert incoming requests into object entities. This allows
for rapid development of data-driven applications with minimal boilerplate.

The package extends the Symfony HTTP
Foundation components (`Request` and `Response`) with these added conveniences.

## Installation

To install the Rammewerk Request component, you can use Composer:

```bash
composer require rammewerk/http
```

## Usage

## Request

The `Rammewerk\Http\Request` class extends the `Symfony Symfony\Component\HttpFoundation\Request` class. It provides
several
helper methods for working with requests, including **type-safe** input retrieval, **CSRF** token handling, and **entity
hydration**.

### Initialization

The `RequestFactory` class provides a convenient way to create a new request instance.

```php
use Rammewerk\Http\RequestFactory;
$request = RequestFactory::create();
```

To manually create a request instance without a session, use `RequestFactory::createWithoutSession()`. For more control,
see the `Request` class and symfony documentation.

### Domain and URI Checks

```php
$path = $request->path(); // e.g., 'profile/settings'
$domain = $request->domainName(); // e.g., 'example.com'
$subdomain = $request->subdomain(); // e.g., 'api' from 'api.example.com'
$isSubdomain = $request->isSubdomain('api'); // true if subdomain is 'api'
```

### Input Retrieval

The `input()` method retrieves input from the request body, query string, or uploaded files.

```php
$name = $request->input('name'); // Get input from query or request body
$allInputs = $request->all(); // Get all inputs
$file = $request->file('avatar'); // Get uploaded file (returns Symfony\Component\HttpFoundation\File\UploadedFile)
```

### Type-Safe Input Retrieval

```php
$name = $request->inputString('name'); // Get input as a string
$age = $request->inputInt('age'); // Get input as an integer
$price = $request->inputFloat('price'); // Get input as a float
$isActive = $request->inputBool('is_active'); // Get input as a boolean
$tags = $request->inputArray('tags'); // Get input as an array
$date = $request->inputDateTime('date', 'Y-m-d'); // Get input as a DateTimeImmutable object
$email = $request->inputEmail('email'); // Get input as a validated email string
```

### CSRF Token Handling

```php
$token = $request->generateCsrfToken(); // Generate a CSRF token
$request->validateCsrfToken(); // Validate the CSRF token from the request
```

### Entity Hydration

```php
use Rammewerk\Http\RequestFactory;
use App\Entity\User;

$request = RequestFactory::create();

// Simple hydration where each input key is mapped to an entity property
$user = $request->decode(User::class);

// More advanced hydration with custom mapping and required fields
$user = $request->decode(User::class, function (DecodeConfig $config) {
    $config->assign('first_name', 'firstName'); // Map request input 'first_name' to entity property 'firstName'
    $config->require('email'); // Mark 'email' as a required field
    $config->exclude('password'); // Exclude 'password' from being set
});

// $user is now an instance of App\Entity\User populated with data from the request
```

The `decode()` method hydrates an entity with data from the request. It supports mapping input keys to entity
properties,
specifying required fields, and excluding fields from being set. It uses the `Rammewerk\Component\Hydrator\Hydrator`
component for the actual hydration process.

### Flash Messages

```php
$request->flash('success', 'User created successfully!');
$messages = $request->getFlashMessages(); // Get all flash messages
```

### HTMX Helpers

```php
$isHtmxRequest = $request->htmxRequest();
$currentUrl = $request->htmxCurrentUrl();
$historyRestoreRequest = $request->htmxHistoryRestoreRequest();
$promptResponse = $request->htmxPromptResponse();
$request = $request->htmxRequest();
$targetId = $request->htmxTargetId();
$triggerName = $request->htmxTriggerName();
$triggerId = $request->htmxTriggerId();
```

## Response

The `Rammewerk\Http\Response` class extends the `Symfony Symfony\Component\HttpFoundation\Response` class. It provides
convenient methods for creating responses, especially for HTMX interactions.

### Basic Usage

```php
use Rammewerk\Http\Response;
$response = new Response('Hello, world!');
$response->send();
```

### Redirects

```php
$response = new Response();
$redirectResponse = $response->redirect('/dashboard');
$redirectResponse->send();
```

### HTMX Helpers

```php
$response->htmxRedirect('/new-page');
$response->htmxRefresh();
$response->htmxLocation('/profile', ['userId' => 123]);
$response->htmxPushUrl('/new-url');
$response->htmxReplaceUrl('/old-url');
$response->htmxReswap('innerHTML');
$response->htmxRetarget('#target-element');
$response->htmxTriggers(['event1', 'event2']);
$response->htmxAfterSettleTriggers('afterSettleEvent');
$response->htmxAfterSwapTriggers(['afterSwap1', 'afterSwap2']);
```

### Page Not Found

A super simple static method for creating a 404 response.

```php
use Rammewerk\Http\Response;
Response::pageNotFound('Page not found');
```

## Contributing

If you would like to contribute to the Rammewerk Request component, please feel free to submit a pull request. All
contributions are welcome!

## License

Rammewerk Request is open-sourced software licensed under the MIT license.