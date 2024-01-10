# Router Bundle

Модуль роутера просто позволяет делать красивый yaml-конфиг для роутера.

Этот модуль добавит после валидации слушателя на `kernel.find_action`

Пример конфигурации:
```yaml
router:
    scan:
        - App\Controller
    
    prefixes:
        Kaa\SampleProject\Controller\BlogApiController: /api/
    
    routes:
        - { route: /external-api, method: callExternalApi, service: AppControllerExternalController }
```
