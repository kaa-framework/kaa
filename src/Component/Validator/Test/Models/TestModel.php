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
    public string $NotBlankTrue = 'str';

    #[Assert\NotBlank]
    public string $NotBlankFalse = '';

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
}
