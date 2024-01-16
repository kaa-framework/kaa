# Database Bundle

Почти полноценная ORM, но нет транзакий, QueryBuilder и fetch-join.

Пример конфигурации:
```yaml
database:
    default:
        driver:
            type: pdo_mysql
            host: database
            database: demo
            user: demo
            password: demo

        scan:
            - App\Entity
```

На данный момент поддерживается только драйвер `pdo_mysql`.

`EntityManager` для соединения `default` можно получить через `DI` просто по типу `EntityManagerInerface`.
Для других соединений имя сервиса будет `database.<имя_соединения>`.

`OneToMany` и `ManyToOne` почти аналогичны им же в `Doctrine`.

`Column` поддерживает типы:
* `int`, `float`, `string` (в любых их представления в базе);
* `DateTimeImmutabele` (`DATETIME` в базе);
* `SIMPLE_ARRAY` (любое строковое представление в базе).

### EntityManger

`EntityManager::new(string $entityClass)` - создать новый объект сущности.

`EntityManager::find(string $entityClass, int $id)` - найти сущность по `id`.

`EntityManager::findOneBy(string $entityClass, array $criteria)` - найти сущность по параметрам.

`EntityManager::findOneBy(string $entityClass, array $criteria)` - найти сущность по параметрам.

`EntityManager::findBy(string $entityClass, array $criteria, array $oder = [], ?int $limit = null, ?int $offset = null)` - найти несколько сущностей по параметрам.

`EntityManager::flush()` - сохранить изменения в базу.
