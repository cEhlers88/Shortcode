<?php

namespace CEhlers\Shortcode;

class DomParser
{
    /**
     * @return TextFragment[]|DomElement[]
     */
    public static function parse(string $string, int $offset=0):array{
        $result = [];
        $startTagPos = strpos($string,'<',$offset);
        $isCommentNode = (isset(substr($string,$offset)[$startTagPos+1]) && substr($string,$offset)[$startTagPos+1]==='!');

        if($startTagPos>-1){
            $nextSpacePos = strpos($string,' ',$startTagPos);
            $nextClosePos = strpos($string,($isCommentNode?'--':'').'>',$startTagPos);

            if($isCommentNode){
                $nameEndPos = strpos($string,' ',$nextSpacePos+1);
                $nameLength = $nameEndPos - $nextSpacePos - 1;
            }else{
                if($nextSpacePos > -1 && $nextSpacePos < $nextClosePos){
                    $nameEndPos = $nextSpacePos;
                }else{
                    $nameEndPos = $nextClosePos;
                }
                $nameLength = $nameEndPos - 1 - $startTagPos;
            }

            $tagName = substr($string, $startTagPos+1+($isCommentNode?4:0), $nameLength-($string[$nameEndPos-1]==='/'?1:0));
            $debug = false;//($tagName==='wp:column');

            $openTag = '<' . ($isCommentNode?'!-- ':'') . $tagName;
            $closeTag = '<'.($isCommentNode?'!-- ':'').'/'.$tagName.($isCommentNode?' --':'').'>';

            $shortcodeStart = strpos($string,$openTag);

            $shortcodeEndBegin = self::getClosingTagPosition($string, $openTag, $closeTag,$debug);

            $lenClosingTag = strlen($closeTag);

            if(!$shortcodeEndBegin){

                $shortcodeEndBegin = strpos($string,($isCommentNode?'/-->':'/>'));
                if(!$shortcodeEndBegin){
                    // current found is not a dom-element
                    return self::parse($string, $startTagPos+1);
                }
                $lenClosingTag = ($isCommentNode?4:2);
            }

            $shortcodeLastEnd = $shortcodeEndBegin+$lenClosingTag;

            if($shortcodeStart>0){
                $raw = substr($string,0,$shortcodeStart);
                if(!empty(trim($raw))){
                    $result[] = new TextFragment($raw);
                }
            }

            if($debug){
                echo "openTag:|".$openTag.'|<br/>';
                echo "closeTag:|".$closeTag.'|<br/>';
                echo "start:|".$shortcodeStart.'|<br/>';
                echo "endBegin:|".$shortcodeEndBegin.'|<br/>';
                echo "end:".substr($string,$shortcodeStart,$shortcodeLastEnd-$shortcodeStart);
                echo "string:".$string;

                die();
            }

            $element = new DomElement(substr($string,$shortcodeStart,$shortcodeLastEnd-$shortcodeStart),$tagName);

            $element
                ->setName($tagName)
            ;
            $result[] = $element;
            if($shortcodeLastEnd<strlen($string)){

                $rest = substr($string,$shortcodeLastEnd);
                if(!empty(trim($rest))){
                    $result = array_merge($result,DomParser::parse($rest));
                }
            }
        }else{
            if(!empty($string)){
                $result[] = new TextFragment($string);
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

    private static function getClosingTagPosition(string $string, string $openTag, string $closeTag,$debug=false):int{
        $closingTagPositions = self::strposAll($string, $closeTag);
        if(count($closingTagPositions)===1){
            return $closingTagPositions[0];
        }
        $counter = 0;
        foreach ($closingTagPositions as $closingTagPosition){
            $counter++;
            if($debug){
                echo $openTag;
                echo count(self::strposAll($string, $openTag, $closingTagPosition))."<br/>";
            }
            if($counter-count(self::strposAll($string, $openTag, $closingTagPosition))===0){
                return $closingTagPosition;
            }
        }
        return false;
    }

    private static function strposAll(string $string, string $needle,int $positionLimit=-1):array{
        $offset = 0;
        $allpos = array();
        while (($pos = strpos($string, $needle, $offset)) !== FALSE) {
            $offset   = $pos + 1;
            if($positionLimit===-1 || $positionLimit>$pos){
                $allpos[] = $pos;
            }
        }
        return $allpos;
    }
}
/*
*/
