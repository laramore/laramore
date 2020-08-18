<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Laramore\Elements\{
    OperatorElement, OperatorManager, Element, ElementManager
};

final class OperatorTest extends TestCase
{
    public function testOperatorClass()
    {
        $operator = new OperatorElement('name', 'native');

        $this->assertTrue($operator instanceof Element);
    }

    public function testOperatorManagerClass()
    {
        $manager = new OperatorManager();

        $this->assertTrue($manager instanceof ElementManager);

        $manager->set(new OperatorElement('name', 'native'));
    }

    public function testWrongOperator()
    {
        $manager = new OperatorManager();

        $this->expectException(ErrorException::class);

        $manager->set(new class() {});
    }
}
