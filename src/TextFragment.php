<?php

namespace CEhlers\Shortcode;

class TextFragment
{
    protected string $rawText;
    protected string $syncKey;

    public function __construct(string $rawText){
        $this->rawText = $rawText;
        $this->syncKey = uniqid('sk-');
    }

    public function __toString(){return $this->rawText;}
    public function toHtmlList(){
        return "<li class='".$this->getSyncKey()." fragment fragment--text'>".$this."</li>";
    }
    public function getInnerFragments(){return [];}

    public function getRaw():string {return $this->rawText;}

    final public function getSyncKey():string{
        return $this->syncKey;
    }
    public function setSyncKey(string $syncKey):TextFragment{
        $this->syncKey = $syncKey;
        return $this;
    }
}