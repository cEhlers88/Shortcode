<?php

namespace CEhlers\Shortcode;

use CEhlers\Shortcode\DTO\AttributeDTO;

abstract class AbstractFragmentObject extends TextFragment
{
    private array $metaInfos;
    private string $name;
    /**
     * @var AttributeDTO[]
     */
    private array $attributes;
    private array $innerFragments;

    abstract public function getTagStart():string;
    abstract public function getTagEnd():string;
    abstract public function getTagClose():string;

    public function __construct(string $rawText="",string $name="") {
        parent::__construct($rawText);
        $this->metaInfos = [];
        $this->name = $name;
        $this->attributes = [];
        $posAttributesStart = strpos($rawText,' ');
        $posAttributesEnd = strpos($rawText,$this->getTagEnd());
        $posCloseSign = strpos($rawText, $this->getTagClose());
        $this->addMetaInfo('singleTag',($posCloseSign===$posAttributesEnd-1?'true':'false'));
        $innerText = substr($rawText,$posAttributesEnd+1, strrpos($rawText,$this->getTagStart())-$posAttributesEnd-1);
        $this->innerFragments = ShortcodeParser::parse($innerText);

        if($posAttributesStart>0 && $posAttributesEnd-$posAttributesStart>3){
            $this->attributes = $this->parseAttributes(substr($rawText,$posAttributesStart,$posAttributesEnd-$posAttributesStart));
        }

    }

    private function parseAttributes(string $attributesString):array{
        $rest = "";
        $posEqualSign = strpos($attributesString,'=');
        $attrName = trim(substr($attributesString, 0, $posEqualSign));

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
        if(!empty(trim($rest)) && strlen($rest)>1){
            return array_merge([AttributeDTO::create($attrName,$attrValue)],$this->parseAttributes($rest));
        }
        return [AttributeDTO::create($attrName,$attrValue)];
    }

    final public function addMetaInfo(string $metaKey, string $metaValue):AbstractFragmentObject {
        $this->metaInfos[$metaKey] = $metaValue;
        return $this;
    }
    final public function getMetaInfo(string $metaKey,string $defaultValue=""):string {
        if(array_key_exists($metaKey,$this->metaInfos)){
            return $this->metaInfos[$metaKey];
        }
        return $defaultValue;
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
        $hasGrandChilds = false;

        if($this->hasChildFragments()){
            $buttons .= "<button data-type='children' class='js-toggle-children badge btn-secondary mx-1'>Show children</button>";
            $childs="<ul>";
            foreach ($this->innerFragments as $innerFragment){
                if($innerFragment instanceof AbstractFragmentObject && $innerFragment->hasChildFragments()){$hasGrandChilds = true;}
                $childs.= $innerFragment->toHtmlList();
            }
            $childs.="</ul>";
        }
        if($hasGrandChilds){
            $buttons .= "<button data-type='all' class='js-toggle-children badge btn-secondary mx-1'>Show all children</button>";
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
            $this->getSyncKey().($this->getMetaInfo('rule-found','true')!=='true'?' no-converter-rule':'').' ',
            str_replace(__NAMESPACE__.'\\','',get_class($this)),
            $this->getName(),
            $attributes,
            $buttons,
            $childs);
    }

    public function getInnerFragments(){
        return $this->innerFragments;
    }
    public function getInnerFragment(int $index):?TextFragment {
        return $this->innerFragments[$index];
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

    final public function setSyncKey(string $syncKey):TextFragment{
        $currentSyncKey = $this->getSyncKey();
        $this->syncKey = $syncKey;
        foreach ($this->innerFragments as $innerFragment){
            if($innerFragment->getSyncKey()===$currentSyncKey){
                $innerFragment->setSyncKey($syncKey);
            }
        }
        return $this;
    }

}