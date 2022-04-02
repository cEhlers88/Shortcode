<?php

namespace CEhlers\Shortcode;

use CEhlers\Shortcode\DTO\AttributeDTO;
use function Symfony\Component\String\s;

abstract class AbstractFragmentObject extends TextFragment
{
    public const ATTRIBUTE_TYPE_DEFAULT = "default";
    public const ATTRIBUTE_TYPE_JSON = "json";
    private string $name;
    /**
     * @var AttributeDTO[]
     */
    private array $attributes;
    private array $innerFragments;

    abstract public function getTagStart():string;
    abstract public function getTagEnd():string;
    abstract public function getTagClose():string;
    abstract public function getFragmentTypeName():string;
    private $debug = 0;

    public function __construct(string $rawText="",string $name="") {
        parent::__construct($rawText);

        $this->name = $name;
        $this->attributes = [];
        $posAttributesStart = strpos($rawText,$name)+strlen($name);
        $posAttributesEnd = strpos($rawText,$this->getTagEnd());
        $posCloseSign = strpos($rawText, $this->getTagClose());

        $this->addMetaInfo('singleTag',($this->isSingleTag()?'true':'false'));

        if(!$this->isSingleTag()){
            $innerText = substr($rawText,$posAttributesEnd+strlen($this->getTagEnd()), strrpos($rawText,$this->getTagStart())-$posAttributesEnd-strlen($this->getTagEnd()));
            $this->innerFragments = ShortcodeParser::parse($innerText);
        }else{
            $this->innerFragments = [];
        }

        if($posAttributesStart>0 && $posAttributesEnd-$posAttributesStart>3){
            $this->attributes = $this->parseAttributes(substr($rawText,$posAttributesStart,$posAttributesEnd-$posAttributesStart));
        }
    }

    public function isSingleTag():bool {
        $posAttributesEnd = strpos($this->getRaw(),$this->getTagEnd());
        return substr($this->getRaw(), $posAttributesEnd-1,1) === $this->getTagClose();
    }

    private function parseAttributes(string $attributesString):array{
        switch ($this->getAttributesType()){
            case AbstractFragmentObject::ATTRIBUTE_TYPE_DEFAULT:
                $rest = "";
                $posEqualSign = strpos($attributesString,'=');
                $posNextSpace = strpos(trim($attributesString),' ');

                if(!$posEqualSign || ($posNextSpace && $posNextSpace<$posEqualSign)){
                    // flag attribute
                    $attrName = substr(trim($attributesString), 0, $posNextSpace);
                    $attrValue = "";
                    if($posNextSpace){
                        $rest = substr(trim($attributesString), $posNextSpace);
                    }else{
                        $attrName = trim($attributesString);
                        $rest = "";
                    }

                }else{
                    // normal attribute
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

                    if($this->debug>0){
                        echo "| aussen |". $attributesString;
                    }
                }

                if(!empty(trim($rest)) && strlen(trim($rest))>1 && trim($rest)!=='/'){
                    return array_merge([AttributeDTO::create($attrName,$attrValue)],$this->parseAttributes($rest));
                }
                return [AttributeDTO::create($attrName,$attrValue)];

                break;
            case AbstractFragmentObject::ATTRIBUTE_TYPE_JSON:
                $result = [];
                try{
                    $json = json_decode($attributesString);
                    foreach ($json as $name=>$value){
                        $result[] = AttributeDTO::create($name, $value);
                    }
                }catch (\Exception $exception){}

                return $result;

                break;
        }
        return [];
    }

    private function validateAttributes():AbstractFragmentObject {
        $attributeIndex = [];
        /** @var AttributeDTO[] $validatedAttributes */
        $validatedAttributes = [];

        foreach ($this->attributes as $attribute){
            if(isset($attributeIndex[$attribute->name])){
                $index = $attributeIndex[$attribute->name];
                $validatedAttributes[$index]->value .= " ".$attribute->value;
            }else{
                $attributeIndex[$attribute->name] = count($validatedAttributes);
                $validatedAttributes[] = $attribute;
            }
        }

        $this->attributes = $validatedAttributes;
        return $this;
    }

    public function getAttributesType():string{
        return AbstractFragmentObject::ATTRIBUTE_TYPE_DEFAULT;
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
        return $this->validateAttributes();
    }
    public function addAttributes(array $attributes):AbstractFragmentObject {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this->validateAttributes();
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
    public function setAttribute(AttributeDTO $attributeDTO):AbstractFragmentObject {
        $index = -1;
        $found = false;
        foreach ($this->attributes as $attribute){
            $index++;
            if($attribute->name===$attributeDTO->name){
                $found = true;
                $this->attributes[$index] = $attributeDTO;
                break;
            }
        }
        if(!$found){
            $this->addAttribute($attributeDTO);
        }
        return $this;
    }

    public function __toString()
    {
        $attributes = ($this->hasAttributes()?' ':'');
        $inner = '';

        if(count($this->attributes)>0){
            $seperator = ' ';
            if($this->getAttributesType()===self::ATTRIBUTE_TYPE_JSON){
                $attributes .= '{';
                $seperator = ',';
            }

            foreach ($this->attributes as $attribute){
                $attributes.=AttributeHelper::attributeToString($attribute,$this->getAttributesType()).$seperator;
            }

            if($this->getAttributesType()===self::ATTRIBUTE_TYPE_JSON){
                $attributes = substr($attributes,0,strlen($attributes)-1). '} ';
            }
        }

        foreach ($this->innerFragments as $fragment){$inner.=$fragment;}

        if($this->getMetaInfo('singleTag',"false")==="true"){
            return sprintf($this->getTagStart()."%s%s".$this->getTagClose().$this->getTagEnd(),
                $this->name,
                $attributes,
            );
        }else{
            return sprintf($this->getTagStart()."%s%s".$this->getTagEnd()."%s".$this->getTagStart().$this->getTagClose()."%s".$this->getTagEnd(),
                $this->name,
                $attributes,
                $inner,
                $this->name
            );
        }
    }
    public function toJson(): string
    {
        $childs =  [];
        foreach ($this->innerFragments as $fragment){
            $childs[] = json_decode($fragment->toJson());
        }
        return json_encode([
            'attributes' => $this->getAttributes(),
            'childs' => $childs,
            'fragmentType' => $this->getFragmentTypeName(),
            'metaInfos' => $this->getMetaInfos(),
            'name' => $this->getName(),
            'id' => $this->getId(),
            'syncKey' => $this->syncKey,
            'objectList' => $this->getInnerFragmentNames(true)

        ]);
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

                $attributes.=sprintf('<span style="color:%s;">%s </span>',
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

    public function addInnerFragment(TextFragment $fragmentObject):AbstractFragmentObject{
        $this->innerFragments[] = $fragmentObject;
        return $this;
    }
    public function getInnerFragments(){
        return $this->innerFragments;
    }
    public function getInnerFragment(int $index):?TextFragment {
        return $this->innerFragments[$index];
    }
    public function getInnerFragmentNames(bool $grouped = false, bool $includeCurrent = false):array{
        $result = ($includeCurrent ? ($grouped? [$this->getFragmentTypeName() => [$this->getName()]] : [$this->getName()]) : []);

        foreach ($this->getInnerFragments() as $innerFragment){
            if($innerFragment instanceof AbstractFragmentObject){
                if($grouped){
                    foreach ($innerFragment->getInnerFragmentNames($grouped,true) as $groupKey=>$groupValue){
                        foreach ($groupValue as $name){
                            if(!isset($result[$groupKey])){$result[$groupKey]=[];}
                            $result[$groupKey][] = $name;
                        }
                    }
                }else{
                    foreach ($this->getInnerFragmentNames(false, true) as $name){
                        $result[] = $name;
                    }
                }

            }
        }

        return $result;
    }
    public function getInnerText(): string
    {
        $inner = "";
        foreach ($this->innerFragments as $fragment){
            $inner .= $fragment->getInnerText();
        }
        return $inner;
    }

    public function setInnerFragments(array $fragments):AbstractFragmentObject {
        $this->innerFragments = $fragments;
        return $this;
    }
    public function hasChildClass(string $className):bool {
        foreach ($this->innerFragments as $innerFragment){
            if($innerFragment instanceof AbstractFragmentObject){
                if(get_class($innerFragment)===$className){return true;}
                if($innerFragment->hasChildClass($className)){return true;}
            }
        }
        return false;
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
