## Installation

- Requires PHP 8.2 or higher.

```sh
composer require amirhossein5/return-error
```

## Usage

Given a function that returns either an error or value:

```php
use Illuminate\Support\Facades\Http;

enum PaymentErrors {
    case INVALID_AMOUNT;
    case INVALID_TOKEN;
    case SERVER_ERROR;
}

function processPayment(int $amount, string $token): array|ReturnError
{
    // Some upfront validation
    if ($amount <= 0) {
        return new ReturnError(
            message: "The payment amount must be greater than zero.",
            type: PaymentErrors::INVALID_AMOUNT,
        );
    }

    // Calling the API and handling errors based on the response
    return ReturnError::wrap(function () use ($amount, $token): array {
        $response = Http::post('https://gateway.com', [
            'amount' => $amount,
            'token' => $token,
        ]);

        if ($response->failed()) {
            $data = $response->json();
            $statusCode = $response->status();

            if ($statusCode === 401 || ($data['error']['code'] ?? '') === 'token_invalid') {
                return new ReturnError(
                    message: $data['error']['message'] ?? "The provided payment token is invalid.",
                    type: PaymentErrors::INVALID_TOKEN,
                );
            }

            if ($statusCode >= 500) {
                return new ReturnError(
                    message: $data['error']['message'] ?? "The payment gateway is currently down.",
                    type: PaymentErrors::SERVER_ERROR,
                );
            }

            // throwing client or server errors as an exception, so that ReturnError::wrap()
            // can catch them and return an instance of ReturnError class containing the exception
            $response->throw();
        }

        return $response->json();
    });
}

// Handling the function call
$response = processPayment(2500, 'invalid_tok');

// Handling errors
if ($response instanceof ReturnError) {
    switch ($response->type) {
        case PaymentErrors::INVALID_AMOUNT:
            // ...
            break;
        case PaymentErrors::INVALID_TOKEN:
            // ...
            break;
        case PaymentErrors::SERVER_ERROR:
            // ...
            break;
        default:
            // Handle other exceptions caught by ReturnError::wrap(), including server or client API errors
            break;
    }
    
    if (!in_array($response->type, [PaymentErrors::INVALID_AMOUNT, PaymentErrors::INVALID_TOKEN])) {
        // Logging the exception
        $response->report(); 
    }
}
```

### Wrapping exceptions

To wrap an exception into a ReturnError class instance use:

```php
$result = ReturnError::wrap(function() {
    throw new Exception();
});
$result instanceof ReturnError; // true

$result = ReturnError::wrap(function(): int {
    return 2;
});
$result === 2; // true
```

### Unwrapping ReturnError

In some cases you might want to throw the exception that is inside a ReturnError (if there is any):

```php
ReturnError::unwrap(
    ReturnError::wrap(fn() => throw new TestException)
); // throws TestException

$num = ReturnError::unwrap(
    ReturnError::wrap(fn() => divide(20, 1))
); // $num is 20
```

### Initializing ReturnError

Initialize with a custom message:

```php
new ReturnError(message: "something went wrong");
```

Or with a type, which can be a *string* or `enum`:

```php
new ReturnError(..., type: 'custom_type');
new ReturnError(..., type: Enum::ENUM);
```

### Reporting ReturnError

To log the error message along with its stack trace in your Laravel log file, call the `report()` method:

```php
(new ReturnError())->report(); // local.ERROR:  {"exception":"[object] (Exception(code: 0):  at ...
(new ReturnError("with message"))->report(); // local.ERROR: message: with message {"exception...
(new ReturnError("with message", "custom_type"))->report(); // local.ERROR: message: with message, type: custom_type {"exception...

enum DivisionErrors: string { case DIVISION_BY_ZERO = 'division_by_zero'; }

(new ReturnError(type: DivisionErrors::DIVISION_BY_ZERO))->report(); // local.ERROR: type: DIVISION_BY_ZERO {"exception...
(new ReturnError(type: BackedDivideErrors::DIVISION_BY_ZERO))->report(); // local.ERROR: type: division_by_zero {"exception...

(new ReturnError())->report(additional: 'string'); // local.ERROR: additional: "string" {"exception...
(new ReturnError())->report(additional: ['given' => '...']); // local.ERROR: additional: {"given":"..."} {"exception...
```

## License

This project is open-source software licensed under the [MIT License](https://opensource.org/licenses/MIT).
