## Библиотека валидации для Kaa Framework.

Реализует набор основных необходимых проверок данных во время работы веб-приложения.
Возможно использовать как совместно с Kaa Framework, так и в качестве самостоятельного сервиса.

* `Validator` - Создает конструкции проверки условий для валидации модели.

Валидацию проходят поля моделей, типы которых соответствуют требованиям 
корректных типов проверок. 

Поддерживается рекурсивная валидация моделей. Если поле является другой моделью,
то валидатор попробует проверить все имеющиеся проверки и в этой модели. Также,
если полем является массив, то валидатор проверит каждый элемент массива в соответствии
с обычными правилами валидации.

### Использование валидатора, как самостоятельного сервиса

Для реализации валидации на Ваших данных необходимо создать объект класса
```ValidatorGenerator```. Данный класс реализует интерфейс ```GeneratorInterface```. Он имеет
метод ```generate(array $config): void```. Данный метод отвечает за генерацию класса валидатора.
Параметр ```array $config``` содержит в себе конфигурацию для валидатора. 

```php
class ValidatorGenerator implements GeneratorInterface 
{
    function generate(array $config): void 
}
```

Сгенерированный класс реализует интерфейс ```ValidatorInterface```. Он содержит единственный 
метод ```function validate(object $model): ConstraintViolationList```, который отвечает за валидацию моделей. Аргументом
необходимо передать объект модели для валидации. В качестве 
возвращаемого значения будет массив ```ViolationList```, который содержит всю информацию о возникших
нарушениях.

```php
class Validator implements ValidatorInterface
{
    function validate(object $model): ViolationList
}
```

### Конфигурация валидатора

Для корректной работы валидатора необходимо создать конфигурацию в формате php массива, которая будет описывать
основные директории работы валидатора.

#### Пример конфигурационного файла 

```php
"config" => [
    "scanNamespace" => [
      "App\Model",
      "App\Entity",
    ],
    "ignore" => [
      "App\Model\Builtin",
      "App\Model\User::class",
    ],
    "export" => [
      "directory" => "src\generated",
      "namespace" => "App\Generated",
      "className" => "Validator",
    ]
]
```

#### Описание полей

**scanNamespace** - в данном поле необходимо указать пространства имен, для которых нужно
проводить валидацию моделей.  
**ignore** - здесь указываются пространства имен, которые нужно игнорировать при валидации.  
**export** - в данном поле указывается основная информация об создаваемом объекте валидатора.
* **directory** - директория для сохранения сгенерированного кода.
* **namespace** - пространство имен для сгенерированного валидатора.
* **className** - наименования сгенерированного класса.


### Примеры использования валидатора

```php
/**
 * @var Kaa\Validator\ValidatorInterface $validator 
 */
$validator = new App\Generated\Validator();

$model = new ModelToValidate();
$violationList = $validator->validate($model);
```

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
    #[Assert\GreaterThan(18)]
    public array $array_of_ages = [18, 12, 25, 19];

    #[Assert\NotNull]
    public NoteModel $someModel;

    #[Assert\Blank]
    private string $text;
}
```

### Обработка ошибок

#### Содержание ```ViolationList```

В массиве ```ViolationList``` содержатся объекты ```Violation```. Это объект нарушения, который содержит в себе 
следующие поля:
* ```className``` - содержится наименования класса.
* ```propertyName``` - содержится наименования метода.
* ```message``` - содержится сообщение об возникшем нарушении.

Также класс реализует методы, которые позволяют обращаться к полям описанным выше.

```php
class Violation
{
    private string $className;
    private string $propertyName;
    private string $message;
    
    public function getClassName(): string
    public function getPropertyName(): string
    public function getMessage(): string
}
```

#### Пример обработки ошибок

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

### Список поддерживаемых правил валидации

1. Blank
2. Email
3. GreaterThan
4. GreaterThanOrEqual
5. IsFalse
6. IsTrue
7. LessThan
8. LessThanOrEqual
9. Max
10. Min
11. Negative
12. NegativeOrZero
13. NotBlank
14. NotNull
15. Positive
16. PositiveOrZero
17. Range
18. Type
19. Url

### Blank

Проверяет, является ли значение пустым, то есть равным пустой строке или *null*.

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

* **message** - Это сообщение будет отображаться, если значение не пустое.  
  *type:* **string**, *default:* **This value should be blank.** 
* **allowNull** - Флаг разрешающий **Nullable types**.  
  *type:* **bool**, *default:* **false**

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

* **message** - Это сообщение отображается, если базовые данные не являются действительным адресом электронной почты.  
  *type:* **string**, *default:* **This value is not a valid email address.**
* **allowNull** - Флаг разрешающий **Nullable types**.  
  *type:* **bool**, *default:* **false**
* **mode** - Этот параметр определяет шаблон, используемый для проверки адреса электронной почты. Допустимые значения:  
* * **loose** использует простое регулярное выражение (просто проверяет наличие хотя бы одного символа @ и т. д.). Эта проверка слишком проста, и вместо нее рекомендуется использовать один из других режимов;  
* * **html5** использует регулярное выражение элемента ввода электронной почты HTML5, за исключением того, что оно требует присутствия tld.  
* * **html5-allow-no-tld** использует точно такое же регулярное выражение, что и элемент ввода электронной почты HTML5, что делает внутреннюю проверку согласованной с той, которую предоставляют браузеры.  
  *type:* **string**, *default:* **loose**

### GreaterThan

Проверяет, что значение больше, чем другое значение, определенное в параметрах. 
Чтобы заставить значение быть больше или равно другому значению, см. GreaterThanOrEqual. Чтобы заставить значение быть меньше другого значения, см. LessThan.

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

* **value** - Эта опция обязательна. Она определяет значение сравнения.
* **message** - Это сообщение будет показано, если значение не превышает значение сравнения.  
  *type:* **string**, *default:* **This value should be greater than {{ compared_value }}.**


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

* **value** - Эта опция обязательна. Она определяет значение сравнения.
* **message** - Это сообщение будет показано, если значение не превышает или равно значению сравнения.  
  *type:* **string**, *default:* **This value should be greater than or equal to {{ compared_value }}.**

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

* **value** - Эта опция обязательна. Она определяет значение сравнения.
* **message** - Это сообщение будет показано, если значение не меньше значения сравнения.  
  *type:* **string**, *default:* **This value should be less than {{ compared_value }}.**

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

* **value** - Эта опция обязательна. Она определяет значение сравнения.
* **message** - Это сообщение будет показано, если значение не меньше или равно значению сравнения.  
  *type:* **string**, *default:* **This value should be less than or equal to {{ compared_value }}.**

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

* **message** - Это сообщение отображается, если базовые данные не являются ложными.  
  *type:* **string**, *default:* **This value should be false.**

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

* **message** - Это сообщение отображается, если базовые данные не являются истинными.  
  *type:* **string**, *default:* **This value should be true.**

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

* **message** - Сообщение по умолчанию предоставляется, когда значение не меньше нуля.  
  *type:* **string**, *default:* **This value should be negative.**

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

* **message** - Сообщение по умолчанию, предоставляемое, когда значение не меньше или равно нулю.  
  *type:* **string**, *default:* **This value should be negative or zero.**

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

* **message** - Сообщение по умолчанию, предоставляемое, когда значение не больше нуля.  
  *type:* **string**, *default:* **This value should be positive.**

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

* **message** - Сообщение по умолчанию, предоставляемое, когда значение не больше или равно нулю.  
  *type:* **string**, *default:* **This value should be positive or zero.**

### NotNull

Проверяет, что значение не строго равно *null*. Чтобы убедиться, 
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

* **message** - Это сообщение будет показано, если значение равно *null*.  
  *type:* **string**, *default:* **This value should not be null.**

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

* **message** - Это сообщение будет отображаться, если значение пустое. 
  *type:* **string**, *default:* **This value should not be blank.**

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

* **message** - Это сообщение будет отображаться, если значение не попадает в заданный диапазон.
  *type:* **string**, *default:* **The value must lie in the range from {{ min }} to {{ max }}**
* **min** - Это обязательный параметр. Устанавливает нижнею границу диапазона.  
  *type:* **int, float**
* **max** - Это обязательный параметр. Устанавливает верхнюю границу диапазона.  
  *type:* **int, float**

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

* **message** -  
  *type:* **string**, *default:* **This value is not a valid URL.**
* **protocol** - Протоколы, которые считаются действительными для данного URL.
  Например, если вы также считаете действительными URL типа ftp://,
  переопределите массив протоколов, перечислив http, https, а также ftp.  
  *type:* **array**, *default:* **['http', 'https']**
* **relativeProtocol** - Если *true*, протокол считается необязательным при 
  проверке синтаксиса данного URL. Это означает, что и *http://*, и *https://* 
  являются допустимыми, а также относительными URL, не содержащими протокола 
  (например, *//example.com*).  
  *type:* **bool**, *default:* **false**
* **allowNull** - Флаг разрешающий **Nullable types**.  
    *type:* **bool**, *default:* **false**












