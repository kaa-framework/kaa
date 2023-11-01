# Request Mapper Decorator

Это компонент предоставляет декораторы:

* `#[MapRequestPayload]` - маппит данные запроса из `$_POST` в модель
* `#[MapJsonPayload]` - декодитрует JSON-тело запроса в переденную модель с помощью `JsonDecoder::decode`
* `#[MapQueryParameter]` - подставляет параметр из query в переменную
* `#[MapQueryParametesrs]` - мапит все переменные из query в модель
* `#[MapRouteParameter]` - подставляет параметр пути в модель

Примеры:
```php
class MyController
{
    #[Post('/post')]
    public function createPost(
        #[MapRequestPayload] PostModel $model,
    ): void {
        // ...
    }
    
    #[Get('/posts')]
    public function getPosts(
        #[MapQueryParametesrs] QueryParamsModel $model,
    ): ...
    
    #[Get('/{userId}/posts')]
    public function getUserPosts(
        #[MapRouteParameter] int $userId,
    )
}
```

У `#[MapQueryParametesrs]`, `#[MapJsonPayload]` и `#[MapRequestPayload]` есть поле `validate=true`.
Если оно установлено в `true`, то модель будет провалидирована и в случае ошибки валидации выброшено исключение.
