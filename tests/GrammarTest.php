<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Laramore\Grammars\GrammarType;
use Laramore\Observers\BaseObserver;

final class GrammarTest extends TestCase
{
    public function testGrammarTypeClass()
    {
        $operator = new GrammarType('name', function () {});

        $this->assertTrue($operator instanceof BaseObserver);
    }
}
