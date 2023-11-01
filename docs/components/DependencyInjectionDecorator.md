# Dependency Injection Decorator

Этот компонент предоставляет аттрибут `#[Inject]` (аналог Container::get())

Пример:
```php
class MyController
{
    #[Get('/posts')]
    public function getPosts(
        #[Inject('app.post_service')] PostService $postService,  
    ): ...
}
```
