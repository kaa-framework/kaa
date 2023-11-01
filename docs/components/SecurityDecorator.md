# Security Decorator

Этот компонент предоставляет декораторы `#[IsGranted]` и `#[CurrentUser]`

`#[IsGranted]` позволяет проверить, есть ли у текущего пользователя права на определённое действие, перед запуском контроллера.

`#[CurrentUser]` позволяет подставить объект пользователя (аналог прямого вызова $security->getUser()).

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
    public function createPost(
        #[CurrentUser] User $user,
    ): void {
        // ...
    }
}
```
