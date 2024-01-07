# Validator

### Содержание

* [Введение](#введение)
* [Использование генератора валидатора](#использование-генератора-валидатора)
* [Конфигурация валидатора](#конфигурация-валидатора)
* [Пример использования правил](#пример-использования-правил)
* [Пример обработки ошибок](#пример-обработки-ошибок)
* [Правила](#правила)

### Введение

`Validator` реализует набор основных необходимых проверок данных во время работы веб-приложения.
Возможно использовать как совместно с Kaa Framework, так и в качестве самостоятельного сервиса.

Поддерживается рекурсивная валидация моделей.
Если поле является другой моделью,
то валидатор попробует проверить все имеющиеся проверки и в этой модели. Также,
если полем является массив, то валидатор проверит каждый элемент массива в соответствии
с обычными правилами валидации.

### Использование генератора валидатора

Валидатор использует генерацию кода для обработки моделей и генерирует класс
`Kaa\Generated\Validator\Validator`, реализующий `ValidatorInterface`.

```php
interface ValidatorInterface
{
    function validate(object $model): Violation[];
}
```

Пример генерации валидатора:

```php
$sharedConfig = new SharedConfig('./generated');
$config = [
    'scan' => [
      'App\Model',
      'App\Entity',
    ],
];

$validatorGenerator = new ValidatorGenerator();
$validatorGenerator->generate($sharedConfig, $config);

// теперь можно использовать сам валидатор

/** @var ValidatorInterface */
$validator = new \Kaa\Generated\Validator\Validator();
$violations = $validator->validate(new App\Model\SomeModel());
```

### Конфигурация валидатора

Для корректной работы валидатора необходимо создать конфигурацию в формате php массива, которая будет описывать
основные директории работы валидатора.

* `scan` - в данном поле необходимо указать пространства имен, для классов в которых нужно создать правила
  валидации.

#### Пример использования правил

```php
namespace SampleProject\Model;

use Kaa\Validator\Assert;

class NoteModel
{
    #[Assert\Url]
    public string $url;

    #[Assert\Email]
    public string $email = 'testemail@gmail.com';
    #[Assert\NotBlank]
    private string $title;

    /**
     * @var int[]
     */
    #[Assert\All([new Assert\GreaterThan(18)])]
    public array $arrayOfAges = [18, 12, 25, 19];

    #[Assert\NotNull]
    public NoteModel $someModel;

    #[Assert\Blank]
    private string $text;
}
```

### Violation

Также класс реализует методы, которые позволяют обращаться к полям описанным выше.

```php
class Violation
{
    /** Получить класс, в котором было найдено нарушение */   
    public function getClassName(): string
    
    /** Получить имя свойства, в котором было найдено нарушений */
    public function getPropertyName(): string
    
    /* Получить сообщение о нарушении */
    public function getMessage(): string
    
    /* Если свойство, в котором обнаружено нарушение, само является моделью, то возвращает список нарушений в полях этого свойства */
    public function getViolations(): Violation[]|null;
}
```

### Пример обработки ошибок

При необходимости обработки ошибок нужно написать пользовательский класс
ошибки, который будет парсить возвращаемый массив ошибок.

```php
use Exception;
use Kaa\Validator\Violation;

class ValidationException extends Exception
{
    /**
     * @param Violation[] $violationList
     */
    public function __construct(array $violationList)
    {
        $message = '';
        foreach ($violationList as $violation) {
            $message .= sprintf('%s - %s ', $violation->getPropertyName(), $violation->getMessage());
        }

        parent::__construct($message);
    }
}
```

### Правила

### AssertInterface

Все правила реализуют `AssertInterface`.
Для добавления своего правила достаточно просто создать атрибут, реализующий этот интерфейс.

```php
interface AssertInterface {
    /**
     * @param ReflectionProperty $reflectionProperty свойство, для которого нужно сгенерировать код, проверяющий правило
     * @param string $hostVariable переменная, в которой содержится это свойство
     * @param string $violationsPropertyName Переменная, которая хранит информацию о нарушениях 
     * @return string Код, который выполняет проверку и добавляет результат в $violations[]
     */
    public function generate(
        ReflectionProperty $reflectionProperty,
        string $hostVariable,
        string $violationsPropertyName,
    ): string;
}

```

### Список поддерживаемых правил валидации

1. [Blank](#blank)
2. [Email](#email)
3. [GreaterThan](#greaterthan)
4. [GreaterThanOrEqual](#greaterthanorequal)
5. [IsFalse](#isfalse)
6. [IsTrue](#istrue)
7. [LessThan](#lessthan)
8. [LessThanOrEqual](#lessthanorequal)
9. [Negative](#negative)
10. [NegativeOrZero](#negativeorzero)
11. [NotBlank](#notblank)
12. [NotNull](#notnull)
13. [Positive](#positive)
14. [PositiveOrZero](#positiveorzero)
15. [Range](#range)
16. [Url](#url)
17. [All](#all)

### Blank

Проверяет, является ли значение пустым, то есть равным пустой строке или `null`.

#### Разрешенные типы полей

* string

#### Пример использования

```php
namespace SampleProject\Model;

use Kaa\Validator\Assert;

class NoteModel
{ 
    #[Assert\Blank]
    public string $text;
}
```

#### Параметры

* `message` - Это сообщение будет отображаться, если значение не пустое.  
  `type`: `string`, `default`: `This value should be blank.`
* `allowNull` - Флаг разрешающий `Nullable types`.  
  `type`: `bool`, `default`: `false`

### Email

Проверяет, является ли значение действительным адресом электронной почты.

#### Разрешенные типы полей

* string

#### Пример использования

```php
namespace SampleProject\Model;

use Kaa\Validator\Assert;

class NoteModel
{ 
    #[Assert\Email]
    public string $email;
}
```

#### Параметры

* `message` - Это сообщение отображается, если базовые данные не являются действительным адресом электронной почты.  
  `type`: `string`, `default`: `This value is not a valid email address.`
* `allowNull` - Флаг разрешающий `Nullable types`.  
  `type`: `bool`, `default`: `false`
* `mode` - Этот параметр определяет шаблон, используемый для проверки адреса электронной почты. Допустимые значения:
    * `loose` использует простое регулярное выражение (просто проверяет наличие хотя бы одного символа @ и т. д.). Эта
      проверка слишком проста, и вместо нее рекомендуется использовать один из других режимов;
    * `html5` использует регулярное выражение элемента ввода электронной почты HTML5, за исключением того, что оно
      требует присутствия tld.
    * `html5-allow-no-tld` использует точно такое же регулярное выражение, что и элемент ввода электронной почты HTML5,
      что делает внутреннюю проверку согласованной с той, которую предоставляют браузеры.

* `type`:  `string`, `default:` `loose`

### GreaterThan

Проверяет, что значение больше, чем другое значение, определенное в параметрах.
Чтобы заставить значение быть больше или равно другому значению, см. GreaterThanOrEqual. Чтобы заставить значение быть
меньше другого значения, см. LessThan.

#### Разрешенные типы полей

* int
* float

#### Пример использования

```php
namespace SampleProject\Model;

use Kaa\Validator\Assert;

class NoteModel
{ 
    #[Assert\GreaterThan(18)]
    public int $age = 18;
}
```

#### Параметры

* `value` - Эта опция обязательна. Она определяет значение сравнения.
* `message` - Это сообщение будет показано, если значение не превышает значение сравнения.  
  `type`: `string`, `default`: `This value should be greater than {{ compared_value }}.`

### GreaterThanOrEqual

Проверяет, что значение больше или равно другому значению, определенному в параметрах.
Чтобы заставить значение быть больше другого значения, см. GreaterThan.

#### Разрешенные типы полей

* int
* float

#### Пример использования

```php
namespace SampleProject\Model;

use Kaa\Validator\Assert;

class NoteModel
{ 
    #[Assert\GreaterThanOrEqual(18)]
    public int $age = 18;
}
```

#### Параметры

* `value` - Эта опция обязательна. Она определяет значение сравнения.
* `message` - Это сообщение будет показано, если значение не превышает или равно значению сравнения.  
  `type`: `string`, `default`: `This value should be greater than or equal to {{ compared_value }}.`

### LessThan

Проверяет, что значение меньше другого значения, определенного в параметрах.
Чтобы заставить значение быть меньше или равно другому значению, см. LessThanOrEqual.
Чтобы заставить значение быть больше другого значения, см. GreaterThan.

#### Разрешенные типы полей

* int
* float

#### Пример использования

```php
namespace SampleProject\Model;

use Kaa\Validator\Assert;

class NoteModel
{ 
    #[Assert\LessThan(101)]
    public int $age = 18;
}
```

#### Параметры

* `value` - Эта опция обязательна. Она определяет значение сравнения.
* `message` - Это сообщение будет показано, если значение не меньше значения сравнения.  
  `type`: `string`, `default`: `This value should be less than {{ compared_value }}.`

### LessThanOrEqual

Проверяет, что значение меньше или равно другому значению, определенному в параметрах.
Чтобы заставить значение быть меньше другого значения, см. LessThan.

#### Разрешенные типы полей

* int
* float

#### Пример использования

```php
namespace SampleProject\Model;

use Kaa\Validator\Assert;

class NoteModel
{ 
    #[Assert\LessThanOrEqual(101)]
    public int $age = 18;
}
```

#### Параметры

* `value` - Эта опция обязательна. Она определяет значение сравнения.
* `message` - Это сообщение будет показано, если значение не меньше или равно значению сравнения.  
  `type`: `string`, `default`: `This value should be less than or equal to {{ compared_value }}.`

### IsFalse

Проверяет, что значение является ложным.

#### Разрешенные типы полей

* bool

#### Пример использования

```php
namespace SampleProject\Model;

use Kaa\Validator\Assert;

class NoteModel
{ 
    #[Assert\IsFalse]
    public bool $flag = false;
}
```

#### Параметры

* `message` - Это сообщение отображается, если базовые данные не являются ложными.  
  `type`: `string`, `default`: `This value should be false.`

### IsTrue

Проверяет, что значение истинно.

#### Разрешенные типы полей

* bool

#### Пример использования

```php
namespace SampleProject\Model;

use Kaa\Validator\Assert;

class NoteModel
{ 
    #[Assert\IsTrue]
    public bool $flag = true;
}
```

#### Параметры

* `message` - Это сообщение отображается, если базовые данные не являются истинными.  
  `type`: `string`, `default`: `This value should be true.`

### Negative

Проверяет, является ли значение отрицательным числом. Ноль не является ни положительным, ни отрицательным,
поэтому вы должны использовать NegativeOrZero, если хотите разрешить ноль в качестве значения.

#### Разрешенные типы полей

* int
* float

#### Пример использования

```php
namespace SampleProject\Model;

use Kaa\Validator\Assert;

class NoteModel
{ 
    #[Assert\Negative]
    public int $age = 18;
}
```

#### Параметры

* `message` - Сообщение по умолчанию предоставляется, когда значение не меньше нуля.  
  `type`: `string`, `default`: `This value should be negative.`

### NegativeOrZero

Проверяет, является ли значение отрицательным числом или равным нулю.
Если вы не хотите использовать ноль в качестве значения, вместо этого используйте Negative.

#### Разрешенные типы полей

* int
* float

#### Пример использования

```php
namespace SampleProject\Model;

use Kaa\Validator\Assert;

class NoteModel
{ 
    #[Assert\NegativeOrZero]
    public int $age = 18;
}
```

#### Параметры

* `message` - Сообщение по умолчанию, предоставляемое, когда значение не меньше или равно нулю.  
  `type`: `string`, `default`: `This value should be negative or zero.`

### Positive

Проверяет, является ли значение положительным числом. Ноль не является ни положительным, ни отрицательным,
поэтому вы должны использовать PositiveOrZero, если хотите разрешить ноль в качестве значения.

#### Разрешенные типы полей

* int
* float

#### Пример использования

```php
namespace SampleProject\Model;

use Kaa\Validator\Assert;

class NoteModel
{ 
    #[Assert\Positive]
    public int $age = 18;
}
```

#### Параметры

* `message` - Сообщение по умолчанию, предоставляемое, когда значение не больше нуля.  
  `type`: `string`, `default`: `This value should be positive.`

### PositiveOrZero

Проверяет, является ли значение положительным числом или равным нулю.
Если вы не хотите использовать ноль в качестве значения, вместо этого используйте Положительное значение.

#### Разрешенные типы полей

* int
* float

#### Пример использования

```php
namespace SampleProject\Model;

use Kaa\Validator\Assert;

class NoteModel
{ 
    #[Assert\PositiveOrZero]
    public int $age = 18;
}
```

#### Параметры

* `message` - Сообщение по умолчанию, предоставляемое, когда значение не больше или равно нулю.  
  `type`: `string`, `default`: `This value should be positive or zero.`

### NotNull

Проверяет, что значение не строго равно `null`. Чтобы убедиться,
что значение не пустое (не пустая строка), см. ограничение NotBlank.

#### Разрешенные типы полей

* int
* float
* string
* bool
* object

#### Пример использования

```php
namespace SampleProject\Model;

use Kaa\Validator\Assert;

class NoteModel
{ 
    #[Assert\NotNull]
    public NewNoteModel $someModel;
}
```

#### Параметры

* `message` - Это сообщение будет показано, если значение равно `null`.  
  `type`: `string`, `default`: `This value should not be null.`

### NotBlank

Проверяет, что значение не является пустой строкой.

#### Разрешенные типы полей

* string

#### Пример использования

```php
namespace SampleProject\Model;

use Kaa\Validator\Assert;

class NoteModel
{ 
    #[Assert\NotBlank]
    public string $title;
}
```

#### Параметры

* `message` - Это сообщение будет отображаться, если значение пустое.
  `type`: `string`, `default`: `This value should not be blank.`

### Range

Проверяет, находится ли заданное число между некоторым минимумом и максимумом.

#### Разрешенные типы полей

* int
* float

#### Пример использования

```php
namespace SampleProject\Model;

use Kaa\Validator\Assert;

class NoteModel
{ 
    #[Assert\Range(
            min: 18,
            max: 45
    )]
    public int $age = 20;
}
```

#### Параметры

* `message` - Это сообщение будет отображаться, если значение не попадает в заданный диапазон.
  `type`: `string`, `default`: `The value must lie in the range from {{ min }} to {{ max }}`
* `min` - Это обязательный параметр. Устанавливает нижнею границу диапазона.  
  `type`: `int, float`
* `max` - Это обязательный параметр. Устанавливает верхнюю границу диапазона.  
  `type`: `int, float`

### Url

Проверяет, является ли значение допустимой строкой URL.

#### Разрешенные типы полей

* string

#### Пример использования

```php
namespace SampleProject\Model;

use Kaa\Validator\Assert;

class NoteModel
{ 
    #[Assert\Url(
        protocols: ['http', 'https', 'ftp'],
    )]
    public $bioUrl;
}
```

#### Параметры

* `message` - Это сообщение будет отображаться, если значение будет невалидно.
  `type`: `string`, `default`: `This value is not a valid URL.`
* `protocol` - Протоколы, которые считаются действительными для данного URL.
  Например, если вы также считаете действительными URL типа ftp://,
  переопределите массив протоколов, перечислив http, https, а также ftp.  
  `type`: `array`, `default`: `['http', 'https']`
* `relativeProtocol` - Если `true`, протокол считается необязательным при
  проверке синтаксиса данного URL. Это означает, что и *http://*, и *https://*
  являются допустимыми, а также относительными URL, не содержащими протокола
  (например, *//example.com*).  
  `type`: `bool`, `default`: `false`
* `allowNull` - Флаг разрешающий `Nullable types`.  
  `type`: `bool`, `default`: `false`

### All

Применяет переданные правила ко всем элементам массива.
Сообщение о нарушениях является конкатенацией через `;` сообщений о нарушениях всех параметров.

#### Разрешенные типы полей

* array

#### Пример использования

```php
namespace SampleProject\Model;

use Kaa\Validator\Assert;

class NoteModel
{ 
    /** @var int[] */
    #[Assert\All([new Assert\GreaterThan(10)])]
    public array $ages = [20, 30];
}
```

#### Параметры

* `asserts` - список правил, которые нужно применить
