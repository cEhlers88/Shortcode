# Shortcode
This library helps you to locate DOM-Elements and Shortcodes in a string. 

## Usage
Using the ShortcodeParser to convert a string that contains several Shortcodes, DOM-Elements or just simple text.

``Overview``
`````php
<?php 

use CEhlers\Shortcode\ShortcodeParser;

// Parse simple text
$shortcode = ShortcodeParser::parse('This string just contain simple text.'); 
# [{TextFragment}]

// Parse DOM-Element example
$shortcode = ShortcodeParser::parse('<foo dummyAttribute="dummyValue">dummyText</foo>');
# [{DomElement attributes=[{AttributeDTO}] innerFragments=[{TextFragment}] }]

// Parse Shortcode example
$shortcode = ShortcodeParser::parse('[foo dummyAttribute="dummyValue"]dummyText[/foo]');
# [{Shortcode attributes=[{AttributeDTO}] innerFragments=[{TextFragment}] }]

// Parse Gutenberg-Block example
$shortcode = ShortcodeParser::parse('<!-- wp:button {"foo":"bar"} -->dummyText<!-- /wp:button -->');
# [{DomElement attributes=[{AttributeDTO}] innerFragments=[{TextFragment}] }]

// Parse individual
$shortcode = ShortcodeParser::parse('[foo dummyAttribute="dummyValue"]
                                        <foo dummyAttribute="dummyValue">dummyText</foo>
                                    [/foo]
                                    <!-- wp:button {"foo":"bar"} -->dummyText<!-- /wp:button -->');
# [
#   {Shortcode
#       attributes=[{AttributeDTO}]
#       innerFragments=[
#           {DomElement attributes=[{AttributeDTO}] innerFragments=[{TextFragment}] }
#       ]
#   },
#   {DomElement attributes=[{AttributeDTO}] innerFragments=[{TextFragment}] }
# ]
`````

`````php
<?php

use CEhlers\Shortcode\ShortcodeParser;

$parsed = ShortcodeParser::parse('<div class="foo1">bar1</div>[code class="foo2"]bar2[/code]'); 

echo $parsed[0]->getName();                     # div
echo $parsed[0];                                # <div class="foo1">bar1</div>
echo $parsed[0]->getAttributeValue('class');    # foo1
echo $parsed[0]->getInnerFragment(0);           # bar1

echo $parsed[1]->getName();                     # code
echo $parsed[1];                                # [code class="foo2"]bar2[/code]
echo $parsed[1]->getAttributeValue('class');    # foo2
echo $parsed[1]->getInnerFragment(0);           # bar2

`````

### AttributeDTO
- name
- value
- type (flag/boolean/number/string)
