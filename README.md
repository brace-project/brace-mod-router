# brace-mod-router

Core Router

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
