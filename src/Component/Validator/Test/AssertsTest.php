<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Test;

use Kaa\Component\Validator\Assert\All;
use Kaa\Component\Validator\Assert\Blank;
use Kaa\Component\Validator\Assert\Choice;
use Kaa\Component\Validator\Assert\DateRange;
use Kaa\Component\Validator\Assert\Email;
use Kaa\Component\Validator\Assert\GreaterThan;
use Kaa\Component\Validator\Assert\GreaterThanOrEqual;
use Kaa\Component\Validator\Assert\IsFalse;
use Kaa\Component\Validator\Assert\IsTrue;
use Kaa\Component\Validator\Assert\Length;
use Kaa\Component\Validator\Assert\LessThan;
use Kaa\Component\Validator\Assert\LessThanOrEqual;
use Kaa\Component\Validator\Assert\Negative;
use Kaa\Component\Validator\Assert\NegativeOrZero;
use Kaa\Component\Validator\Assert\NotBlank;
use Kaa\Component\Validator\Assert\NotNull;
use Kaa\Component\Validator\Assert\Positive;
use Kaa\Component\Validator\Assert\PositiveOrZero;
use Kaa\Component\Validator\Assert\Range;
use Kaa\Component\Validator\Assert\Url;
use Kaa\Component\Validator\Test\Models\TestModel;
use Kaa\Component\Validator\ValidatorLocator\AttributesParser;
use Kaa\Component\Validator\ValidatorLocator\ModelLocator;
use Twig;

trait AssertsTest
{
    private const NAME_MODEL = 'Kaa\Component\Validator\Test\Models\TestModel';

    private function getViolationsList($assert, $propertyName): array
    {
        $violationsList = [];
        $code = [];
        $model = $this->testModel;
        foreach ($this->attributes[self::NAME_MODEL] as $val) {
            if (get_class($val['attribute']) === $assert && $val['reflectionProperty']->getName() === $propertyName) {
                $generator = $val['attribute']->getGenerator();
                $code = $generator->generateAssert(
                    $val['attribute'],
                    $val['reflectionProperty'],
                    self::NAME_MODEL,
                    $this->twig,
                );
            }
        }

        eval($code);

        return $violationsList;
    }
}

uses(AssertsTest::class);

beforeEach(function () {
    $this->testModel = new TestModel();
    $this->config = [
        'scan' => [
            self::NAME_MODEL,
        ],
    ];

    $reflectionClasses = (new ModelLocator($this->config))->locate();
    $this->attributes = (new AttributesParser($reflectionClasses))->parseAttributes();

    $loader = new Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
    $this->twig = new Twig\Environment(
        $loader, [
            'autoescape' => false,
        ]
    );
});

describe('Blank', function () {
    it('returns true if the value is blank', function () {
        $violationsList = $this->getViolationsList(Blank::class, 'BlankTrue');
        expect($violationsList)->toHaveCount(0);
    });

    it('returns true if the value is not blank', function () {
        $violationsList = $this->getViolationsList(Blank::class, 'BlankFalse');
        expect($violationsList[0])
            ->getMessage()->toBe('This value should be blank.')
            ->and($violationsList)->toHaveCount(1);
    });
});

describe('GreaterThan', function () {
    it('returns true if the value is not greater', function () {
        $violationsList = $this->getViolationsList(GreaterThan::class, 'GreaterThanFalse');
        expect($violationsList[0])
            ->getMessage()->toBe('This value should be greater than 18.')
            ->and($violationsList)->toHaveCount(1);
    });

    it('returns true if the value is greater', function () {
        $violationsList = $this->getViolationsList(GreaterThan::class, 'GreaterThanTrue');
        expect($violationsList)->toHaveCount(0);
    });

    it('returns true if the value is not equal', function () {
        $violationsList = $this->getViolationsList(GreaterThan::class, 'GreaterThanEqualFalse');
        expect($violationsList[0])
            ->getMessage()->toBe('This value should be greater than 18.')
            ->and($violationsList)->toHaveCount(1);
    });
});

describe('GreaterThanOrEqual', function () {
    it('returns true if the value is not greater than or equal', function () {
        $violationsList = $this->getViolationsList(GreaterThanOrEqual::class, 'GreaterThanOrEqualFalse');
        expect($violationsList[0])
            ->getMessage()->toBe('This value should be greater than or equal to 6.')
            ->and($violationsList)->toHaveCount(1);
    });

    it('returns true if the value is greater than or equal', function () {
        $violationsList = $this->getViolationsList(GreaterThanOrEqual::class, 'GreaterThanOrEqualTrue');
        expect($violationsList)->toHaveCount(0);
    });
});

describe('IsFalse', function () {
    it('returns true if the value is false', function () {
        $violationsList = $this->getViolationsList(IsFalse::class, 'IsFalse');
        expect($violationsList[0])
            ->getMessage()->toBe('This value should be false.')
            ->and($violationsList)->toHaveCount(1);
    });
});

describe('IsTrue', function () {
    it('returns true if the value is true', function () {
        $violationsList = $this->getViolationsList(IsTrue::class, 'IsTrue');
        expect($violationsList[0])
            ->getMessage()->toBe('This value should be true.')
            ->and($violationsList)->toHaveCount(1);
    });
});

describe('LessThan', function () {
    it('returns true if the value is not less', function () {
        $violationsList = $this->getViolationsList(LessThan::class, 'LessThanFalse');
        expect($violationsList[0])
            ->getMessage()->toBe('This value should be less than 18.')
            ->and($violationsList)->toHaveCount(1);
    });

    it('returns true if the value is less', function () {
        $violationsList = $this->getViolationsList(LessThan::class, 'LessThanTrue');
        expect($violationsList)->toHaveCount(0);
    });

    it('returns true if the value is not equal', function () {
        $violationsList = $this->getViolationsList(LessThan::class, 'LessThanEqualFalse');
        expect($violationsList[0])
            ->getMessage()->toBe('This value should be less than 18.')
            ->and($violationsList)->toHaveCount(1);
    });
});

describe('LessThanOrEqual', function () {
    it('returns true if the value is not less than or equal', function () {
        $violationsList = $this->getViolationsList(LessThanOrEqual::class, 'LessThanOrEqualFalse');
        expect($violationsList[0])
            ->getMessage()->toBe('This value should be less than or equal to 6.')
            ->and($violationsList)->toHaveCount(1);
    });

    it('returns true if the value is less than or equal', function () {
        $violationsList = $this->getViolationsList(LessThanOrEqual::class, 'LessThanOrEqualTrue');
        expect($violationsList)->toHaveCount(0);
    });
});

describe('Email', function () {
    it('returns true if the value is not a valid email', function () {
        $violationsList = $this->getViolationsList(Email::class, 'EmailFalse');
        expect($violationsList[0])
            ->getMessage()->toBe('This value is not a valid email address.')
            ->and($violationsList)->toHaveCount(1);
    });

    it('returns true if the value is a valid email', function () {
        $violationsList = $this->getViolationsList(Email::class, 'EmailTrue');
        expect($violationsList)->toHaveCount(0);
    });
});

describe('Negative', function () {
    it('returns true if the value is not negative', function () {
        $violationsList = $this->getViolationsList(Negative::class, 'NegativeFalse');
        expect($violationsList[0])
            ->getMessage()->toBe('This value should be negative.')
            ->and($violationsList)->toHaveCount(1);
    });

    it('returns true if the value is negative', function () {
        $violationsList = $this->getViolationsList(Negative::class, 'NegativeTrue');
        expect($violationsList)->toHaveCount(0);
    });
});

describe('NegativeOrZero', function () {
    it('returns true if the value is negative or equal to zero', function () {
        $violationsList = $this->getViolationsList(NegativeOrZero::class, 'NegativeOrZeroTrue');
        expect($violationsList)->toHaveCount(0);
    });
});

describe('NotBlank', function () {
    it('returns true if the string is blank', function () {
        $violationsList = $this->getViolationsList(NotBlank::class, 'NotBlankStringFalse');
        expect($violationsList[0])
            ->getMessage()->toBe('This value should not be blank.')
            ->and($violationsList)->toHaveCount(1);
    });

    it('returns true if the string value is not blank', function () {
        $violationsList = $this->getViolationsList(NotBlank::class, 'NotBlankStringTrue');
        expect($violationsList)->toHaveCount(0);
    });

    it('returns true if the string value is null', function () {
        $violationsList = $this->getViolationsList(NotBlank::class, 'NotBlankStringAllowNullTrue');
        expect($violationsList)->toHaveCount(0);
    });

    it('returns true if the array value is blank', function () {
        $violationsList = $this->getViolationsList(NotBlank::class, 'NotBlankArrayFalse');
        expect($violationsList[0])
            ->getMessage()->toBe('This value should not be blank.')
            ->and($violationsList)->toHaveCount(1);
    });

    it('returns true if the array value is not blank', function () {
        $violationsList = $this->getViolationsList(NotBlank::class, 'NotBlankArrayTrue');
        expect($violationsList)->toHaveCount(0);
    });

    it('returns true if the array value is null', function () {
        $violationsList = $this->getViolationsList(NotBlank::class, 'NotBlankArrayAllowNullTrue');
        expect($violationsList)->toHaveCount(0);
    });

    it('returns true if the bool value is blank', function () {
        $violationsList = $this->getViolationsList(NotBlank::class, 'NotBlankBoolFalse');
        expect($violationsList[0])
            ->getMessage()->toBe('This value should not be blank.')
            ->and($violationsList)->toHaveCount(1);
    });

    it('returns true if the bool value is not blank', function () {
        $violationsList = $this->getViolationsList(NotBlank::class, 'NotBlankBoolTrue');
        expect($violationsList)->toHaveCount(0);
    });

    it('returns true if the bool value is null', function () {
        $violationsList = $this->getViolationsList(NotBlank::class, 'NotBlankBoolAllowNullTrue');
        expect($violationsList)->toHaveCount(0);
    });
});

describe('NotNull', function () {
    it('returns true if the value is null', function () {
        $violationsList = $this->getViolationsList(NotNull::class, 'NotNullFalse');
        expect($violationsList[0])
            ->getMessage()->toBe('This value should not be null.')
            ->and($violationsList)->toHaveCount(1);
    });

    it('returns true if the value is not equal to null', function () {
        $violationsList = $this->getViolationsList(NotNull::class, 'NotNullTrue');
        expect($violationsList)->toHaveCount(0);
    });
});

describe('Positive', function () {
    it('returns true if the value is not positive', function () {
        $violationsList = $this->getViolationsList(Positive::class, 'PositiveFalse');
        expect($violationsList[0])
            ->getMessage()->toBe('This value should be positive.')
            ->and($violationsList)->toHaveCount(1);
    });

    it('returns true if the value is positive', function () {
        $violationsList = $this->getViolationsList(Positive::class, 'PositiveTrue');
        expect($violationsList)->toHaveCount(0);
    });
});

describe('PositiveOrZero', function () {
    it('returns true if the value is positive or equal to zero', function () {
        $violationsList = $this->getViolationsList(PositiveOrZero::class, 'PositiveOrZeroTrue');
        expect($violationsList)->toHaveCount(0);
    });
});

describe('Range', function () {
    it('returns true if the value is not in the range', function () {
        $violationsList = $this->getViolationsList(Range::class, 'RangeFalse');
        expect($violationsList[0])
            ->getMessage()->toBe('The value must lie in the range from 3 to 10')
            ->and($violationsList)->toHaveCount(1);
    });

    it('returns true if the value is in the range', function () {
        $violationsList = $this->getViolationsList(Range::class, 'RangeTrue');
        expect($violationsList)->toHaveCount(0);
    });
});

describe('Choice', function () {
    it('returns true if no value is selected', function () {
        $violationsList = $this->getViolationsList(Choice::class, 'ChoiceFalse');
        expect($violationsList[0])
            ->getMessage()->toBe('The value you selected is not a valid choice.')
            ->and($violationsList)->toHaveCount(1);
    });

    it('returns true if the value is selected', function () {
        $violationsList = $this->getViolationsList(Choice::class, 'ChoiceTrue');
        expect($violationsList)->toHaveCount(0);
    });
});

describe('DateRange', function () {
    it('returns true if the value is in the range between two dates', function () {
        $violationsList = $this->getViolationsList(DateRange::class, 'DateRangeBetweenTrue');
        expect($violationsList)->toHaveCount(0);
    });

    it('returns true if the value is in the range after date', function () {
        $violationsList = $this->getViolationsList(DateRange::class, 'DateRangeAfterTrue');
        expect($violationsList)->toHaveCount(0);
    });

    it('returns true if the value is in the range before date', function () {
        $violationsList = $this->getViolationsList(DateRange::class, 'DateRangeBeforeTrue');
        expect($violationsList)->toHaveCount(0);
    });

    it('returns true if the value is in the range not between two dates', function () {
        $violationsList = $this->getViolationsList(DateRange::class, 'DateRangeBetweenFalse');
        expect($violationsList[0])
            ->getMessage()->toBe('This value should be between 2020-12-10 and 2023-09-15.')
            ->and($violationsList)->toHaveCount(1);
    });

    it('returns true if the value is in the range not after date', function () {
        $violationsList = $this->getViolationsList(DateRange::class, 'DateRangeAfterFalse');
        expect($violationsList[0])
            ->getMessage()->toBe('This value should be after 2020-12-10.')
            ->and($violationsList)->toHaveCount(1);
    });

    it('returns true if the value is in the range not before date', function () {
        $violationsList = $this->getViolationsList(DateRange::class, 'DateRangeBeforeFalse');
        expect($violationsList[0])
            ->getMessage()->toBe('This value should be before 2023-10-10.')
            ->and($violationsList)->toHaveCount(1);
    });
});

describe('Length', function () {
    it('returns true if the value has the correct length max', function () {
        $violationsList = $this->getViolationsList(Length::class, 'LengthMaxTrue');
        expect($violationsList)->toHaveCount(0);
    });

    it('returns true if the value has not the correct length max', function () {
        $violationsList = $this->getViolationsList(Length::class, 'LengthMaxFalse');
        expect($violationsList[0])
            ->getMessage()->toBe('This value is too long. It should have 2 characters or less.')
            ->and($violationsList)->toHaveCount(1);
    });

    it('returns true if the value has the correct length min', function () {
        $violationsList = $this->getViolationsList(Length::class, 'LengthMinTrue');
        expect($violationsList)->toHaveCount(0);
    });

    it('returns true if the value has not the correct length min', function () {
        $violationsList = $this->getViolationsList(Length::class, 'LengthMinFalse');
        expect($violationsList[0])
            ->getMessage()->toBe('This value is too short. It should have 5 characters or more.')
            ->and($violationsList)->toHaveCount(1);
    });

    it('returns true if the value has the correct length exactly', function () {
        $violationsList = $this->getViolationsList(Length::class, 'LengthExactlyTrue');
        expect($violationsList)->toHaveCount(0);
    });

    it('returns true if the value has not the correct length exactly', function () {
        $violationsList = $this->getViolationsList(Length::class, 'LengthExactlyFalse');
        expect($violationsList[0])
            ->getMessage()->toBe('This value should have exactly 2 characters')
            ->and($violationsList)->toHaveCount(1);
    });
});

describe('Url', function () {
    it('return true if the value is a valid url', function () {
        $violationsList = $this->getViolationsList(Url::class, 'UrlTrue');
        expect($violationsList)->toHaveCount(0);
    });

    it('return true if the value is not a valid url', function () {
        $violationsList = $this->getViolationsList(Url::class, 'UrlFalse');
        expect($violationsList[0])
            ->getMessage()->toBe('This value is not a valid URL.')
            ->and($violationsList)->toHaveCount(1);
    });
});

describe('All', function () {
    it('return true if the array of values is all blank', function () {
        $violationsList = $this->getViolationsList(All::class, 'AllBlankTrue');
        expect($violationsList)->toHaveCount(0);
    });

    it('return false if the array of values is any not blank', function () {
        $violationsList = $this->getViolationsList(All::class, 'AllBlankFalse');
        expect($violationsList[0])
            ->getMessage()->toBe('This value should be positive.')
            ->and($violationsList)->toHaveCount(1);
    });

    it('return true if the value is a valid url', function () {
        $violationsList = $this->getViolationsList(All::class, 'AllUrlTrue');
        expect($violationsList)->toHaveCount(0);
    });

    it('return true if the value is not a valid url', function () {
        $violationsList = $this->getViolationsList(All::class, 'AllUrlFalse');
        expect($violationsList[0])
            ->getMessage()->toBe('This value is not a valid URL.')
            ->and($violationsList)->toHaveCount(1);
    });

    it('returns true if the value has the correct length max', function () {
        $violationsList = $this->getViolationsList(All::class, 'AllLengthMaxTrue');
        expect($violationsList)->toHaveCount(0);
    });

    it('returns true if the value is in the range between two dates', function () {
        $violationsList = $this->getViolationsList(All::class, 'AllDateRangeBetweenTrue');
        expect($violationsList)->toHaveCount(0);
    });

    it('returns true if the value is in the range after date', function () {
        $violationsList = $this->getViolationsList(All::class, 'AllDateRangeAfterTrue');
        expect($violationsList)->toHaveCount(0);
    });

    it('returns true if the value is in the range before date', function () {
        $violationsList = $this->getViolationsList(All::class, 'AllDateRangeBeforeTrue');
        expect($violationsList)->toHaveCount(0);
    });

    it('returns true if the value is selected', function () {
        $violationsList = $this->getViolationsList(All::class, 'AllChoiceTrue');
        expect($violationsList)->toHaveCount(0);
    });

    it('returns true if the value is in the range', function () {
        $violationsList = $this->getViolationsList(All::class, 'AllRangeTrue');
        expect($violationsList)->toHaveCount(0);
    });

    it('returns true if the value is positive', function () {
        $violationsList = $this->getViolationsList(All::class, 'AllPositiveTrue');
        expect($violationsList)->toHaveCount(0);
    });

    it('returns true if the value is not equal to null', function () {
        $violationsList = $this->getViolationsList(All::class, 'AllNotNullTrue');
        expect($violationsList)->toHaveCount(0);
    });

    it('returns true if the value is not blank', function () {
        $violationsList = $this->getViolationsList(All::class, 'AllNotBlankTrue');
        expect($violationsList)->toHaveCount(0);
    });

    it('returns true if the value is negative', function () {
        $violationsList = $this->getViolationsList(All::class, 'AllNegativeTrue');
        expect($violationsList)->toHaveCount(0);
    });

    it('returns true if the value is a valid email', function () {
        $violationsList = $this->getViolationsList(All::class, 'AllEmailTrue');
        expect($violationsList)->toHaveCount(0);
    });

    it('returns true if the value is less', function () {
        $violationsList = $this->getViolationsList(All::class, 'AllLessThanTrue');
        expect($violationsList)->toHaveCount(0);
    });

    it('returns true if the value is false', function () {
        $violationsList = $this->getViolationsList(All::class, 'AllIsFalse');
        expect($violationsList[0])
            ->getMessage()->toBe('This value should be false.')
            ->and($violationsList)->toHaveCount(1);
    });

    it('returns true if the value is true', function () {
        $violationsList = $this->getViolationsList(All::class, 'AllIsTrue');
        expect($violationsList[0])
            ->getMessage()->toBe('This value should be true.')
            ->and($violationsList)->toHaveCount(1);
    });

    it('returns true if the value is greater', function () {
        $violationsList = $this->getViolationsList(GreaterThan::class, 'GreaterThanTrue');
        expect($violationsList)->toHaveCount(0);
    });
});
