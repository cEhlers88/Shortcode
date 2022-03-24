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

    public function testSingleTags(){
        $parsed = DomParser::parse("<foo><bar foo='bar'/><bar2><bar foo='bar2'/></bar2></foo>");
        $this->assertCount(2,$parsed[0]->getInnerFragments());
        $this->assertSame('bar',$parsed[0]->getInnerFragment(0)->getAttributeValue('foo'));
    }

    public function testSingleTagAttributes(){
        array_map(function($format){
            $testValue = 'value123456789';
            $testValue2 = 'value987654321';

            $parsed = DomParser::parse(sprintf($format, "bar='".$testValue."'","dummy", "lala='".$testValue2."'"));

            $this->assertCount(1,$parsed);
            $this->assertCount(3,$parsed[0]->getAttributes());

            $this->assertSame($testValue,$parsed[0]->getAttributeValue('bar'));
            $this->assertSame($testValue2,$parsed[0]->getAttributeValue('lala'));
        },[
            "<foo %s %s %s />",
            "<foo %s %s %s/>",
            "<foo %s%s %s/>",
        ]);
    }
}
