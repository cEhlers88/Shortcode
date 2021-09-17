<?php

namespace CEhlers\Shortcode;

class TextFragment
{
    protected string $rawText;

    public function __construct(string $rawText){
        $this->rawText = $rawText;
    }

    public function __toString(){return $this->rawText;}
    public function toHtmlList(){
        return "<li class='fragment fragment--text'>".$this."</li>";
    }
    public function getInnerFragments(){return [];}

    public function getRaw():string {return $this->rawText;}
}