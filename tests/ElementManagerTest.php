<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Laramore\Exceptions\LockException;
use Laramore\Elements\{
    Element, ElementManager
};

final class ElementManagerTest extends TestCase
{
    public function testManagerNoArgs()
    {
        $manager = new ElementManager();

        $this->assertCount(0, $manager->all());
        $this->assertEquals(0, $manager->count());
        $this->assertCount(0, $manager->definitions());

        $manager->lock();

        $this->assertCount(0, $manager->all());
        $this->assertEquals(0, $manager->count());
        $this->assertCount(0, $manager->definitions());
    }

    public function testManagerNullArg()
    {
        $this->expectException(TypeError::class);

        new ElementManager(null);
    }

    protected function getElements(): array
    {
        $element = new Element('name', 'native');
        $element2 = new Element('name2', 'native2');
        $element2->key = 'value';

        $this->assertFalse($element->has('key'));
        $this->assertEquals($element2->get('key'), 'value');

        return [$element, $element2];
    }

    public function testManagerDefaults()
    {
        $manager = new ElementManager($this->getElements());

        $this->assertCount(2, $manager->all());
        $this->assertEquals(2, $manager->count());
        $this->assertCount(0, $manager->definitions());

        $manager->lock();

        $this->assertCount(2, $manager->all());
        $this->assertEquals(2, $manager->count());
        $this->assertCount(0, $manager->definitions());
    }

    public function testManagerSet()
    {
        $manager = new ElementManager();

        $this->assertCount(0, $manager->all());
        $this->assertEquals(0, $manager->count());
        $this->assertCount(0, $manager->definitions());

        $manager->set($this->getElements());

        $this->assertCount(2, $manager->all());
        $this->assertEquals(2, $manager->count());
        $this->assertCount(0, $manager->definitions());

        $manager->lock();

        $this->assertCount(2, $manager->all());
        $this->assertEquals(2, $manager->count());
        $this->assertCount(0, $manager->definitions());
    }

    public function testManagerSetOverride()
    {
        $manager = new ElementManager($this->getElements());

        $this->assertCount(2, $manager->all());
        $this->assertEquals(2, $manager->count());
        $this->assertCount(0, $manager->definitions());

        $manager->set(new Element('name', 'newNative'));

        $this->assertCount(2, $manager->all());
        $this->assertEquals(2, $manager->count());
        $this->assertCount(0, $manager->definitions());

        $manager->lock();

        $this->assertCount(2, $manager->all());
        $this->assertEquals(2, $manager->count());
        $this->assertCount(0, $manager->definitions());
    }

    public function testManagerCreate()
    {
        $manager = new ElementManager();

        $this->assertCount(0, $manager->all());
        $this->assertEquals(0, $manager->count());
        $this->assertCount(0, $manager->definitions());

        $manager->create('new');

        $this->assertCount(1, $manager->all());
        $this->assertEquals(1, $manager->count());
        $this->assertCount(0, $manager->definitions());

        $this->assertTrue($manager->has('new'));
        $this->assertEquals($manager->get('new')->native, 'new');

        $manager->create('new2', 'native2');

        $this->assertCount(2, $manager->all());
        $this->assertEquals(2, $manager->count());
        $this->assertCount(0, $manager->definitions());

        $this->assertTrue($manager->has('new2'));
        $this->assertEquals($manager->get('new2')->native, 'native2');

        $manager->lock();

        $this->assertCount(2, $manager->all());
        $this->assertEquals(2, $manager->count());
        $this->assertCount(0, $manager->definitions());
    }

    public function testManagerCreateOverride()
    {
        $manager = new ElementManager();

        $this->assertCount(0, $manager->all());
        $this->assertEquals(0, $manager->count());
        $this->assertCount(0, $manager->definitions());

        $manager->create('new');

        $this->assertCount(1, $manager->all());
        $this->assertEquals(1, $manager->count());
        $this->assertCount(0, $manager->definitions());

        $manager->create('new', 'native');

        $this->assertCount(1, $manager->all());
        $this->assertEquals(1, $manager->count());
        $this->assertCount(0, $manager->definitions());

        $this->assertTrue($manager->has('new'));
        $this->assertEquals($manager->get('new')->native, 'native');

        $manager->lock();

        $this->assertCount(1, $manager->all());
        $this->assertEquals(1, $manager->count());
        $this->assertCount(0, $manager->definitions());
    }

    public function testManagerHas()
    {
        $manager = new ElementManager($this->getElements());

        $this->assertTrue($manager->has('name'));
        $this->assertTrue($manager->has('name2'));
        $this->assertFalse($manager->has('name3'));
        $this->assertFalse($manager->has('name4'));

        $manager->set(new Element('name3', 'native3'));
        $this->assertTrue($manager->has('name'));
        $this->assertTrue($manager->has('name2'));
        $this->assertTrue($manager->has('name3'));
        $this->assertFalse($manager->has('name4'));

        $manager->lock();
        $this->assertTrue($manager->has('name'));
        $this->assertTrue($manager->has('name2'));
        $this->assertTrue($manager->has('name3'));
        $this->assertFalse($manager->has('name4'));
    }

    public function testManagerGet()
    {
        $elements = $this->getElements();
        $manager = new ElementManager($elements);

        $this->assertEquals($manager->get('name'), $elements[0]);
        $this->assertEquals($manager->get('name2'), $elements[1]);

        $element = new Element('name', 'newNative');

        $manager->set($element);
        $this->assertEquals($manager->get('name'), $element);
        $this->assertEquals($manager->get('name2'), $elements[1]);

        $manager->lock();
        $this->assertEquals($manager->get('name'), $element);
        $this->assertEquals($manager->get('name2'), $elements[1]);
    }

    public function testManagerMagicGet()
    {
        $elements = $this->getElements();
        $manager = new ElementManager($elements);

        $this->assertEquals($manager->name, $elements[0]);
        $this->assertEquals($manager->name2, $elements[1]);

        $element = new Element('name', 'newNative');

        $manager->set($element);
        $this->assertEquals($manager->name, $element);
        $this->assertEquals($manager->name2, $elements[1]);

        $manager->lock();
        $this->assertEquals($manager->name, $element);
        $this->assertEquals($manager->name2, $elements[1]);
    }

    public function testManagerFind()
    {
        $elements = $this->getElements();
        $manager = new ElementManager($elements);

        $this->assertEquals($manager->find('native'), $elements[0]);
        $this->assertEquals($manager->find('native2'), $elements[1]);

        $element = new Element('name3', 'native');

        $manager->set($element);
        $this->assertEquals($manager->find('native'), $elements[0]);
        $this->assertEquals($manager->find('native2'), $elements[1]);

        $elements[0]->native = 'native3';
        $this->assertEquals($manager->find('native'), $element);
        $this->assertEquals($manager->find('native2'), $elements[1]);
        $this->assertEquals($manager->find('native3'), $elements[0]);

        $manager->lock();
        $this->assertEquals($manager->find('native'), $element);
        $this->assertEquals($manager->find('native2'), $elements[1]);
        $this->assertEquals($manager->find('native3'), $elements[0]);
    }

    public function testManagerDefineName()
    {
        $elements = $this->getElements();
        $manager = new ElementManager($elements);

        $manager->define('key');
        $this->assertTrue($manager->doesDefine('key'));
        $this->assertFalse($manager->doesDefine('key2'));
        $this->assertCount(2, $manager->all());
        $this->assertEquals(2, $manager->count());
        $this->assertCount(1, $manager->definitions());
        $this->assertEquals(['key' => null], $manager->definitions());
        $this->assertEquals($elements[0]->key, $elements[0]->name);
        $this->assertEquals($elements[1]->key, 'value');

        $manager->lock();
        $this->assertCount(2, $manager->all());
        $this->assertEquals(2, $manager->count());
        $this->assertCount(1, $manager->definitions());
    }

    public function testManagerCall()
    {
        $elements = $this->getElements();
        $manager = new ElementManager($elements);

        $this->assertEquals($manager->name(), $elements[0]->__toString());
        $this->assertEquals($manager->name2(), $elements[1]->__toString());

        $manager->lock();

        $this->assertEquals($manager->name(), $elements[0]->__toString());
        $this->assertEquals($manager->name2(), $elements[1]->__toString());
    }
}
