<?php

namespace CEhlers\Shortcode;

use CEhlers\Shortcode\DTO\AttributeDTO;

abstract class AbstractFragmentObject extends TextFragment
{
    private string $name;
    /**
     * @var AttributeDTO[]
     */
    private array $attributes;
    private array $innerFragments;

    abstract public function getTagStart():string;
    abstract public function getTagEnd():string;
    abstract public function getTagClose():string;

    public function __construct(string $rawText,string $name="") {
        parent::__construct($rawText);
        $this->name = $name;
        $this->attributes = [];
        $posAttributesStart = strpos($rawText,' ');
        $posAttributesEnd = strpos($rawText,']');

        $innerText = substr($rawText,$posAttributesEnd+1, strrpos($rawText,'[')-$posAttributesEnd-1);
        $this->innerFragments = ShortcodeParser::parse($innerText);

        if($posAttributesStart>0 && $posAttributesEnd>$posAttributesStart){
            $this->attributes = $this->parseAttributes(substr($rawText,$posAttributesStart,$posAttributesEnd-$posAttributesStart));
        }

    }

    private function parseAttributes(string $attributesString):array{
        $foundAttributes =  [];
        $attrsSplit = explode(' ',$attributesString);
        foreach ($attrsSplit as $attribute){
            if($attribute===''){continue;}
            $nameValueSplit = explode('=',$attribute);
            $foundAttributes[] = AttributeDTO::create($nameValueSplit[0],isset($nameValueSplit[1])?$nameValueSplit[1]:'');
        }
        return $foundAttributes;
    }

    /**
     * @return AttributeDTO[]
     */
    public function getAttributes():array{
        return $this->attributes;
    }

    public function getAttributeValue(string $attributeName, string $defaultValue=""){
        foreach ($this->attributes as $attribute){
            if($attribute->name===$attributeName){
                return $attribute->value;
            }
        }
        return $defaultValue;
    }

    public function __toString()
    {
        $attributes = (count($this->attributes)>0?' ':'');
        $inner = '';
        foreach ($this->attributes as $attribute){$attributes.=$attribute;}
        foreach ($this->innerFragments as $fragment){$inner.=$fragment;}

        return sprintf($this->getTagStart()."%s%s".$this->getTagEnd()."%s".$this->getTagStart().$this->getTagClose()."%s".$this->getTagEnd(),
            $this->name,$attributes,$inner,$this->name);
    }

    public function getInnerFragments(){
        return $this->innerFragments;
    }

    public function getName():string {
        return $this->name;
    }
    public function setName(string $name):AbstractFragmentObject {
        $this->name = $name;
        return $this;
    }
}