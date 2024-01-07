# Dependency Injection

### Содержание

* [Введение](#введение)
* [Использование генератора контейнера](#использование-генератора-контейнера)
* [Конфигурация контейнера](#конфигурация-контейнера)
* [Конфигурация через атрибуты](#конфигурация-через-атрибуты)

### Введение

`Dependency Injection` позволяет легко справиться с зависимостями ваших классов и избавиться от boilerplate-кода
создания объектов.

### Использование генератора контейнера

`Dependency Injection` использует генерацию кода для создания контейнера, позволяющего получать объекты сервисов.
Контейнер реализует интерфейс `ContainerInterface` и после генерации будет
называться `\Kaa\Generated\DependencyInjection\Container`.

```php
<?php

interface ContainerInterface
{
    /**
     * @kphp-generic T
     * @param class-string<T> $class
     * @return T
     */
    public static function get(string $nameOrAlias, string $class): object;
}
```

Пример генерации контейнера:

```php
<?php

$sharedConfig = new SharedConfig('./generated');
$config = [
    'scan' => [
      'App\\Service',
      'App\\EventListener',
    ],
    
    'ignore' => [
      'App\\Service\\NotAService',
    ],  
    
    'parameters' => [
        'app.some_parameter' => 'my_value',
    ],
    
    'services' => [
        'app.manual_service_name' => [
            'class' => MyService::class,
            'arguments' => [
                'someService' => '@app.other_service',
                'someValue' => '%app.some_parameter'
            ]
        ],
        
        OtherService::class => [
            'singleton' => false,
            'factory' => [
                'service' => FactoryService::class,
                'method' => 'createOtherService',
                'static' => false,
            ]
        ]   
    ],
    
    'aliases' => [
        'app.other_service' => OtherService::class,
    ],
];

$containerGenerator = new ContainerGenerator();
$containerGenerator->generate($sharedConfig, $config);

// Теперь можно использовать контейнер
$service = \Kaa\Generated\DependencyInjection\Container::get(OtherService::class, OtherService::class);
```

### Конфигурация контейнера

* `scan` - в данном поле необходимо указать пространства имен, для классов в которых нужно создать правила
  валидации.
* `ignore` - здесь указываются пространства имен или классы, которые нужно игнорировать при генерации валидатора.
* `parameters` - параметры, которые потом можно будет использовать в сервисах.
* `services` - определение сервисов. Она полностью переопределяет конфигурацию из атрибутов, если имя сервиса совпадает с именем, указанным через атрибуты.
    * `class` - Класс сервиса
    * `signleton` - если установить `false`, то при каждом запросе сервиса будет создаваться новый экземпляр этого сервиса.
    * `arguments` - аргументы, которые надо передать в конструктор класса.
      Если аргумент начинается с `@`, то `Kaa`, будет искать сервис с таким названием.
      Если аргумент заключён в символы `%`, то `Kaa` будет искать параметр с таким названием.
      Если какие-то параметры конструктора здесь не указаны, то `Kaa` будет пытаться найти их самостоятельно, `!`но всё
      ещё игнорируя атрибуты`!`.
    * `factory` - фабрика для создания сервиса. Класс сервиса будет равен типу возвращаемого значения фабрики.
        * `service` - имя сервиса, если фабрика не статическая, имя класса, если фабрика статическая
        * `method` - имя метода, который нужно вызвать, чтобы получить объект сервиса, по умолчанию `invoke` (без кавычек).
        * `static` - статическая ли фабрика.
* `aliases` - псевдонимы сервисов: ключ - название псевдонима, значение - сервис, на который он указывает.

### Конфигурация через атрибуты

Тот же конфиг можно получить с помощью атрибутов:
```php
#[Service(name: 'app.manual_service_name')]
class MyService 
{
    public function __construct(
        #[Autwore('app.other_service')]
        SomeService $someService,
        
        #[Autowire(parameter: 'app.some_parameter')]
        string $someValue,
        
        OneMoreService $oneMoreService, 
    ) {
    }
}

#[Service(singleton: false)]
#[Factory(FactoryService::class, 'createOtherService', static: false)]
class OtherService {
}
```
