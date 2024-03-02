# Security Decorator

Этот компонент предоставляет декораторы `#[IsGranted]` и `#[CurrentUser]`

`#[IsGranted]` - позволяет проверить, есть ли у текущего пользователя права на определённое действие, перед запуском контроллера.

`#[CurrentUser]` - позволяет подставить объект пользователя (аналог прямого вызова $security->getUser()).

`#[CheckAccess]` - работает аналогично `#[IsGranted]` но просто вызывает метод переданного класса. Имеет возможность передать ему параметры контроллера. Имеет очень низкий приоритет

```php
readonly class CheckAccess
{
    public function __construct(
        public string $accessCheckerClass,
        /** @var string */
        public array $arguments = [],
        public string $method = 'invoke',
        public string $serviceName,
    ) {
    }
}
```


`arguments` - аргументы в том порядке, в котором их ожидает метод.
Если значение аргумента начинается с `$`, то будет передана переменная из `Variables` с таким же именем, иначе просто само значение.

# Примеры:

```php
class MyController
{
    #[Put('/post')]
    #[IsGranted('EDIT_POST')]
    public function editPost(): void
    {
        // ...
    }
    
    #[Post('/post')]
    #[CheckAccess(PostAccessChecker:class, ['$user', '$postModel', 'someVal'])]
    public function createPost(
        #[CurrentUser] User $user,
        #[MapJsonPayload] PostModel $postModel,
    ): void {
        // ...
    }
}
```
