<?php

namespace CEhlers\Shortcode;

use CEhlers\Shortcode\DTO\ParserOptionsDTO;

class ShortcodeParser
{
    /**
     * @return TextFragment[]|Shortcode[]
     */
    public static function parse(string $string, ParserOptionsDTO $optionsDTO = null):array{
        $result = [];
        preg_match( '@\[([^<>&/\[\]\x00-\x20=]++)@', $string, $matches );
        if(is_null($optionsDTO)){
            $optionsDTO = ParserOptionsDTO::create();
        }

        if(array_key_exists(1,$matches)){
            $shortcodeStart = strpos($string,'['.$matches[1]);
            $shortcodeEndBegin = strpos($string,'[/'.$matches[1].']');
            $lenClosingTag = strlen('[/'.$matches[1].']');

            if(!$shortcodeEndBegin){
                $shortcodeEndBegin = strpos($string,'/]');
                $lenClosingTag = 2;
            }

            if(!$shortcodeEndBegin){
                $shortcodeEndBegin = strpos($string,']');
                $lenClosingTag = 1;
            }

            $shortcodeLastEnd = $shortcodeEndBegin+$lenClosingTag;

            if($shortcodeStart>0){
                $raw = substr($string,0,$shortcodeStart);
                if(!empty(trim($raw))){
                    if($optionsDTO->composeParser){
                        $result = array_merge($result, DomParser::parse($raw));
                    }else{
                        $result[] = new TextFragment($raw);
                    }
                }
            }

            $shortcode = new Shortcode(substr($string,$shortcodeStart,$shortcodeLastEnd-$shortcodeStart),$matches[1]);

            $result[] = $shortcode;
            if($shortcodeLastEnd<strlen($string)){
                $rest = substr($string,$shortcodeLastEnd);
                if(!empty(trim($rest))){
                $result = array_merge($result,ShortcodeParser::parse($rest, $optionsDTO));
                }
            }
        }else{
            if(!empty($string)){
                if($optionsDTO->composeParser){
                    $result = array_merge($result, DomParser::parse($string));
                }else{
                    $result[] = new TextFragment($string);
                }
            }
        }

        return $result;
    }

    public static function parseToString(string $string):string {
        $result = '';
        foreach (self::parse($string) as $fragment){
            $result .= $fragment;
        }
        return $result;
    }
}
