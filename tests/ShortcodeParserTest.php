<?php

namespace CEhlers\Shortcode\Tests;

use CEhlers\Shortcode\DomElement;
use CEhlers\Shortcode\DTO\AttributeDTO;
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
        $rawTwo = "[Bar][/Bar]";
        $parsed = ShortcodeParser::parse($rawOne.$rawTwo);

        $this->assertInstanceOf(Shortcode::class, $parsed[0]);
        $this->assertCount(2, $parsed);

        $this->assertSame($rawOne,$parsed[0]->getRaw());
        $this->assertSame($rawTwo,$parsed[1]->getRaw());
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
        $parsed = ShortcodeParser::parse("[Test][/Test]test");
        $this->assertCount(2,$parsed);
        $this->assertInstanceOf(Shortcode::class, $parsed[0]);
        $this->assertInstanceOf(TextFragment::class, $parsed[1]);
    }
    public function testParseShortcodeAndDomElement(){
        $parsed = ShortcodeParser::parse("[Test][/Test]<p>test</p>");
        $this->assertCount(2,$parsed);
        $this->assertInstanceOf(Shortcode::class, $parsed[0]);
        $this->assertInstanceOf(DomElement::class, $parsed[1]);
    }
    public function testParseDomElementTextAndShortcode(){
        $parsed = ShortcodeParser::parse("<p>test</p>123[Test][/Test]");
        $this->assertCount(3,$parsed);
        $this->assertInstanceOf(DomElement::class, $parsed[0]);
        $this->assertInstanceOf(TextFragment::class, $parsed[1]);
        $this->assertInstanceOf(Shortcode::class, $parsed[2]);
    }
    public function testParseAttribute(){
        $attributeName = 'attr1';
        $attributeValue = 'value1';
        $parsed = ShortcodeParser::parse("[Test ".$attributeName."='".$attributeValue."' attr2=2][/Test][Foo][/Foo]");

        $this->assertCount(2, $parsed[0]->getAttributes());
        $this->assertCount(0, $parsed[1]->getAttributes());

        $this->assertSame($attributeName,$parsed[0]->getAttributes()[0]->name);
        $this->assertSame($attributeValue,$parsed[0]->getAttributes()[0]->value);
        $this->assertSame("string",$parsed[0]->getAttributes()[0]->type);
    }
    public function testParseMultipleAttributes(){
        $parsed = ShortcodeParser::parse("[Foo][/Foo][Test attr=1 more='123'][/Test]");
        $this->assertCount(2, $parsed);

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

    public function testParseDomElementTextAndShortcodeWithAttributes(){
        $testClass1 = 'foo1';
        $testClass2 = 'foo2';
        $testFlag1 = 'bar1';
        $testFlag2 = 'bar2';

        $parsed = ShortcodeParser::parse("
            <p class='$testClass1' $testFlag1>test</p>
            123
            [Test class='$testClass2' $testFlag2 dummy][/Test]
        ");

        $this->assertCount(3,$parsed);
        $this->assertInstanceOf(DomElement::class, $parsed[0]);
        $this->assertInstanceOf(TextFragment::class, $parsed[1]);
        $this->assertInstanceOf(Shortcode::class, $parsed[2]);

        $this->assertCount(2,$parsed[0]->getAttributes());
        $this->assertCount(3,$parsed[2]->getAttributes());

        $this->assertSame($testClass1, $parsed[0]->getAttributes()[0]->value);
        $this->assertSame('flag', $parsed[0]->getAttributes()[1]->type);
        $this->assertSame($testFlag1, $parsed[0]->getAttributes()[1]->name);

        $this->assertSame($testClass2, $parsed[2]->getAttributes()[0]->value);
        $this->assertSame('flag', $parsed[2]->getAttributes()[1]->type);
        $this->assertSame($testFlag2, $parsed[2]->getAttributes()[1]->name);
        $this->assertSame('flag', $parsed[2]->getAttributes()[2]->type);
        $this->assertSame('dummy', $parsed[2]->getAttributes()[2]->name);
    }

    public function testSingleTags(){
        $parsed = ShortcodeParser::parse("[foo][bar foo='bar'/][bar2][bar foo='bar2'/][/bar2][/foo]");
        $this->assertCount(2,$parsed[0]->getInnerFragments());
        $this->assertSame('bar',$parsed[0]->getInnerFragment(0)->getAttributeValue('foo'));
    }

    public function testSingleTagAttributes(){
        array_map(function($format){
            $testValue = 'value123456789';
            $testValue2 = 'value987654321';

            $parsed = ShortcodeParser::parse(sprintf($format, "bar='".$testValue."'", "dummy", "lala='".$testValue2."'"));

            $this->assertCount(1,$parsed);
            $this->assertCount(3,$parsed[0]->getAttributes());

            $this->assertSame($testValue,$parsed[0]->getAttributeValue('bar'));
            $this->assertSame($testValue2,$parsed[0]->getAttributeValue('lala'));
        },[
            "[foo %s %s %s /]",
            "[foo %s %s %s/]",
            "[foo %s%s %s/]",
        ]);
    }

    public function test(){
        $parsed = ShortcodeParser::parse("[Foo] [Bar]Content[/Bar][Bar][/Bar]Content[/Foo]");
        $this->assertInstanceOf(Shortcode::class, $parsed[0]);
        $this->assertCount(1, $parsed);
        $this->assertCount(3, $parsed[0]->getInnerFragments());
        $this->assertCount(1, $parsed[0]->getInnerFragment(0)->getInnerFragments());
        $this->assertSame("ContentContent", $parsed[0]->getInnerText());
    }
}
