<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Test;

use Exception;
use Kaa\Component\Generator\SharedConfig;
use Kaa\Component\Validator\Assert\Blank;
use Kaa\Component\Validator\Assert\Email;
use Kaa\Component\Validator\Assert\GreaterThan;
use Kaa\Component\Validator\Assert\GreaterThanOrEqual;
use Kaa\Component\Validator\Assert\IsFalse;
use Kaa\Component\Validator\Assert\IsTrue;
use Kaa\Component\Validator\Assert\LessThan;
use Kaa\Component\Validator\Assert\LessThanOrEqual;
use Kaa\Component\Validator\Assert\Negative;
use Kaa\Component\Validator\Assert\NegativeOrZero;
use Kaa\Component\Validator\Assert\NotBlank;
use Kaa\Component\Validator\Assert\NotNull;
use Kaa\Component\Validator\Assert\Positive;
use Kaa\Component\Validator\Assert\PositiveOrZero;
use Kaa\Component\Validator\Assert\Range;
use Kaa\Component\Validator\Exception\ValidatorGeneratorException;
use Kaa\Component\Validator\Test\Models\TestModel;
use Kaa\Component\Validator\ValidatorLocator\AttributesParser;
use Kaa\Component\Validator\ValidatorLocator\ModelLocator;
use PHPUnit\Framework\TestCase;
use Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;

class AssertsTest extends TestCase
{
    private const NAME_MODEL = 'Kaa\Component\Validator\Test\Models\TestModel';
    private Twig\Environment $twig;

    /**
     * @throws SyntaxError|RuntimeError|ValidatorGeneratorException|LoaderError|Exception
     */
    protected function setUp(): void
    {
        $this->testModel = new TestModel();
        $this->sharedConfig = new SharedConfig('/home/azly/KaaFramework2/kaa/src/Component/Validator/Test/generated');
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
    }

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

    public function testAssertBlankFalse(): void
    {
        $violationsList = $this->getViolationsList(Blank::class, 'BlankFalse');
        assertCount(1, $violationsList);
        assertEquals('This value should be blank.', $violationsList[0]->getMessage());
    }

    public function testAssertBlankTrue(): void
    {
        $violationsList = $this->getViolationsList(Blank::class, 'BlankTrue');
        assertCount(0, $violationsList);
    }

    public function testGreaterThanFalse(): void
    {
        $violationsList = $this->getViolationsList(GreaterThan::class, 'GreaterThanFalse');
        assertCount(1, $violationsList);
        assertEquals('This value should be greater than 18.', $violationsList[0]->getMessage());
    }

    public function testGreaterThanTrue(): void
    {
        $violationsList = $this->getViolationsList(GreaterThan::class, 'GreaterThanTrue');
        assertCount(0, $violationsList);
    }

    public function testGreaterThanEqualFalse(): void
    {
        $violationsList = $this->getViolationsList(GreaterThan::class, 'GreaterThanEqualFalse');
        assertCount(1, $violationsList);
        assertEquals('This value should be greater than 18.', $violationsList[0]->getMessage());
    }

    public function testGreaterThanOrEqualFalse(): void
    {
        $violationsList = $this->getViolationsList(GreaterThanOrEqual::class, 'GreaterThanOrEqualFalse');
        assertCount(1, $violationsList);
        assertEquals('This value should be greater than or equal to 6.', $violationsList[0]->getMessage());
    }

    public function testGreaterThanOrEqualTrue(): void
    {
        $violationsList = $this->getViolationsList(GreaterThanOrEqual::class, 'GreaterThanOrEqualTrue');
        assertCount(0, $violationsList);
    }

    public function testIsFalse(): void
    {
        $violationsList = $this->getViolationsList(IsFalse::class, 'IsFalse');
        assertCount(1, $violationsList);
        assertEquals('This value should be false.', $violationsList[0]->getMessage());
    }

    public function testIsTrue(): void
    {
        $violationsList = $this->getViolationsList(IsTrue::class, 'IsTrue');
        assertCount(1, $violationsList);
        assertEquals('This value should be true.', $violationsList[0]->getMessage());
    }

    public function testLessThanFalse(): void
    {
        $violationsList = $this->getViolationsList(LessThan::class, 'LessThanFalse');
        assertCount(1, $violationsList);
        assertEquals('This value should be less than 18.', $violationsList[0]->getMessage());
    }

    public function testLessThanTrue(): void
    {
        $violationsList = $this->getViolationsList(LessThan::class, 'LessThanTrue');
        assertCount(0, $violationsList);
    }

    public function testLessThanEqualFalse(): void
    {
        $violationsList = $this->getViolationsList(LessThan::class, 'LessThanEqualFalse');
        assertCount(1, $violationsList);
        assertEquals('This value should be less than 18.', $violationsList[0]->getMessage());
    }

    public function testLessThanOrEqualFalse(): void
    {
        $violationsList = $this->getViolationsList(LessThanOrEqual::class, 'LessThanOrEqualFalse');
        assertCount(1, $violationsList);
        assertEquals('This value should be less than or equal to 6.', $violationsList[0]->getMessage());
    }

    public function testLessThanOrEqualTrue(): void
    {
        $violationsList = $this->getViolationsList(LessThanOrEqual::class, 'LessThanOrEqualTrue');
        assertCount(0, $violationsList);
    }

    public function testEmailTrue(): void
    {
        $violationsList = $this->getViolationsList(Email::class, 'EmailTrue');
        assertCount(0, $violationsList);
    }

    public function testEmailFalse(): void
    {
        $violationsList = $this->getViolationsList(Email::class, 'EmailFalse');
        assertCount(1, $violationsList);
        assertEquals('This value is not a valid email address.', $violationsList[0]->getMessage());
    }

    public function testNegativeTrue(): void
    {
        $violationsList = $this->getViolationsList(Negative::class, 'NegativeTrue');
        assertCount(0, $violationsList);
    }

    public function testNegativeFalse(): void
    {
        $violationsList = $this->getViolationsList(Negative::class, 'NegativeFalse');
        assertCount(1, $violationsList);
        assertEquals('This value should be negative.', $violationsList[0]->getMessage());
    }

    public function testNegativeOrZeroTrue(): void
    {
        $violationsList = $this->getViolationsList(NegativeOrZero::class, 'NegativeOrZeroTrue');
        assertCount(0, $violationsList);
    }

    public function testNotBlankTrue(): void
    {
        $violationsList = $this->getViolationsList(NotBlank::class, 'NotBlankTrue');
        assertCount(0, $violationsList);
    }

    public function testNotBlankFalse(): void
    {
        $violationsList = $this->getViolationsList(NotBlank::class, 'NotBlankFalse');
        assertCount(1, $violationsList);
        assertEquals('This value should not be blank.', $violationsList[0]->getMessage());
    }

    public function testNotNullTrue(): void
    {
        $violationsList = $this->getViolationsList(NotNull::class, 'NotNullTrue');
        assertCount(0, $violationsList);
    }

    public function testNotNullFalse(): void
    {
        $violationsList = $this->getViolationsList(NotNull::class, 'NotNullFalse');
        assertCount(1, $violationsList);
        assertEquals('This value should not be null.', $violationsList[0]->getMessage());
    }

    public function testPositiveTrue(): void
    {
        $violationsList = $this->getViolationsList(Positive::class, 'PositiveTrue');
        assertCount(0, $violationsList);
    }

    public function testPositiveFalse(): void
    {
        $violationsList = $this->getViolationsList(Positive::class, 'PositiveFalse');
        assertCount(1, $violationsList);
        assertEquals('This value should be positive.', $violationsList[0]->getMessage());
    }

    public function testPositiveOrZeroTrue(): void
    {
        $violationsList = $this->getViolationsList(PositiveOrZero::class, 'PositiveOrZeroTrue');
        assertCount(0, $violationsList);
    }

    public function testRangeTrue(): void
    {
        $violationsList = $this->getViolationsList(Range::class, 'RangeTrue');
        assertCount(0, $violationsList);
    }

    public function testRangeFalse(): void
    {
        $violationsList = $this->getViolationsList(Range::class, 'RangeFalse');
        assertCount(1, $violationsList);
        assertEquals(
            'The value must lie in the range from 3 to 10',
            $violationsList[0]->getMessage(),
        );
    }
}
