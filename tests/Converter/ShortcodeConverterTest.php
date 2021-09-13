<?php

namespace CEhlers\Shortcode\Tests\Converter;

use CEhlers\Shortcode\Converter\ShortcodeConverter;
use CEhlers\Shortcode\ShortcodeParser;
use PHPUnit\Framework\TestCase;

class ShortcodeConverterTest extends TestCase
{
    public function test(){
        $converter = new ShortcodeConverter();
        $originalString = "[B]Bold[/B]Normal";
        $convertedString = (string) $converter->convert($originalString);

        $this->assertSame($originalString,$convertedString);
    }
}