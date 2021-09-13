# Shortcode
Library to handle shortcode-strings in PHP

## Configure

## Usage

`````php
<?php

use CEhlers\Shortcode\ShortcodeParser;

$shortcode = ShortcodeParser::parse('[foo dummyAttribute="dummyValue"]dummyText[/foo]');

`````
