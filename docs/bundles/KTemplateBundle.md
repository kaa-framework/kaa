# KTemplate Bundle

Модуль KTemplate предоставляет шаблонизатор с возможностью получения статических файлов.

Этот модуль зарегистрирует сервис `kernel.ktemplate`

Пример конфигурации:
```yaml
ktemplate:
  path: /app/public/static
  url: "http://localhost:8800"
  template_path: /app/templates
```

В конфигурации все поля обязательные для заполнения. 
Каждое из них имеет следующее значени:
- `path` - путь до папки со статическими файлами.
- `url` - адрес вашего сервера.
- `template_path` - путь до папки с шаблонами.

Сервис Engine можно получить с помощью инъкции зависимостей.

Пример контроллера и шаблона:
```php
class ExampleController
{
    #[Get('/test')]
    public function test(
        #[Inject]
        Engine $ktemplate,
        #[MapQueryParameter]
        string $name
    ): Response {
        return new Response($ktemplate->render('greeting', new ArrayDataProvider(['name' => $name])));
    }
}
```

Или можно указать сервис Engine в конструкторе контроллера:
```php
class ExampleController
{
    private Engine $ktemplate;

    public function __construct(
        Engine $ktemplate
    ) {
        $this->ktemplate = $ktemplate;
    }

    #[Get('/test')]
    public function test(
        #[MapQueryParameter]
        string $name
    ): Response {
        return new Response($this->ktemplate->render('greeting', new ArrayDataProvider(['name' => $name])));
    }
}
```

```twig
<! -- template/greeting -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Example controller</title>
</head>
<header>
    Test
</header>
<body>

    <link  rel="stylesheet" type="text/css" href="{{ asset("css/style.css", "css") }}" />
    <p>Hello, {{ name }}</p>

</body>
<footer>
    Test
</footer>
</html>
```

Для подключения статических файлов необходимо использовать метод `asset(string $filePath, string $fileType)`. 
Метод принимает два параметра путь до необходимо файла и тип файла. 

Соответственно, файл из примера, для вызова метода `asset("css/style.css", "css")`, должен
лежать по пути `/app/public/static/css/style.css`.

*Важно!* Не стоит в путь файла добавлять `---` и `___`. Это может привести к некорректной
обработки пути к файлу.

