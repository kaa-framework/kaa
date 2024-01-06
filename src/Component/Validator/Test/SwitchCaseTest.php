<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Test;

use Kaa\Component\Validator\Test\GeneratedTest\Validator\Validator;
use Kaa\Component\Validator\Test\Models\SomeModel;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;

class SwitchCaseTest extends TestCase
{
    protected function setUp(): void
    {
        $this->validator = new Validator();
        $this->model = new SomeModel();
    }

    public function testSwitchCase(): void
    {
        $violationsList = $this->validator->validate($this->model);
        assertCount(1, $violationsList);
        assertEquals('This value should be blank.', $violationsList[0]->getMessage());
    }
}
