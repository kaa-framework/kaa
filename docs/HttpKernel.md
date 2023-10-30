### HttpKernel

This is HttpKernel library inspired by Symfony framework and compatible with KPHP

### Usage:

```php
require_once '../vendor/autoload.php';

$kernel = yourWayToCreateKernel();

$request = \Kaa\HttpKernel\Request::initFromGlobals();
$response = $kernel->handle($request);
$request->send();
```

The `HttpKernel::handle` is the only public method in kernel it receives request and transforms it to a response
by dispatching several events and maybe calling some user`s action

The first event dispatched is ```HttpKernelEvents::REQUEST```

It's listeners call instantiate some user classes if needed or can perform some security checks

If response is set to event it will be returned from handle method, no further code will be executed

The second event dispatched is ```HttpKernelEvents::FIND_ACTION```

It's listeners must set action with will handle the Response, action has the following
signature `callable(Request $request): ResponseInterface`
If no listeners set an action, `ResponseNotReachedException` will be thrown

The third event dispatched is ```HttpKernelEvents::Response```

It's listeners can modify and change response object

If there is unhandled exception during execution chain the kernel will catch it and dispatch
```HttpKernelEvents::THROWABLE```
Throwable event listener must provide response to be returned otherwise `ResponseNotReachedException` will be thrown

HttpKernel`s constructor receives only parameter - instance of EventDispatcher which must have listeners to
HttpKernelEvent
