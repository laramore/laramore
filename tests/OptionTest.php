<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Laramore\Elements\{
    OptionElement, OptionManager, Element, ElementManager
};

final class OptionTest extends TestCase
{
    public function testOptionClass()
    {
        $operator = new OptionElement('name', 'native');

        $this->assertTrue($operator instanceof Element);
    }

    public function testOptionManagerClass()
    {
        $manager = new OptionManager();

        $this->assertTrue($manager instanceof ElementManager);

        $manager->set(new OptionElement('name', 'native'));
    }

    public function testWrongOption()
    {
        $manager = new OptionManager();

        $this->expectException(ErrorException::class);

        $manager->set(new class() {});
    }
}
