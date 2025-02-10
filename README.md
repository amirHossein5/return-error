## Installation

- requires php8.2

```sh
composer require amirhossein5/return-error
```

## Usage

Given you have a function that might return an error or value:

```php
enum DivisionErrors {
    case DIVISION_BY_ZERO;
}

function divide(int $num, int $divideBy): int|ReturnError
{
    if ($divideBy === 0) {
        return new ReturnError(
            message: "can't divide by zero",
            type: DivisionErrors::DIVISION_BY_ZERO,
        );
    }

    return $num / $divideBy;
}

$divisionResult = divide(20, 0);
if ($divisionResult instanceof ReturnError) {
    if ($divisionResult->type === DivisionErrors::DIVISION_BY_ZERO) {
        // ...
    }

    // ...
}
```

## Constructing ReturnError

```php
new ReturnError();
```

With message:

```php
new ReturnError(message: "something went wrong");
```

Or with a type which can be a *string* or *enum*:

```php
new ReturnError(..., type: 'its_type');
new ReturnError(..., type: Enum::ENUM);
```

## Reporting ReturnError

To log the error message in with stacktrace in laravel logs call `report()` method:

```php
(newReturnError())->report(); // local.ERROR:  {"exception":"[object] (Exception(code: 0):  at ...
(newReturnError("with message"))->report(); // local.ERROR: message: with message {"exception...
(newReturnError("with message", "its_type"))->report(); // local.ERROR: message: with message, type: its_type {"exception...

enum DivisionErrors: string { case DIVISION_BY_ZERO = 'division_by_zero'; }

(newReturnError(type: DivisionErrors::DIVISION_BY_ZERO))->report(); // local.ERROR: type: DIVISION_BY_ZERO {"exception...
(newReturnError(type: BackedDivideErrors::DIVISION_BY_ZERO))->report(); // local.ERROR: type: division_by_zero {"exception...

(newReturnError())->report(additional: 'string'); // local.ERROR: additional: "string" {"exception...
(newReturnError())->report(additional: ['given' => '...']); // local.ERROR: additional: {"given":"..."} {"exception...
```

## Wrapping exceptions

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

## Unwrapping ReturnError

In some cases you might want to throw the ReturnError if a function returns it:

```php
$num = ReturnError::unwrap(divide(20, 0)); // throws exception
$num = ReturnError::unwrap(divide(20, 1)); // num is 20
```
