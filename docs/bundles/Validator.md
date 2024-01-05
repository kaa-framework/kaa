# Validator Bundle

Модуль валидации просто позволяет делать красивый yaml-конфиг для валидатора.

Этот модуль после регистрации регистрирует сервис
`kernel.validator` с алиасом `Kaa\Validator\ValidatorInterface`.

Пример конфигурации:
```yaml
validator:
    scan:
        - App\Model
        - App\Entity
```
