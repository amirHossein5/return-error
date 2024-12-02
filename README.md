Return error class with utility functions.

## Installation

- requires php8.2

```sh
composer require amirhossein5/return-erro
```

## Usage

Given you have a function that might return an error or value:

```php
enum DivideErrors {
    case DIVISION_BY_ZERO;
}

function divide(int $num, int $divideBy): int|ReturnError
{
    if ($divideBy === 0) {
        return newReturnError(
            message: "can't divide by zero",
            type: DivideErrors::DIVISION_BY_ZERO,
        );
    }

    return $num / $divideBy;
}

$divideResult = divide(20, 0);
if (isReturnError($divideResult, DivideErrors::DIVISION_BY_ZERO)) {
    // ...
}
if ($divideResult instanceof ReturnError) {
    // ...
}

// ...
```

## Constructing ReturnError

To create a ReturnError call:

```php
newReturnError();
```

With message:

```php
newReturnError(message: "something went wrong");
```

Or with a type which can be a *string* or *enum*:

```php
newReturnError(..., type: 'its_type');
```

## Checking ReturnError

To check wether a function returned ReturnError:

```php
if ($result instanceof ReturnError) {
}
```

To check for ReturnError type which can be a string, or enum:

```php
if (isReturnError($result, 'its_type')) {
}
```

## Reporting ReturnError

To log the error message in exception form with stacktrace in laravel logs use:

```php
reportRE(newReturnError()); // local.ERROR:  {"exception":"[object] (Exception(code: 0):  at ...
reportRE(newReturnError("with message")); // local.ERROR: message: with message {"exception...
reportRE(newReturnError("with message", "its_type")); // local.ERROR: message: with message, type: its_type {"exception...

enum BackedDivideErrors: string { case DIVISION_BY_ZERO = 'division_by_zero'; }

reportRE(newReturnError(type: DivideErrors::DIVISION_BY_ZERO)); // local.ERROR: type: DIVISION_BY_ZERO {"exception...
reportRE(newReturnError(type: BackedDivideErrors::DIVISION_BY_ZERO)); // local.ERROR: type: division_by_zero {"exception...
```

TODO: additional

## Wrapping exceptions

To wrap exception into a ReturnError class instance use:

```php
$result = wrapRE(function() {
    throw new Exception();
});
```

it will return the callback result, or if the exception occures, it'll return ReturnError that has the exception message.

## Unwrapping ReturnError

In some cases you might want to throw the ReturnError if a function returns it:

```php
$num = unwrapRE(divide(20, 0)); // throws exception
$num = unwrapRE(divide(20, 1)); // returns result
```
