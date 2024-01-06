<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Test\GeneratedTest\Validator;

class Validator implements \Kaa\Component\Validator\ValidatorInterface
{
    private function validate_Kaa_Component_Validator_Test_Models_TestModel(object $model): array
    {
        $violationsList = [];
        if ($model->GreaterThanFalse <= 18) {
            $violationsList[] = new \Kaa\Component\Validator\Violation('Kaa\Component\Validator\Test\Models\TestModel', 'GreaterThanFalse', 'This value should be greater than 18.');
        }
        if ($model->GreaterThanTrue <= 18) {
            $violationsList[] = new \Kaa\Component\Validator\Violation('Kaa\Component\Validator\Test\Models\TestModel', 'GreaterThanTrue', 'This value should be greater than 18.');
        }
        if ($model->GreaterThanEqualFalse <= 18) {
            $violationsList[] = new \Kaa\Component\Validator\Violation('Kaa\Component\Validator\Test\Models\TestModel', 'GreaterThanEqualFalse', 'This value should be greater than 18.');
        }
        if ($model->BlankTrue !== '' && $model->BlankTrue !== null) {
            $violationsList[] = new \Kaa\Component\Validator\Violation('Kaa\Component\Validator\Test\Models\TestModel', 'BlankTrue', 'This value should be blank.');
        }

        if ($model->BlankFalse !== '' && $model->BlankFalse !== null) {
            $violationsList[] = new \Kaa\Component\Validator\Violation('Kaa\Component\Validator\Test\Models\TestModel', 'BlankFalse', 'This value should be blank.');
        }

        if ($model->GreaterThanOrEqualFalse < 6) {
            $violationsList[] = new \Kaa\Component\Validator\Violation('Kaa\Component\Validator\Test\Models\TestModel', 'GreaterThanOrEqualFalse', 'This value should be greater than or equal to 6.');
        }
        if ($model->GreaterThanOrEqualTrue < 5) {
            $violationsList[] = new \Kaa\Component\Validator\Violation('Kaa\Component\Validator\Test\Models\TestModel', 'GreaterThanOrEqualTrue', 'This value should be greater than or equal to 5.');
        }
        if ($model->IsFalse !== false) {
            $violationsList[] = new \Kaa\Component\Validator\Violation('Kaa\Component\Validator\Test\Models\TestModel', 'IsFalse', 'This value should be false.');
        }
        if ($model->IsTrue !== true) {
            $violationsList[] = new \Kaa\Component\Validator\Violation('Kaa\Component\Validator\Test\Models\TestModel', 'IsTrue', 'This value should be true.');
        }
        if ($model->LessThanFalse >= 18) {
            $violationsList[] = new \Kaa\Component\Validator\Violation('Kaa\Component\Validator\Test\Models\TestModel', 'LessThanFalse', 'This value should be less than 18.');
        }
        if ($model->LessThanTrue >= 18) {
            $violationsList[] = new \Kaa\Component\Validator\Violation('Kaa\Component\Validator\Test\Models\TestModel', 'LessThanTrue', 'This value should be less than 18.');
        }
        if ($model->LessThanEqualFalse >= 18) {
            $violationsList[] = new \Kaa\Component\Validator\Violation('Kaa\Component\Validator\Test\Models\TestModel', 'LessThanEqualFalse', 'This value should be less than 18.');
        }
        if ($model->LessThanOrEqualFalse > 6) {
            $violationsList[] = new \Kaa\Component\Validator\Violation('Kaa\Component\Validator\Test\Models\TestModel', 'LessThanOrEqualFalse', 'This value should be less than or equal to 6.');
        }
        if ($model->LessThanOrEqualTrue > 5) {
            $violationsList[] = new \Kaa\Component\Validator\Violation('Kaa\Component\Validator\Test\Models\TestModel', 'LessThanOrEqualTrue', 'This value should be less than or equal to 5.');
        }
        if (!preg_match("/^.+\@\S+\.\S+$/", $model->EmailTrue)) {
            $violationsList[] = new \Kaa\Component\Validator\Violation('Kaa\Component\Validator\Test\Models\TestModel', 'EmailTrue', 'This value is not a valid email address.');
        }
        if (!preg_match("/^.+\@\S+\.\S+$/", $model->EmailFalse)) {
            $violationsList[] = new \Kaa\Component\Validator\Violation('Kaa\Component\Validator\Test\Models\TestModel', 'EmailFalse', 'This value is not a valid email address.');
        }
        if ($model->NegativeTrue >= 0) {
            $violationsList[] = new \Kaa\Component\Validator\Violation('Kaa\Component\Validator\Test\Models\TestModel', 'NegativeTrue', 'This value should be negative.');
        }
        if ($model->NegativeFalse >= 0) {
            $violationsList[] = new \Kaa\Component\Validator\Violation('Kaa\Component\Validator\Test\Models\TestModel', 'NegativeFalse', 'This value should be negative.');
        }
        if ($model->NegativeOrZeroTrue > 0) {
            $violationsList[] = new \Kaa\Component\Validator\Violation('Kaa\Component\Validator\Test\Models\TestModel', 'NegativeOrZeroTrue', 'This value should be negative or zero.');
        }
        if ($model->NotBlankTrue === false || (empty($model->NotBlankTrue) && $model->NotBlankTrue !== '0')) {
            $violationsList[] = new \Kaa\Component\Validator\Violation('Kaa\Component\Validator\Test\Models\TestModel', 'NotBlankTrue', 'This value should not be blank.');
        }
        if ($model->NotBlankFalse === false || (empty($model->NotBlankFalse) && $model->NotBlankFalse !== '0')) {
            $violationsList[] = new \Kaa\Component\Validator\Violation('Kaa\Component\Validator\Test\Models\TestModel', 'NotBlankFalse', 'This value should not be blank.');
        }
        if ($model->NotNullTrue === null) {
            $violationsList[] = new \Kaa\Component\Validator\Violation('Kaa\Component\Validator\Test\Models\TestModel', 'NotNullTrue', 'This value should not be null.');
        }
        if ($model->NotNullFalse === null) {
            $violationsList[] = new \Kaa\Component\Validator\Violation('Kaa\Component\Validator\Test\Models\TestModel', 'NotNullFalse', 'This value should not be null.');
        }
        if ($model->PositiveTrue <= 0) {
            $violationsList[] = new \Kaa\Component\Validator\Violation('Kaa\Component\Validator\Test\Models\TestModel', 'PositiveTrue', 'This value should be positive.');
        }
        if ($model->PositiveFalse <= 0) {
            $violationsList[] = new \Kaa\Component\Validator\Violation('Kaa\Component\Validator\Test\Models\TestModel', 'PositiveFalse', 'This value should be positive.');
        }
        if ($model->PositiveOrZeroTrue < 0) {
            $violationsList[] = new \Kaa\Component\Validator\Violation('Kaa\Component\Validator\Test\Models\TestModel', 'PositiveOrZeroTrue', 'This value should be positive or zero.');
        }
        if ($model->RangeTrue < 3 || $model->RangeTrue > 10) {
            $violationsList[] = new \Kaa\Component\Validator\Violation('Kaa\Component\Validator\Test\Models\TestModel', 'RangeTrue', 'The value must lie in the range from 3 to 10');
        }
        if ($model->RangeFalse < 3 || $model->RangeFalse > 10) {
            $violationsList[] = new \Kaa\Component\Validator\Violation('Kaa\Component\Validator\Test\Models\TestModel', 'RangeFalse', 'The value must lie in the range from 3 to 10');
        }

        return $violationsList;
    }

    private function validate_Kaa_Component_Validator_Test_Models_SomeModel(object $model): array
    {
        $violationsList = [];
        if ($model->text !== '' && $model->text !== null) {
            $violationsList[] = new \Kaa\Component\Validator\Violation('Kaa\Component\Validator\Test\Models\SomeModel', 'text', 'This value should be blank.');
        }

        return $violationsList;
    }

    /**
     * @return \Kaa\Component\Validator\Violation[]
     */
    public function validate(object $model): array
    {
        $violationsList = [];
        switch (get_class($model)) {
            case \Kaa\Component\Validator\Test\Models\TestModel::class:
                $violationsList = $this->validate_Kaa_Component_Validator_Test_Models_TestModel($model);

                break;

            case \Kaa\Component\Validator\Test\Models\SomeModel::class:
                $violationsList = $this->validate_Kaa_Component_Validator_Test_Models_SomeModel($model);

                break;
        }

        return $violationsList;
    }
}
