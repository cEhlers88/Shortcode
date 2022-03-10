<?php

namespace CEhlers\Shortcode\Tests;

use CEhlers\Shortcode\DomElement;
use CEhlers\Shortcode\DomParser;
use CEhlers\Shortcode\TextFragment;
use PHPUnit\Framework\TestCase;

class DomParserTest extends TestCase
{
    public function testHalloWorld(){
        $testValue = "<div><p>hallo world </p></div><br/>";

        $parsed = DomParser::parse($testValue);

        $this->assertInstanceOf(DomElement::class, $parsed[0]);
        $this->assertInstanceOf(DomElement::class, $parsed[0]->getInnerFragment(0));
        $this->assertInstanceOf(TextFragment::class, $parsed[0]->getInnerFragment(0)->getInnerFragments()[0]);

        $this->assertCount(2, $parsed);
        $this->assertCount(1, $parsed[0]->getInnerFragments());
        $this->assertCount(1, $parsed[0]->getInnerFragment(0)->getInnerFragments());

        $this->assertSame($testValue,  $parsed[0] . $parsed[1]);
    }

}
