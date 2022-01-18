<?php

namespace CEhlers\Shortcode;

class DomElement extends AbstractFragmentObject
{
    public const TYPE_COMMENT = "Comment";
    public const TYPE_NODE = "Node";

    private string $type = DomElement::TYPE_NODE;

    public function getAttributesType(): string
    {
        return ($this->type===DomElement::TYPE_NODE?AbstractFragmentObject::ATTRIBUTE_TYPE_DEFAULT:AbstractFragmentObject::ATTRIBUTE_TYPE_JSON);
    }

    public function evalRaw(string $rawText): TextFragment
    {
        if(substr($rawText,1,1)==='!'){
            $this->type = DomElement::TYPE_COMMENT;
        }
        return parent::evalRaw($rawText);
    }

    public function getTagStart(): string
    {
        return ($this->type===DomElement::TYPE_NODE ? "<" : "<!--");
    }

    public function getTagEnd(): string
    {
        return ($this->type===DomElement::TYPE_NODE ? ">" : "-->");
    }

    public function getTagClose(): string
    {
        return "/";
    }

    public function getFragmentTypeName(): string
    {
        return "HTML DOM-".$this->type;
    }

    public function getName(): string
    {
        return str_replace(["<",">"],["&lt;","&gt;"],parent::getName());
    }

    public function getType():string {
        return  $this->type;
    }

    public function isSingleTag(): bool
    {
        if($this->getAttributesType()===self::ATTRIBUTE_TYPE_DEFAULT){
            return parent::isSingleTag();
        }else{
            return (substr($this->getRaw(), strlen($this->getRaw())-4,1)==='/');
        }
    }
}
