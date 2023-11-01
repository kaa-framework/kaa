# HttpMessage

### Содержание

* [Введение](#введение)
* [Request](#request)
* [Response](#response)
* [JsonResponse](#jsonresponse-создание-и-работа-с-содержимым)
* [RedirectResponse](#redirectresponse)

### Введение

`HttpMessage` предоставляет классы для удобной работы с HTTP запросами и ответами.

### Request

#### Получение запроса Request

```php
use Kaa\HttpFoundation\Request;

$request = Request::initFromGlobals();
```

Также мы можем создать пустой объект класса Request. Значения такого объекта можно проинициализировать методом `initialize()`.
Он позволяет задать уже различные параметры объекта Request самостоятельно, такие как параметры запроса (query),
параметры формы (request), атрибуты (attributes) и заголовки (headers).

```php
$request = new Request();

$request->initialize(['foo' => 'bar']);
//'bar' === $request->query->get('foo')

$request->initialize([], ['foo' => 'bar']);
//'bar' === $request->request->get('foo')

$request->initialize([], [], ['foo' => 'bar']);
//'bar' === $request->attributes->get('foo')
```

Можно сымитировать запрос от сервера

```php
$request = Request::create(
    '/hello-world',
    'GET',
    ['name' => 'Mike']
);
```

После обязательно следует переопределить глобальные переменные, для этого делаем

```php
$request->overrideGlobals();
```

Создать объект класса Request можно из текущего состояния суперглобальных переменных, которые нам выдаёт сервер, для
этого используется метод `Request::createFromGlobals()`

#### Доступ к данным запроса и ParameterBag

Объект запроса содержит информацию о запросе клиента. К этой информации можно получить доступ через несколько
публичных свойств:

- `request`: эквивалент `$_POST`;
- `query`: эквивалент `$_GET` ($request->query->get('name'));
- `cookies`: эквивалент `$_COOKIE`;
- `attributes`: нет эквивалента. Используется для хранения кастомных атрибутов
- `server`: эквивалент `$_SERVER`;
- `headers`: в основном эквивалентно подмножеству $_SERVER ($request->headers->get('User-Agent')).

Каждое свойство является экземпляром ParameterBag

- request: ParameterBag;
- query: InputBag наследник ParameterBag
- cookies: InputBag наследник ParameterBag
- attributes: ParameterBag;
- server: ServerBag наследник ParameterBag
- headers: HeaderBag исключение, из-за разницы сигнатур, но предоставляет те же методы Parameter bag и больше

Все экземпляры ParameterBag имеют методы для извлечения и обновления своих данных:

```php
all(string[] $parameters): string[]; // Возвращает параметры.

keys(): string[] // Возвращает параметр keys.

add(string[] $parameters): self; // добавляет параметры.

get(string $key, ?string $default = null): ?string; // возвращает параметр по имени.

set(string $key, string $value): self; // задает строковое значение для параметра по его имени.

has(): bool; // Возвращает true, если параметр определен.

remove(string $key): self; // удаляет параметр.
```

Экземпляр ParameterBag также имеет некоторые методы для фильтрации входных значений:

- Всегда запрашивается значение по строковому ключу значение string $key
- getAlpha() - Возвращает буквенные символы значения параметра как <b>string</b>
- getAlnum() - Возвращает буквенные символы и цифры значения параметра как <b>string</b>
- getDigits() - Возвращает цифры значения параметра как <b>string</b>
- getBoolean() - Возвращает значение параметра, преобразованное в boolean @return boolean
- getInt() - Возвращает значение параметра, преобразованное в целое число @return int

Значение всегда получаем по параметру, можно установить возвращаемое значение в случае, если параметр отсутвует

```php
// the query string is '?foo=bar'

$request->query->get('foo');
// returns 'bar'

$request->query->get('bar');
// returns null

$request->query->get('bar', 'baz');
// returns 'baz'
```

Если в строке запроса параметр передан через массив
`?foo[bar]=baz`

То его не получится получить методом `get()`

```php
$request->query->get('foo[bar]');
//returns null
```

Вместо этого нужно использовать метод `all()`

```php
$request->query->all('foo');
// returns ['bar' => 'baz']

$request->query->all()['foo']['bar'];
// returns 'baz'
```

Доступ к данным которые посылаются через POST-запрос можно получить методом `$request->getContent()`

Если через POST мы передали JSON строку, то значения можно получить матодом `$request->toArray()`

#### Идентификация запроса

Чтобы получить информацию о пути по которому сделан запрос используем метод `getPathInfo()`

```php
// for a request to http://example.com/blog/index.php/post/hello-world
$request->getPathInfo();
// the path info is "/post/hello-world"
```

### Response

#### Создание Response

Создаём в программе объект класса response

```php
use Kaa\HttpFoundation\Response;

$response = new Response();
```

Любой ответ сервера всегда содержит в себе 3 параметра:
content (содержимое): Это параметр, который содержит основное информационное содержимое ответа сервера. Он может быть
представлен в виде текста, HTML-кода, JSON-данных или других форматов в зависимости от того, какой тип данных сервер
возвращает в ответ на запрос.

status (статус): Этот параметр обычно содержит числовой код, который указывает на статус выполнения запроса и ответа
сервера. Например, код состояния HTTP, такой как 200 (OK), 404 (Not Found), 500 (Internal Server Error) и другие,
является частью статуса. Он предоставляет информацию о том, успешно ли выполнен запрос или возникли проблемы.

headers (заголовки): Этот параметр содержит метаданные о ответе сервера. Заголовки могут включать информацию о типе
контента, кодировке, дате, сервере и другие дополнительные сведения о запросе и ответе. Заголовки могут быть
представлены в виде ключ-значение, где ключи представляют различные атрибуты, а значения предоставляют соответствующие
значения или параметры.

Эти параметры мы можем задавать при создании response:

```php
$response = new Response(
    'Content',
    Response::HTTP_OK,
    ['content-type' => 'text/html']
);
```

Или задавать в любой момент до отправки Response:

```php
$response->setContent('Hello World');

$response->headers->set('Content-Type', 'text/plain');

$response->setStatusCode(Response::HTTP_NOT_FOUND);
```

<b>В HttpFoundation все ответы кодируются UTF-8 из-за ограничений KPHP.</b>

#### Отправка Response

Перед отправкой ответа можно дополнительно вызвать метод `prepare()` для устранения несоответствий спецификации HTTP (
например, неправильного заголовка Content-Type):

```php
$response->prepare($request);
```

Отправка ответа клиенту осуществляется вызовом метода:

```php
$response->send();
```

После этого в консоли запущенной программы напечается отправленный Response

С помощью класса Response можно создать любой тип Response, задав нужное содержимое и заголовки. Мы можем сами
установить тип ответа JsonResponse:

```php
use Kaa\HttpFoundation\Response;

$response = new Response();
$response->setContent(json_encode([
    'data' => 123,
]));
$response->headers->set('Content-Type', 'application/json');
```

Но гораздо удобнее использовать класс-наследник Response - JsonResponse

### JsonResponse создание и работа с содержимым

Json ответы можно создать классом JsonResponse

```php
use Symfony\Component\HttpFoundation\JsonResponse;

$response = JsonResponse::fromJsonString('{ "data": 123 }');

// вызовет JsonEncoder::encode
$response = JsonResponse::fromObject($myModel);

// получаем содержимое объекта JsonResponse
$response->getContent() // '{ "data": 123 }'
```

#### JsonResponse HTTP заголовки

Запрашивая у JsonResponse поле 'Content-Type' мы получаем 'application/json'

```php
$response = new JsonResponse();
$response->headers->get('Content-Type')); // 'application/json'
```

Поле 'Content-Type' можно перезаписать

```php
$headers = ['Content-Type' => 'application/vnd.acme.blog-v1+json'];
$response = new JsonResponse([], 200, $headers);
$response->headers->get('Content-Type'); // 'application/vnd.acme.blog-v1+json'
```

Либо дополнительно установить свой заголовок

```php
// при создании JsonResponse\
$response = new JsonResponse([], 200, ['ETag' => 'foo']);
$response->headers->get('Content-Type')); // 'application/json'
$response->headers->get('ETag'); // 'foo'
``` 


### RedirectResponse
`RedirectResponse` ставит правильные заголовки для того, чтобы сделать редирект.

```php
$response = new RedirectReponse('/path/to/redirect');
```
