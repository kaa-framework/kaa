[![pipeline status](https://git.miem.hse.ru/kaa-framework/kaa/badges/main/pipeline.svg)](https://git.miem.hse.ru/kaa-framework/kaa/-/commits/main)

# Kaa &mdash; web-фреймворк для KPHP

### [Быстрый старт](docs/fast-track.md)

### Установка
`composer install kaa/kaa`

### Структура фреймворка
Фреймворк состоит из следующих компонентов:

+ [Generator Contract](docs/components/GeneratorContract.md) - общий интерфейс всех генераторов

+ [Http Message](docs/components/HttpMessage.md) - предоставляет классы для удобной работы с запросами и ответами HTTP

+ [Event Dispatcher](docs/components/EventDispatcher.md) - отправка и подписка на сообщения

+ [Http Kernel](docs/components/HttpKernel.md) - обработка HTTP-запроса от самого начала до ответа

+ [Validator](docs/components/Validator.md) - валидация полей объектов

+ [Router](docs/components/Router.md) - принимает объект Request и возвращает callback, который нужно вызвать для его обработки

+ [Dependency Injection](docs/components/DependencyInjection.md) - генерирует код для создания объектов

+ [Security](docs/components/Security.md) - принимает объект запроса и решает имеет ли пользователь, отправивший его, доступ к этому запросу...

+ [Security Decorator](docs/components/SecurityDecorator.md)

+ [Request Mapper Decorator](docs/components/RequestMapperDecorator.md)

+ [Dependency Injection Decorator](docs/components/DependedncyInjectionDecorator.md)

И следующих модулей:

+ [Framework Generator](docs/modules/FrameworkGenerator.md) - общий интерфейс всех модулей, генерирующих код
+ [Kernel Module](docs/modules/KernelModule.md)
+ [Event Dispatcher](docs/modules/EventDispatcher.md)
+ [Validator](docs/modules/Validator.md)
+ [Router](docs/modules/Router.md)
+ [Dependency Injection](docs/modules/DependencyInjection.md)
+ [Security](docs/modules/Security.md)

### [Диаграмма обработки запроса](https://miro.com/welcomeonboard/dkV1ZXNGekY3R2dTM1pzRmN1SWpQMTllUGdBbWhMaEJyR0JxR0E4RE5zem9iTlJ5YTRQWjRNbktRTk9laU95TnwzNDU4NzY0NTM2NTEwODMzNzI1fDI=?share_link_id=856045042759)
![Диаграмма обработки запроса](docs/request_handling.jpg)
