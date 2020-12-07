<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Laramore\Exceptions\LockException;
use Laramore\Elements\Element;

final class ElementTest extends TestCase
{
    public function testElementNoArgs()
    {
        $this->expectException(ArgumentCountError::class);

        new Element();
    }

    public function testElementOneArg()
    {
        $element = new Element('name');

        $this->assertEquals($element->getName(), 'name');
        $this->assertEquals($element->get('name'), 'name');
        $this->assertEquals($element->name, 'name');

        $this->assertEquals($element->getNative(), 'name');
        $this->assertEquals($element->get('name'), 'name');
        $this->assertEquals($element->native, 'name');
        $this->assertEquals((string) $element, 'name');
        $this->assertEquals($element->__toString(), 'name');
        $this->assertEquals($element(), 'name');
    }

    public function testSimpleElement()
    {
        $element = new Element('name', 'native');

        $this->assertEquals($element->getName(), 'name');
        $this->assertEquals($element->get('name'), 'name');
        $this->assertEquals($element->name, 'name');

        $this->assertEquals($element->getNative(), 'native');
        $this->assertEquals($element->get('native'), 'native');
        $this->assertEquals($element->native, 'native');
        $this->assertEquals((string) $element, 'native');
        $this->assertEquals($element->__toString(), 'native');
        $this->assertEquals($element(), 'native');
    }

    public function testValueElement()
    {
        $element = new Element('name', 'native');

        $this->assertFalse($element->hasNew());
        $this->assertFalse($element->has('new'));
        $element->set('new', 'value');
        $this->assertTrue($element->hasNew());
        $this->assertTrue($element->has('new'));
        $this->assertEquals($element->getNew(), 'value');
        $this->assertEquals($element->get('new'), 'value');
        $this->assertEquals($element->new, 'value');

        $element->new = 'new';
        $this->assertEquals($element->getNew(), 'new');
        $this->assertEquals($element->get('new'), 'new');
        $this->assertEquals($element->new, 'new');

        $element->setNew('wen');
        $this->assertEquals($element->getNew(), 'wen');
        $this->assertEquals($element->get('new'), 'wen');
        $this->assertEquals($element->new, 'wen');
    }

    public function testCaseElement()
    {
        $element = new Element('name', 'native');

        $element->newKey = 'newValue';
        $this->assertTrue($element->hasNewKey());
        $this->assertTrue($element->has('new_key'));
        $this->assertFalse($element->hasNew_Key());
        $this->assertFalse($element->has('newKey'));
        $this->assertEquals($element->newKey, 'newValue');
        $this->assertEquals($element->getNewKey(), 'newValue');
        $this->assertEquals($element->get('new_key'), 'newValue');
    }

    public function testLockElement()
    {
        $element = new Element('name', 'native');

        $element->key = 'value';

        $this->assertFalse($element->isLocked());
        $element->lock();
        $this->assertTrue($element->isLocked());

        $this->assertEquals($element->key, 'value');

        $this->expectException(LockException::class);

        $element->key = 'nope';
    }
}
