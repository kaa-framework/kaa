# Database Bundle

Почти полноценная ORM, но нет транзакций

## Конфигурация

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

### Entity
Для того чтобы создать класс-представление таблицы базы данных, необходимо создать абстрактный класс, реализующий `EntityInterface` и указать для него атрибут 
`Db\Entity`. В него можно передать `$table`, если имя класса не совпадает с названием таблицы:
```php
#[Db\Entity(table: 'NotArticle')]
abstract class Article implements EntityInterface
{

}
```

### Поля

Чтобы указать столбцы таблицы, нужно создать поля класса, добавив к ним соответствующие атрибуты:

`Column`

Соответствие столбцу в таблице. Можно указать `type`, `name` и `nullable`. По умолчанию тип является `Primitive`, а имя берётся равным названию поля. 

`Column` поддерживает типы:
* `int`, `float`, `string` (в любых их представления в базе);
* `DateTimeImmutabele` (`DATETIME` в базе);
* `SIMPLE_ARRAY` (любое строковое представление в базе).

`Id`

Указывает что данное поле соответствует столбцу Id в таблице

`OneToMany`

Указывает связь *один ко многим* (Например, у одной статьи много комментариев). Нужно указать `targetEntity` - название таблицы
к которой идёт связь, а также `mappedBy` - столбец по которому искать связь.
> ⚠️ WARNING
>
> `OneToMany` можно использовать только если определён связанный `ManyToOne` у второй сущности

`ManyToOne`

Указывает связь *многие к одному* (Много комментариев принадлежат одному пользователю). Нужно указать `targetEntity` - название таблицы
к которой идёт связь, кроме того можно указать `columnName` - название столбца по которому делается связь и `nullable` - возможность поля быть Null.
Определять `ManyToOne` можно без дальнейшего определения `OneToMany` у второй сущности. 

### Пример

```php
<?php

namespace App\Entity;

use DateTimeImmutable;
use Kaa\Component\Database\Attribute as Db;
use Kaa\Component\Database\EntityInterface;

#[Db\Entity]
abstract class Article implements EntityInterface
{
    #[Db\Id]
    #[Db\Column]
    protected ?int $id = null;

    #[Db\ManyToOne(User::class)]
    protected User $author;
    #[Db\ManyToOne(Topic::class)]
    protected Topic $topic;

    #[Db\Column]
    protected string $title;

    #[Db\Column]
    protected string $body;

    #[Db\Column(type: Db\Type::DateTimeImmutable)]
    protected DateTimeImmutable $created;

    #[Db\Column(type: Db\Type::DateTimeImmutable)]
    protected DateTimeImmutable $changed;

    /** @var ArticleTag[] */
    #[Db\OneToMany(ArticleTag::class, mappedBy: 'article')]
    protected array $tags = [];

    /** @var Comment[] */
    #[Db\OneToMany(Comment::class, mappedBy: 'article')]
    protected array$comments = [];

    public function getId(): int
    {
        return $this->id ?? -1;
    }

    public function setId(int $id): Article
    {
        $this->id = $id;
        return $this;
    }

    public function getTopicId(): int
    {
        return $this->topic->getId();
    }

    public function geTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): Article
    {
        $this->title = $title;
        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): Article
    {
        $this->body = $body;
        return $this;
    }


    public function getCreationTime(): DateTimeImmutable
    {
        return $this->created;
    }

    public function setCreationTime(DateTimeImmutable $created): Article
    {
        $this->created = $created;
        return $this;
    }

    public function getChangingTime(): DateTimeImmutable
    {
        return $this->changed;
    }

    public function setChangingTime(DateTimeImmutable $changed): Article
    {
        $this->changed = $changed;
        return $this;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function setAuthor(User $user): Article
    {
        $this->author = $user;
        return $this;
    }

    public function getTopic(): Topic
    {
        return $this->topic;
    }

    public function setTopic(Topic $topic): Article
    {
        $this->topic = $topic;
        return $this;
    }

    /**
     * @return ArticleTag[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function addTag(ArticleTag $tag): Article
    {
        $this->tags[] = $tag;
        return $this;
    }

    /**
     * @return Comment[]
     */
    public function getComments(): array
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): Article
    {
        $this->comments[] = $comment;
        return $this;
    }
}

```

## EntityManger

`EntityManager::new(string $entityClass)` - создать новый объект сущности.

`EntityManager::find(string $entityClass, int $id)` - найти сущность по `id`.

`EntityManager::findOneBy(string $entityClass, array $criteria)` - найти сущность по параметрам.

`EntityManager::findOneBy(string $entityClass, array $criteria)` - найти сущность по параметрам.

`EntityManager::findBy(string $entityClass, array $criteria, array $oder = [], ?int $limit = null, ?int $offset = null)` - найти несколько сущностей по параметрам.

`EntityManager::flush()` - сохранить изменения в базу.

## QueryBuilder

QueryBuilder - объект, предоставляющий возможность создавать сложные запросы в несколько этапов.
> ⚠️ WARNING
> 
> По возможности, всё равно используйте стандартный EntityManager, т.к. это проще

Прежде чем начать работу с QueryBuilder необходимо создать его экземпляр, используя EntityManager:
```
EntityManager::createQueryBuilder(string $entityClass, string alias)
```
QueryBuilder создаётся для определённой сущности, поэтому первым аргументом метода его создания является имя Entity-класса
(Объект этого класса будет результатом запроса, об этом далее).
Кроме того, необходимо передать алиас - сокращённое имя, которое будет использоваться при формировании запроса в методах QueryBuilder.

### Доступные методы
QueryBuilder обладает несколькими базовыми методами, которые позволяют сгенерировать сложный запрос.

**Доступные методы:**

**`QueryBuilder::select(string $select)`**

Позволяет указать какие именно столбца и с какими именами вернуть в запросе. 
Работает как в SQL, то есть `qb->select('id, name')` сгенерирует `SELECT id, name FROM ...`. Если select не указан, то 
создастся запрос, который вернёт все столбцы из всех участвовавших в запросе таблиц. 
> ⚠️ WARNING
>
> При использовании `select` для получения результатов необходимо использовать метод `getCustomResult`, о котором написано ниже.
> Использование других способов получения результатов в данном случае невозможно.

**`QueryBuilder::join(string $join, string $alias)`** 

Позволяет реализовать `INNER JOIN`. Важно уточнить, что join возможен, только если в сущности имеется поле, у которого указан
атрибут `OneToMany` или `ManyToOne` и иметь аргумент `join` будет следующий вид: `алиас сущности.имя поля`. Например, предположим,
что имеется сущность `Article`, у которой есть поле `comments`, которое имеет атрибут `OneToMany(Comment::class, mappedBy: 'article')`. Для того чтобы
сджоинить `Article` и `Comment` необходимо написать следующее:
```php
$qb = $entityManager->createQueryBuilder(Article::class, 'a');
$qb->join('a.comments', 'c')...
```
> ⚠️ WARNING
>
> При получении результата из запросов, составленных с использованием `join` и `leftJoin` нет гарантии того, что сджоиненные
> сущности будут иметь инициализированные поля. Вполне вероятно, что эти сущности будут "ленивыми" (то есть инициализированным
> в них будет только id). Чтобы избежать такого, используйте `fetchJoin` и `leftFetchJoin` соответственно.

**`QueryBuilder::leftJoin(string $join, string $alias)`**

Позволяет реализовать `LEFT JOIN`, в остальном аналогичен `join`.

**`QueryBuilder::fetchJoin(string $join, string $alias)`**

Аналогичен `join`, однако гарантируется, что все поля сджоиненных сущностей, которые были найдены с помощью запроса будут инициализированы.
> ⚠️ WARNING
>
> При одновременном использовании `join/leftJoin` с `fetchJoin/leftFetchJoin` гарантия инициализации распространяется 
> только на `fetchJoin/leftFetchJoin` джоинящиеся к сущности для которой создан QueryBuilder или к приджоиненным 
> с помощью `fetchJoin/leftFetchJoin` сущностям! Если сущность джоинется с помощью `fetchJoin/leftFetchJoin` к сущности
> приджоиненной с помощью `leftJoin/join` (например `->join('a.user', 'u')->fetchJoin('u.group', 'g')`) то её гарантия инициализации
> теряется по очевидным причинам!

> ⓘ INFO
> 
> На самом деле, гарантия никуда не теряется и, если в каком-нибудь запросе будет получена сущность, которая содержит ранее
> полученную\приджоиненную с помощью `fetchJoin/leftFetchJoin`, то безусловно содержащаяся сущность будет инициализирована.
> 
> Почему же говориться об отсутствии гарантии инициализации? 
> 
> Дело в том, что при использовании `leftJoin/join` в результате запроса вполне может содержаться "ленивая" сущность, у
> которой будет доступно только значение её id, поэтому ничего другого из неё напрямую получить нельзя (в том числе и любые джоины).
> Однако из-за особенностей работы QueryBuilder, никакая fecth-сущность не будет потеряна, даже если не войдёт в результат конкретно этого запроса,
> поэтому позже можно будет получить полностью инициализированную сущность, даже не делая `fetchJoin/leftFetchJoin`.
> 
> Тем не менее все джоины рекомендуется прописывать явно, а не надеятся на гарантию инициализации от сделанных ранее запросов.
> 
> Хорошей практикой будет прописывание в начале всех `fetchJoin/leftFetchJoin`, а уже потом `leftJoin/join`, дабы избежать потери
> данных из запроса

**`QueryBuilder::leftFetchJoin(string $join, string $alias)`**
 
Аналог `LEFT JOIN` для `fetchJoin`

**`QueryBuilder::where(Expr $expr)`**

Позволяет задавать условия по которым отбирать строки из таблиц. В качестве аргумента должен быть передан класс, реализующий
`ExprInterface`. В Kaa существует 3 таких класса: `Expr`, `ExprAnd`, `ExprOr`. `Expr` принимает в конструкторе сроку, содержащее выражение.
`ExprAnd` и `ExprOr` принимают массив `ExprInterface`, но при генерации запроса ставят между выражениями `AND` и `OR` соответственно.
Таким образом, можно делать сложные выражения. Кроме того, `Expr`, `ExprAnd` и `ExprOr` можно создавать с помощью статических методов
`Expr::e`, `Expr::and`, `Expr::or` соответственно. 
> ⚠️ WARNING
>
> Если метод используется несколько раз, например: `->where(id > 1)->where(id < 100)`, то
> последний `where` перезапишет все ранее введённые условия другими `where`

**`QueryBuilder::groupBy(array $columns)`**

Позволяет добавить `GROUP BY` к запросу. В аргументах предполагается массив с названиями столбцов, по которым делать группировку.
> ⚠️ WARNING
>
> Как и `where` перезаписывает все ранее введённые условия для `GROUP BY`

**`QueryBuilder::having(Expr $expr)`**

Позволяет добавить условия по которым отбирать группы. Как и `where` принимает класс, реализующий `ExprInterface` и
также перезаписывает ранее введённые условия для `HAVING`

**`QueryBuilder::setMaxResult(int $limit)`**

Позволяет установить `LIMIT` для запроса (то есть максимальное количество строк, которые вернуться).

**`QueryBuilder::setFirstResult(int $offset)`**

Позволяет установить `OFFSET` для запроса (количество строк, удовлетворяющих условию, которые будут пропущены и не вернуться в запросе)

**`QueryBuilder::addOrderBy(string $sort, string $order)`**

Позволяет указать по каким столбцам сортировать результат запроса.

**`QueryBuilder::setParameter(string $key, string $value)`**

Иногда может быть полезно указывать какие-либо значения в where и having не напрямую, а через алиасы вида `:алиас`.
Данный метод позволяет указать значение для подобного алиаса

**`QueryBuilder::getSql()`**

Позволяет получить сформированный SQL запрос.

**`QueryBuilder::getResult(string $entityClass)`**

Возвращает результат запроса в виде массива Entity-классов, имя которого передаётся в параметре метода.

**`QueryBuilder::getOneOrNullResult(string $entityClass)`**

Возвращает один EntityClass, если результатом запроса является одна строка. Null - если вернулось 0 строк.
В ином случае возвращается ошибка.

**`QueryBuilder::getCustomResult(callable $hydrateFunction)`**

Передаёт массив результатов в функцию, которая передаётся пользователем. Данная функция должна распарсить полученный результат
и вернуть Entity-классы. Необходима для того, чтобы пользователь мог использовать `select` при этом получив конкретный результат.

### Пример использования
Простой пример, как можно использовать QueryBuilder
```php
$qb = $em->createQueryBuilder(Article::class, 'a');
$qb->leftFetchJoin('a.comments', 'c')->leftFetchJoin('c.author', 'au')->where(Expr::e('a.id > :p1'))->setParameter('p1', 2)->getResult();
```




