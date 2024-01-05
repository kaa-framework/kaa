# Event Dispatcher Bundle

Модуль Event Dispatcher предоставляет автоконфигурацию слушателей и диспатчеров.

Пример конфигурации:
```yaml
dispatcher:
    scan:
        - App\EventListener
    
    listeners:
        - service: 'app.some_event_listener'
          service_class: App\EventListenerService
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
// method по умолчанию называется invoke (без подчёркиваний)
// dispatcher по умолчанию равен kernel
#[EventListener(method: 'onSomeEvent', event: 'event.some', dispatcher: 'app')]
class SomeEventListener
{

}
```
