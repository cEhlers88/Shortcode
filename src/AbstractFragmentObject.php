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
    private string $syncKey;

    abstract public function getTagStart():string;
    abstract public function getTagEnd():string;
    abstract public function getTagClose():string;

    public function __construct(string $rawText="",string $name="") {
        parent::__construct($rawText);
        $this->name = $name;
        $this->attributes = [];
        $this->syncKey = uniqid('sk-');
        $posAttributesStart = strpos($rawText,' ');
        $posAttributesEnd = strpos($rawText,']');

        $innerText = substr($rawText,$posAttributesEnd+1, strrpos($rawText,'[')-$posAttributesEnd-1);
        $this->innerFragments = ShortcodeParser::parse($innerText);

        if($posAttributesStart>0 && $posAttributesEnd>$posAttributesStart){
            $this->attributes = $this->parseAttributes(substr($rawText,$posAttributesStart,$posAttributesEnd-$posAttributesStart));
        }

    }

    private function parseAttributes(string $attributesString):array{
        $rest = "";
        $posEqualSign = strpos($attributesString,'=');
        $attrName = trim(substr($attributesString, 0, $posEqualSign));

        //$foundAttributes[] = AttributeDTO::create($attributeName,"");
        if($posEqualSign<strlen($attributesString)){
            $firstSign = strtolower($attributesString[$posEqualSign+1]);
            if($firstSign==='"' || $firstSign==="'"){
                $attrType='string';
            }else{
                if($firstSign==='t' || $firstSign==="f") {
                    $attrType = 'boolean';
                }else{
                    $attrType = 'number';
                }
            }
            $lengthValue = strpos(substr($attributesString,$posEqualSign+2),($attrType==='string'?$firstSign:' '));
            $attrValue = substr($attributesString, $posEqualSign+1,$lengthValue+2);
            $rest = substr($attributesString, $posEqualSign+1+strlen($attrValue));
        }
        if(!empty(trim($rest))){
            return array_merge([AttributeDTO::create($attrName,$attrValue)],$this->parseAttributes($rest));
        }
        return [AttributeDTO::create($attrName,$attrValue)];
    }

    /**
     * @return AttributeDTO[]
     */
    public function getAttributes():array{
        return $this->attributes;
    }
    public function hasChildFragments():bool {
        return (count($this->innerFragments)>0);
    }
    public function addAttribute(AttributeDTO $attribute):AbstractFragmentObject {
        $this->attributes[] = $attribute;
        return $this;
    }
    public function hasAttributes():bool{
        return (count($this->attributes)>0);
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
        foreach ($this->attributes as $attribute){$attributes.=$attribute.' ';}
        foreach ($this->innerFragments as $fragment){$inner.=$fragment;}

        return sprintf($this->getTagStart()."%s%s".$this->getTagEnd()."%s".$this->getTagStart().$this->getTagClose()."%s".$this->getTagEnd(),
            $this->name,$attributes,$inner,$this->name);
    }
    public function toHtmlList(){
        $attributes = "";
        $childs = "";
        $buttons = "";
        if($this->hasChildFragments()){
            $buttons .= "<button class='js-toggle-children badge btn-secondary mx-1'>Show childs</button>";
            $childs="<ul>";
            foreach ($this->innerFragments as $innerFragment){
                $childs.= $innerFragment->toHtmlList();
            }
            $childs.="</ul>";
        }
        if($this->hasAttributes()){
            $buttons .= "<button class='js-toggle-attribute badge btn-secondary ".($buttons===''?'mx-1':'')."'>Show attributes</button>";
            $attributes='<div class="js-attributes fragment-attributes">';
            foreach ($this->attributes as $attribute){
                $color = 'black';
                if($attribute->type==='boolean'){
                    $color = 'green';
                }elseif($attribute->type==='number'){
                    $color = 'orange';
                }

                $attributes.=sprintf('<span style="color:%s;">%s</span>',
                    $color,
                    $attribute);
            }
            $attributes.="</div>";
        }

        return sprintf('<li class="%s">[<small>%s</small>(%s)%s]%s%s</li>',
            $this->getSyncKey(),
            str_replace(__NAMESPACE__.'\\','',get_class($this)),
            $this->getName(),
            $attributes,
            $buttons,
            $childs);
    }

    public function getInnerFragments(){
        return $this->innerFragments;
    }
    public function setInnerFragments(array $fragments):AbstractFragmentObject {
        $this->innerFragments = $fragments;
        return $this;
    }

    public function getName():string {
        return $this->name;
    }
    public function setName(string $name):AbstractFragmentObject {
        $this->name = $name;
        return $this;
    }

    final public function getSyncKey():string{
        return $this->syncKey;
    }
    final public function setSyncKey(string $syncKey):AbstractFragmentObject{
        $this->syncKey = $syncKey;
        return $this;
    }
}