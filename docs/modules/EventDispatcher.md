# Event Dispatcher Module

Модуль Event Dispatcher предоставляет автоконфигурацию слушателей и диспатчеров.

Пример конфигурации:
```yaml
dispatcher:
    scan:
        - App\EventListener
    
    listeners:
        - service: 'app.some_event_listener'
          method: onSomeEvent
          event: 'event.some'
          dispatcher: kernel

        - service: App\EventListener\OtherEventListener
          method: onEvent
          event: 'event.other'
          dispatcher: other_dispatcher
```

После этого слушатели будут подписаны на события.
И созданы сервисы `kernel.dispatcher.<имя_диспатчера>`.
```php
class Service {
   public function __construct(
        EventDipatcher $kernelDispatcher,
        
        #[Autowire('kernel.dispatcher.other_dispatcher')]
        EventDipatcher $otherDispatcher,
   ): ...
}
```

Также поддерживает конфигурация через атрибуты:
```php
// по умолчанию сервис совпадает с именем класса
// method по умолчанию называется invoke (без подчёркиваний)
// dispatcher по умолчанию равен kernel
#[EventListener(service: 'app.some_event_listener', method: 'onSomeEvent', event: 'event.some')]
class SomeEventListener
{

}
```
