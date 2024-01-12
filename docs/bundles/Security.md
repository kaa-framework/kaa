# Security Bundle

Модуль security просто позволяет делать красивый yaml-конфиг для Security.

Это модуль зарегистрирует сервис `kernel.security` с алиасом `Kaa\Security\SecurityInterface`

Пример конфигурации:
```yaml
scan:
   - App\Voter

session:
    cookie_name: x-session
    lifetime: 3600
    user_provider: @App\CustomUserProvider

firewalls:
    login: 
        path: '^/login$'
        authenticators:
            - { service: App\Authenticator\MyLoginAuthenticator }
        
    main: 
        path: '.*'
        authenticators: 
            - { service: Kaa\Security\Authenticator\SessionAuthenticator }
            - { service: App\Authenticator\MyFallbackAuthenticator }

voters:
    EDIT_POSTS: { service: App\Voter\PostVoter }
    VIEW_USERS: { service: App\Voter\UserVoter }

access_control:
    ^/api: [ROLE_API]
```
