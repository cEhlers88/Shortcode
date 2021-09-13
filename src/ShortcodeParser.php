<?php

namespace CEhlers\Shortcode;

class ShortcodeParser
{
    /**
     * @return TextFragment[]|Shortcode[]
     */
    public static function parse(string $string):array{
        $result = [];
        preg_match( '@\[([^<>&/\[\]\x00-\x20=]++)@', $string, $matches );
        if(array_key_exists(1,$matches)){
            $shortcodeStart = strpos($string,'['.$matches[1]);
            $shortcodeEndBegin = strpos($string,'[/'.$matches[1].']');
            $lenClosingTag = strlen('[/'.$matches[1].']');
            $shortcodeLastEnd = $shortcodeEndBegin+$lenClosingTag;

            if($shortcodeStart>0){
                $result[] = new TextFragment(substr($string,0,$shortcodeStart));
            }

            $shortcode = new Shortcode(substr($string,$shortcodeStart,$shortcodeLastEnd-$shortcodeStart));
            $shortcode
                ->setName($matches[1])
            ;
            $result[] = $shortcode;
            if($shortcodeLastEnd<strlen($string)){
                $result = array_merge($result,ShortcodeParser::parse(substr($string,$shortcodeLastEnd)));
            }
        }else{
            if(!empty($string)){
                $result[] = new TextFragment($string);
            }
        }

        return $result;
    }
}