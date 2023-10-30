# HttpFoundation - предоставляет классы для удобной работы с запросами и ответами HTTP

### Введение

Компонент HttpFoundation предоставляет удобный способ работы с базовыми элементами HTTP (ничего удивительного!). Но что
мы понимаем под "основами" HTTP?
В основном HTTP работает по простой схеме: клиент посылает запрос на сервер, а сервер посылает ответ. Компонент
HttpFoundation предоставляет вам классы и инструменты для более организованной и эффективной работы с этими запросами и
ответами. Вместо того чтобы работать со сложными суперглобалами PHP, такими как $_GET и $_COOKIE, мы можем использовать
такие классы, как Response и Request, для достижения тех же результатов.
Но более того, компонент добавляет методы для удобного и безопасного чтения содержимого request, составления
собственного response, решает проблемы кодировки в KPHP, работает с JSON, даёт анонимизацию IP-адресов клиентов и не
только.

### Request - запрос к серверу от клиента

#### Cоздание объекта класса Request

Чтобы создать новый запрос в программе, проще всего сделать

```php
use Kaa\HttpFoundation\Request;

$request = new Request();
```

Так мы создадим пустой объект класса Request. Значения такого объекта можно проинициализировать методом `initialize()`.
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
общедоступных свойств:

- request: эквивалент $_POST;
- query: эквивалент $_GET ($request->query->get('name'));
- cookies: эквивалент $_COOKIE;
- attributes: нет эквивалента - используется вашим приложением для хранения других данных, удобнее всего в виде массива
  строк = string[]
- server: эквивалент $_SERVER;
- headers: в основном эквивалентно подмножеству $_SERVER ($request->headers->get('User-Agent')).

Каждое свойство является экземпляром ParameterBag

- request: ParameterBag;
- query: InputBag наследник ParameterBag
- cookies: InputBag наследник ParameterBag
- attributes: ParameterBag;
- server: ServerBag наследник ParameterBag
- headers: HeaderBag исключение, из-за разницы сигнатур, но предоставляет те же методы Parameter bag и больше

Все экземпляры ParameterBag имеют методы для извлечения и обновления своих данных:

```php
@param string[] $parameters
all() // Возвращает параметры.
@return string[]
keys() // Возвращает параметр keys.
@param string[] $parameters
replace() // заменяет текущие параметры новым набором.
@param string[] $parameters
add() // добавляет параметры.
@param string $key, ?string $default = null
@return ?string
get() // возвращает параметр по имени.
@param string $key, boolean|string $value<
set() // задает строковое значение для параметра по его имени.
@return bool
has() // Возвращает true, если параметр определен.
@param string $key
remove() // удаляет параметр.
```

Экземпляр ParameterBag также имеет некоторые методы для фильтрации входных значений:

- <i>Всегда запрашивается значение по строковому ключу значение @param string $key</i>
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

// если мы уже знаем информацию, которую будем отдавать в json
$response = new JsonResponse(['data' => 123]);

// если мы ещё не знаем информацию, которую отдаём пользователя в виде json
$response = new JsonResponse();

// Далее можно менять параметры запроса (содержание ответа, статус ответа, HTTP заголовки)
// Важно: если необходимо задать опции кодирования, то их нужно назначать до вызоыва "setData()"
$response->setEncodingOptions(JsonResponse::DEFAULT_ENCODING_OPTIONS | \JSON_PRESERVE_ZERO_FRACTION);
$response->setData(['data' => 123]);

// Если содержание response уже является строкой json, ставим значение $json = true
$response = new JsonResponse('{ "data": 123 }', 200, [], true);

// либо проще всего использовать метод fromJsonString()
$response = JsonResponse::fromJsonString('{ "data": 123 }');

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

#### JSON Hijacking

Чтобы избежать XSSI JSON Hijacking, следует передавать в JsonResponse ассоциативный массив в качестве крайнего массива,
а не индексированный массив, чтобы конечным результатом был объект (например, {"object": "not inside an array"}), а не
массив (например, [{"object": "inside an array"}]). Более подробная информация приведена в руководстве OWASP.

Чтобы передать объект в json-ответ. Необходимо вызвать метод JsonResponse::fromObject(). Он использует JsonEncoder из
vkcom/kphp-polyfills

Пример объекта, который подходит для JsonEncoder

```php
class UserObjectJsonResponse
{
    public string $name;

    public int $age;

    public function __construct(string $name, int $age)
    {
        $this->name = $name;
        $this->age = $age;
    }
}
```

Теперь создаём JsonResponse из объекта:

```php
$obj = new UserObjectJsonResponse("Vasiliy", 42);
$response = JsonResponse::fromObject($obj);
$response->getContent(); // {"name":"Vasiliy","age":42} - мы спаслись от JSON Hijacking
```

Используем по назначению. Только методы, отвечающие на GET-запросы, уязвимы к XSSI 'JSON Hijacking'. Методы, отвечающие
только на POST-запросы, остаются незатронутыми.

#### JsonCallback

```php 
response = (new JsonResponse(['foo' => 'bar']))->setCallback('callback');
// HTTP заголовок 'Content-Type' устанавливается как 'text/javascript' 
$response->headers->get('Content-Type'); // 'text/javascript'
// к содержимому дописывается callback
$response->getContent(); // '/**/callback({"foo":"bar"});'
// теперь оно выглядит как
callback({"foo":"bar"});
```

### Заключение

Дополнительно рекомендую смотреть полноценную доку:
https://kphp-hse.gitbook.io/httpfoundation/use-cases/response
И исходники:
https://github.com/m-fedosov/http-foundation-kphp

Если нужно объяснить конкретные моменты - пишите @mfedos tg/vk
