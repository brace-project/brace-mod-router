# brace-mod-router

Core Router. Requires PHP 8.5 or newer.

## Unit tests

Run `composer install` followed by `composer test` from the package root.
The PHPSpec suite covers query attribute resolution and query validation.
GitHub Actions runs the suite on PHP 8.5 for pushes and pull requests.

## Explicit query parameters

`Brace\Router\Attributes\BraceQuery` is repeatable on methods and parameters.
On a parameter, `name: null` uses its PHP name; an explicit name is a query-key
alias. On a method, `name` is required and binds a same-named PHP parameter when
present. Otherwise it only declares a query key. Raw values are available through
`Brace\Core\Helper\QueryParams $queryParams` and the request query API.

Types are inferred from the bound PHP parameter, or default to `string`.
An explicit `type` supports `string`, `int`, `float`, `bool`, and `array`; it must
agree with a bound native type. `description` supplies documentation metadata.
Bound parameters are optional only when they have a PHP default (nullable alone
is not optional). Unbound method declarations may supply `default` explicitly.
Missing values use that default, including null; supplied values must match the
declared type. Boolean query values accept only true/false and 1/0.

`QueryParameterResolver` supplies the shared runtime/codegen metadata.
`QueryParamsValidator` rejects missing required values, invalid scalar/array
shapes and unknown keys. Integer overflow and non-finite floats are rejected.
Dispatch returns HTTP 400 for invalid query input before invoking the controller.
Values are passed to DI for this call only, never globally registered.

A parameter named `$query` permits additional keys. With `array $query`, it receives
the validated values plus unchanged additional values; DTO query hydration is also
supported. Without `$query`, only explicitly declared keys are permitted.
`$queryParams` alone does not permit unknown keys. Raw query access does not
include converted values or defaults.

Run the focused checks with `php tests/QueryParameterResolverTest.php` and
`php tests/QueryParamsValidatorTest.php` from this package's source checkout
inside the application's Composer vendor tree.

## Application prefix

The router uses `BraceApp::$appBasePath` by default and applies it to routes registered through `on()`, `delegate()`, `registerClass()` and `autoload()`:

```php
$app = new BraceApp(appBasePath: '/demo');
$app->addModule(new RouterModule());

$app->router->on('GET@/api/status', $handler);
// Public route: /demo/api/status
```

Pass `prefix` to `RouterModule` only when the router must override the application prefix:

```php
$app->addModule(new RouterModule(prefix: '/router-only'));
```
