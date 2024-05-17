<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Test\Models;

use DateTime;
use Kaa\Component\Validator\Assert;

class TestModel
{
    #[Assert\GreaterThan(18)]
    public int $GreaterThanFalse = 15;

    #[Assert\GreaterThan(18)]
    public int $GreaterThanTrue = 19;

    #[Assert\GreaterThan(18)]
    public int $GreaterThanEqualFalse = 18;

    #[Assert\Blank]
    public string $BlankTrue = '';

    #[Assert\Blank]
    public string $BlankFalse = '123';

    #[Assert\GreaterThanOrEqual(6)]
    public int $GreaterThanOrEqualFalse = 5;

    #[Assert\GreaterThanOrEqual(5)]
    public int $GreaterThanOrEqualTrue = 5;

    #[Assert\IsFalse]
    public bool $IsFalse = true;

    #[Assert\IsTrue]
    public bool $IsTrue = false;

    #[Assert\LessThan(18)]
    public int $LessThanFalse = 20;

    #[Assert\LessThan(18)]
    public int $LessThanTrue = 15;

    #[Assert\LessThan(18)]
    public int $LessThanEqualFalse = 18;

    #[Assert\LessThanOrEqual(6)]
    public int $LessThanOrEqualFalse = 7;

    #[Assert\LessThanOrEqual(5)]
    public int $LessThanOrEqualTrue = 5;

    #[Assert\Email]
    public string $EmailTrue = 'example@google.com';

    #[Assert\Email]
    public string $EmailFalse = 'examplegooglecom';

    #[Assert\Negative]
    public int $NegativeTrue = -5;

    #[Assert\Negative]
    public int $NegativeFalse = 5;

    #[Assert\NegativeOrZero]
    public int $NegativeOrZeroTrue = 0;

    #[Assert\NotBlank]
    public string $NotBlankStringTrue = 'str';

    #[Assert\NotBlank]
    public string $NotBlankStringFalse = '';

    #[Assert\NotBlank(
        allowNull: true
    )]
    public ?string $NotBlankStringAllowNullTrue = null;

    #[Assert\NotBlank]
    public array $NotBlankArrayTrue = ['str'];

    #[Assert\NotBlank]
    public array $NotBlankArrayFalse = [];

    #[Assert\NotBlank(
        allowNull: true
    )]
    public ?string $NotBlankArrayAllowNullTrue = null;

    #[Assert\NotBlank]
    public bool $NotBlankBoolTrue = true;

    #[Assert\NotBlank]
    public bool $NotBlankBoolFalse = false;

    #[Assert\NotBlank(
        allowNull: true
    )]
    public ?bool $NotBlankBoolAllowNullTrue = null;

    #[Assert\NotNull]
    public ?int $NotNullTrue = 5;

    #[Assert\NotNull]
    public ?int $NotNullFalse = null;

    #[Assert\Positive]
    public int $PositiveTrue = 5;

    #[Assert\Positive]
    public int $PositiveFalse = -5;

    #[Assert\PositiveOrZero]
    public int $PositiveOrZeroTrue = 0;

    #[Assert\Range(
        min: 3,
        max: 10,
    )]
    public int $RangeTrue = 5;

    #[Assert\Range(
        min: 3,
        max: 10,
    )]
    public int $RangeFalse = 13;

    #[Assert\Choice([10, '123'])]
    public int $ChoiceTrue = 10;

    #[Assert\Choice([10, '123'])]
    public string $ChoiceFalse = 'a';

    #[Assert\DateRange(
        format: 'Y-m-d',
        before: new DateTime('2023-09-15'),
        after: new DateTime('2020-12-10'),
    )]
    public string $DateRangeBetweenTrue = '2023-09-10';

    #[Assert\DateRange(
        format: 'Y-m-d',
        after: new DateTime('2020-12-10'),
    )]
    public string $DateRangeAfterTrue = '2023-09-10';

    #[Assert\DateRange(
        format: 'Y-m-d',
        before: new DateTime('2023-10-10'),
    )]
    public string $DateRangeBeforeTrue = '2023-09-10';

    #[Assert\DateRange(
        format: 'Y-m-d',
        before: new DateTime('2023-09-15'),
        after: new DateTime('2020-12-10'),
    )]
    public string $DateRangeBetweenFalse = '2020-12-09';

    #[Assert\DateRange(
        format: 'Y-m-d',
        after: new DateTime('2020-12-10'),
    )]
    public string $DateRangeAfterFalse = '2019-02-19';

    #[Assert\DateRange(
        format: 'Y-m-d',
        before: new DateTime('2023-10-10'),
    )]
    public string $DateRangeBeforeFalse = '2023-11-10';

    #[Assert\Length(
        max: 10,
    )]
    public string $LengthMaxTrue = '123';

    #[Assert\Length(
        max: 2,
    )]
    public string $LengthMaxFalse = '123';

    #[Assert\Length(
        min: 2,
    )]
    public string $LengthMinTrue = '123';

    #[Assert\Length(
        min: 5,
    )]
    public string $LengthMinFalse = '123';

    #[Assert\Length(
        exactly: 3,
    )]
    public string $LengthExactlyTrue = '123';

    #[Assert\Length(
        exactly: 2,
    )]
    public string $LengthExactlyFalse = '123';

    #[Assert\Url]
    public string $UrlTrue = 'https://google.com';

    #[Assert\Url]
    public string $UrlFalse = 'https://google,com';

    #[Assert\All([
        new Assert\GreaterThan(18)
    ])]
    public array $AllGreaterThanTrue = [19];

    #[Assert\All([
        new Assert\IsFalse()
    ])]
    public array $AllIsFalse = [true];

    #[Assert\All([
        new Assert\IsTrue()
    ])]
    public array $AllIsTrue = [false];

    #[Assert\All([
        new Assert\LessThan(18)
    ])]
    public array $AllLessThanTrue = [15];

    #[Assert\All([
        new Assert\Email()
    ])]
    public array $AllEmailTrue = ['example@google.com'];

    #[Assert\All([
        new Assert\Negative()
    ])]
    public array $AllNegativeTrue = [-5];

    #[Assert\All([
        new Assert\NotBlank()
    ])]
    public array $AllNotBlankTrue = ['str'];

    #[Assert\All([
        new Assert\NotNull()
    ])]
    public ?array $AllNotNullTrue = [5];

    #[Assert\All([
        new Assert\Positive()
    ])]
    public array $AllPositiveTrue = [5];

    #[Assert\All([
        new Assert\Range(
            min: 3,
            max: 10,
        )
    ])]
    public array $AllRangeTrue = [5];

    #[Assert\All([
        new Assert\Choice([10, '123'])
    ])]
    public array $AllChoiceTrue = [10];

    #[Assert\All([
        new Assert\DateRange(
            format: 'Y-m-d',
            before: new DateTime('2023-09-15'),
            after: new DateTime('2020-12-10'),
        )
    ])]
    public array $AllDateRangeBetweenTrue = ['2023-09-10'];

    #[Assert\All([
        new Assert\DateRange(
            format: 'Y-m-d',
            after: new DateTime('2020-12-10'),
        )
    ])]
    public array $AllDateRangeAfterTrue = ['2023-09-10'];

    #[Assert\All([
        new Assert\DateRange(
            format: 'Y-m-d',
            before: new DateTime('2023-10-10'),
        )
    ])]
    public array $AllDateRangeBeforeTrue = ['2023-09-10'];

    #[Assert\All([
        new Assert\Length(
            max: 10,
        )
    ])]
    public array $AllLengthMaxTrue = ['123'];

    #[Assert\All([
        new Assert\Url(),
    ])]
    public array $AllUrlTrue = ['https://google.com'];

    #[Assert\All([
        new Assert\Url(),
    ])]
    public array $AllUrlFalse = ['https://google,com'];

    #[Assert\All([
        new Assert\Blank()
    ])]
    public array $AllBlankTrue = ['', null];

    #[Assert\All([
        new Assert\NotBlank(),
        new Assert\Positive()
    ])]
    public array $AllBlankFalse = [1, -5];
}
