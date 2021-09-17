<?php

namespace CEhlers\Shortcode\Tests;

use CEhlers\Shortcode\Shortcode;
use CEhlers\Shortcode\ShortcodeParser;
use CEhlers\Shortcode\TextFragment;
use PHPUnit\Framework\TestCase;

class ShortcodeParserTest extends TestCase
{
    public function testCreateParser(){
        $parser = new ShortcodeParser();
        $this->assertInstanceOf(ShortcodeParser::class, $parser);
    }
    public function testParseSimpleShortcode(){
        $rawOne = "[Foo]Bar[/Foo]";
        $parsed = ShortcodeParser::parse($rawOne."[Bar][/Bar]");
        $this->assertInstanceOf(Shortcode::class, $parsed[0]);
        $this->assertCount(2, $parsed);
        $this->assertSame($rawOne,$parsed[0]->getRaw());
    }
    public function testParseSimpleTextFragment(){
        $this->assertInstanceOf(TextFragment::class, ShortcodeParser::parse("test")[0]);
    }
    public function testParseTextFragmentAndShortcode(){
        $parsed = ShortcodeParser::parse("test[Test][/Test]");
        $this->assertInstanceOf(TextFragment::class, $parsed[0]);
        $this->assertInstanceOf(Shortcode::class, $parsed[1]);
        $this->assertSame("Test", $parsed[1]->getName());
    }
    public function testParseShortcodeAndTextFragment(){
        $parsed = ShortcodeParser::parse("[Test][/Test]<p>test</p>");
        $this->assertCount(2,$parsed);
        $this->assertInstanceOf(Shortcode::class, $parsed[0]);
        $this->assertInstanceOf(TextFragment::class, $parsed[1]);
    }
    public function testParseAttribute(){
        $attributeName = 'foo';
        $attributeValue = 'bar';
        $parsed = ShortcodeParser::parse("[Test ".$attributeName."='".$attributeValue."'][/Test][Foo][/Foo]");

        $this->assertCount(1, $parsed[0]->getAttributes());
        $this->assertCount(0, $parsed[1]->getAttributes());
        $this->assertSame($attributeName,$parsed[0]->getAttributes()[0]->name);
        $this->assertSame($attributeValue,$parsed[0]->getAttributes()[0]->value);
        $this->assertSame("string",$parsed[0]->getAttributes()[0]->type);
    }
    public function testParseMultipleAttributes(){
        $parsed = ShortcodeParser::parse("[Foo][/Foo][Test attr=1 more='123'][/Test]");
        $this->assertCount(0, $parsed[0]->getAttributes());
        $this->assertCount(2, $parsed[1]->getAttributes());
        $this->assertSame("number",$parsed[1]->getAttributes()[0]->type);
        $this->assertSame("string",$parsed[1]->getAttributes()[1]->type);
    }
    public function testParseDifferentAttributeTypes(){
        $parsed = ShortcodeParser::parse("[Foo attr1=123 attr2='test string' attr3=true attr4=false attr5='true'][/Foo]");
        $this->assertSame("number",$parsed[0]->getAttributes()[0]->type);
        $this->assertSame("string",$parsed[0]->getAttributes()[1]->type);
        $this->assertSame("test string",$parsed[0]->getAttributeValue('attr2'));
        $this->assertSame("boolean",$parsed[0]->getAttributes()[2]->type);
        $this->assertSame("boolean",$parsed[0]->getAttributes()[3]->type);
        $this->assertSame("boolean",$parsed[0]->getAttributes()[4]->type);
    }

    public function test(){
        $parsed = ShortcodeParser::parse("[Foo] [Bar]Content[/Bar][Bar][/Bar]Content[/Foo]");
        $this->assertInstanceOf(Shortcode::class, $parsed[0]);
        $this->assertCount(1, $parsed);
        $this->assertCount(3, $parsed[0]->getInnerFragments());
        $this->assertCount(1, $parsed[0]->getInnerFragments()[0]->getInnerFragments());
    }

}