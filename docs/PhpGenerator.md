# PhpGenerator - библиотека для удобной генерации кода

### Содержание

* [Введение](#введение)
* [Примеры](#примеры)
* [Описание](#описание)
  * [PhpClass](#phpclass)
  * [ClassType](#classtype)
  * [PhpConst](#phpconst)
  * [PhpProperty](#phpproperty)
  * [PhpFunction](#phpfunction)
  * [Visibility](#visibility)
  * [PhpParameter](#phpparameter)
  * [PhpAttribute](#phpattribute)

### Введение

Типичный пример использования:
Создаём объект файла, шаблон метода в twig, затем сохраняем сгенерированный файл

```php
<?php

require '../vendor/autoload.php';

use Kaa\PhpGenerator\PhpFile;
use Kaa\PhpGenerator\PhpClass;
use Kaa\PhpGenerator\PhpFunction;
use Kaa\PhpGenerator\PhpParameter;

$file = (new PhpFile(namespace: 'App\Generated', twigPath: '../templates'))
    ->setDeclareStrict(true);
    
$class = (new PhpClass(name: 'Hello'))
    ->addMethod(
        (new PhpFuction(name: 'sayHello', visibility: Visibility::Public))
            ->setReturnType('void')
            ->addParameter(new PhpParameter(name: 'whom', type: 'string'))
            ->setBody($twig->render('sayHello', ['useWhom' => true]))
    );
    
$file->addClass($class);
file_put_contents('Hello.php', $file->render());
```

### Примеры

#### Генерация класса с полями

```php 
<?php

require '../vendor/autoload.php';

use Kaa\PhpGenerator\PhpFile;
use Kaa\PhpGenerator\PhpClass;
use Kaa\PhpGenerator\PhpAttribute;

$class (new PhpClass(name: 'Foo', type: ClassType::Abstract))
    ->setComment('/** Doc block content */')
    ->addAttribute(new PhpAttribute(
        class: SomeAttribute::class,
        parameters: ["'value'", 1],
        namedParameters: ['max' => 10],
    ))
    ->addProperty(new PhpProperty(
        name: 'baz',
        visibility: Visibility::Private
        type: 'string',
        nullable: true,
        value: "'bar'",
    ))
    ->addConst(new PhpConst(
        name: 'MY_CONST',
        visibility: Visibility::Public,
        value: "'buzz'",
    ));

$file = (new Kaa\PhpGenerator\PhpFile(namespace: 'App\Generated'))
    ->setDeclareStrict(true)
    ->addClass($class);
    
echo $file->generate(); 
```
Код выше создаст такой класс:
```php
<?php

declare(strict_types=1);

namespace App\Generated;

#[Kaa\SomeAttribute('value', 1, max: 10)]
/** Doc block Content */
abstract class Foo {
    public const MY_CONST = 'buzz';
    
    private ?string $baz = 'bar';
}
```

#### Создание функций (и методов)

```php
<?php

require '../vendor/autoload.php';

use Kaa\PhpGenerator\PhpFucntion;
use Kaa\PhpGenerator\PhpAttribute;

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader, [
    'cache' => '/path/to/compilation_cache',
]);

$function = (new PhpFunction(name: 'sayHello'))
    ->setReturnType('void')
    ->addParameter(
        (new PhpParameter(name: 'whom', type: 'string', nullable: true))
        ->addAttribute(class: SensitiveParameter::class)
    )
    ->addParameter(new PhpParameter(name: 'shout', type: 'bool', value: 'false'))
    ->setBody($twig->render('sayHello', ['useWhom' => true]));

echo $function->generate();
```

template/sayHello.php.twig:
```php
{% if useWhom %}
    $whom ??= 'world';
    echo "Hello $whom";
{% else %}
    echo $whom;
{% endif %}

if ($shout) {
    echo '!';
}
```

Код выше создаст такую функцию:
```php
function sayHello(#[\SensitiveParameter] ?string $whom, bool shout = false): void
{
    $whom ??= 'world';
    echo "Hello $whom";
    
    if ($shout) {
        echo '!';
    }
}
```

#### Описание

#### GeneratorInterface
```php
interface Kaa\PhpGenerator\PhpGeneratorInterface
{
    /** Возвращает строку со сгенерированным кодом */
    public function generate(): string;
}
```

Этот интерфейс реализуют все остальные классы этой библиотеки

#### PhpFile
```php
class Kaa\PhpGenerator\PhpFile implements Kaa\PhpGenerator\PhpGeneratorInterface
{
    /**
     * @param string|null $namespace пространство имён сгенерированного файла
     * @param bool $declareStrict нужно ли добавлять declare(strict_types=1);
     */
    public function __construct(?string $namespace = null, bool $declareStrict = false);
    
    public function setDeclareStrict(bool $declareStrict): self;
    
    public function addClass(PhpClass $class): self;
    public function addFunction(PhpFunction $function): self;
}
```

Файл сначала генерирует классы в том порядке, в котором они были добавлены,
потом генерирует функции в том порядке, в котором они были добавлены.

#### PhpClass
```php
class Kaa\PhpGenerator\PhpClass implements Kaa\PhpGenerator\PhpGeneratorInterface
{
    /**
     * @param stringl $name Ммя класса
     * @param ClassType $type Вид class-like структуры 
     */
    public function __construct(string $name, ClassType $type = ClassType::Ordinary);
    
    public function setComment(string $comment): self;
    
    public function addAttribute(PhpAttribute $attribute): self;
    public function addConst(PhpConst $const): self;
    public function addProperty(PhpProperty $property): self;
    public function addMethod(PhpFunction $method): self;
}
```

Класс генерирует своих членов в следующем порядке:
* Константы в порядке их добавления
* Свойства в порядке их добавления
* Методы в порядке их добавления

#### ClassType
```php
enum Kaa\PhpGenerator\ClassType: string 
{
    case Ordinary = 'class';
    case Final = 'final class';
    case Abstract = 'abstract class';
    case Interface = 'interface';
    case Trait = 'trait';
}
```

#### PhpConst
```php
class Kaa\PhpGenerator\PhpConst implements Kaa\PhpGenerator\PhpGeneratorInterface
{
    /**
     * @param string $name Имя константы
     * @param Visibility $visibility Видимость константы
     * @param string $value Значение константы
     */
    public function __construct(
        string $name,
        Visibility $visibility,
        string $value,
    );
}
```

#### PhpProperty
```php
class Kaa\PhpGenerator\PhpProperty implements Kaa\PhpGenerator\PhpGeneratorInterface
{
    /**
     * @param string $name Имя свойства
     * @param Visibility $visibility Видимость свойства
     * @param string|null $type Тип свойства
     * @param bool $nullable Может ли свойство быть null
     * @param string|null $value Значение свойства по умолчанию
     */
    public function __construct(
        string $name,
        Visibility $visibility,
        ?string $type = null,
        bool $nullable = false,
        ?string $value = null,
    );
}
```

#### PhpFunction
```php
class Kaa\PhpGenerator\PhpFunction implements Kaa\PhpGenerator\PhpGeneratorInterface
{
    /**
     * @param string $name Имя функции/метода
     * @param Visibility|null $visibility Видимость метода (устанавливайте, только если используется внутри класса)
     */
    public function __construct(string $name, ?Visibility $visibility = null);
    
    public function setComment(string $comment): self;
    public function setReturnType(?string $returnType);
    
    public function addAttribute(PhpAttribute $attribute): self;
    public function addParameter(PhpParameter $const): self;
    public function setBody(string $body): self;
    
    /** Только если используется внутри класса */
    public function setAbstract(bool $abstract): self;
    
    /** Только если используется внутри класса */
    public function setFinal(bool $final): self;
}
```
Используйте `$visibility`, `setAbstract` и `setFinal`, только если
добавляете эту функцию к классу в качестве метода.

#### Visibility
```php
enum Kaa\PhpGenerator\Visibility: string
{
    case Public = 'public';
    case Protected = 'protected';
    case Private = 'private';
}
```

#### PhpParameter
```php
class Kaa\PhpGenerator\PhpParameter implements Kaa\PhpGenerator\PhpGeneratorInterface
{
    /**
     * @param string $name Имя параметра
     * @param string|null $type Тип параметра
     * @param bool $nullable Может ли принимать значение null
     * @param string|null $value значение по умолчанию
     */
    public function __construct(
        string $name,
        ?string $type = null,
        bool $nullable = false,
        ?string $value = null,
    );
    
    public function addAttribute(PhpAttribute $attribute): self;
}
```

#### PhpAttribute
```php
class Kaa\PhpGenerator\PhpAttribute implements Kaa\PhpGenerator\PhpGeneratorInterface
{
    /**
     * @param string $class Класс аттрибута
     * @param string[] $parameters Параметры
     * @param string[] $namedParameters Именованные параметры
     */
    public function __construct(
        string $class,
        array $parameters = [],
        array $namedParameters = [],
    );
}
```
