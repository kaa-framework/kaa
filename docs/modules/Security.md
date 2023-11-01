# Security Module

Модуль security просто позволяет делать красивый yaml-конфиг для Security.

Это модуль зарегистрирует сервис `kernel.security` с алиасом `Kaa\Security\SecurityInterface`

Пример конфигурации:
```yaml
scan:
   - App\Voter

session:
    cookie_name: x-session
    lifetime: '13 days'
    user_provider: App\Service\MyUserProvider

firewalls:
    login: 
        path: '^/login$'
        authenticator: App\Authenticator\MyLoginAuthenticator
        
    main: 
        path: '.*'
        authenticator: 
            - session
            - App\Authenticator\MyFallbackAuthenticator

voters:
    EDIT_POSTS: App\Voter\PostVoter
    VIEW_USERS: App\Voter\UserVoter
    
voter_strategy: all

access_control:
    /api: [ROLE_API]
```
