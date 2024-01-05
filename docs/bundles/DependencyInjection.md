# Dependency Injection Bundle

Модуль DI просто позволяет делать красивый yaml-конфиг для DI.

Пример конфигурации:
```yaml
scan:
    - App\Service
    - App\EventListener
    - 
ignore:
    - App\Service\NotAService
    - 
parameters:
    app.some_parameter: my_value
    
services:
    app.manual_service_name:
        class: App\Service\MyService
        arguments: 
            - someService: '@app.other_service',
            - someValue: '%app.some_parameter%'
              
    App\Service\OtherService:
      singleton: false
      factory:
        service: App\Facroty\FactoryService
        method: createOtherService
        static: false
        
aliases:
    app.other_service: App\Service\OtherService
```
