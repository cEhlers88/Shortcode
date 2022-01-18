<?php

namespace CEhlers\Shortcode;

use CEhlers\Shortcode\DTO\AttributeDTO;

class AttributeHelper
{
    public static function attributeToString(AttributeDTO $attribute, string $outputType = AbstractFragmentObject::ATTRIBUTE_TYPE_DEFAULT):string{
        if($outputType===AbstractFragmentObject::ATTRIBUTE_TYPE_DEFAULT){
            return $attribute->name.($attribute->type==='flag'?'':'='.($attribute->type==='string'?'"':'').$attribute->value.($attribute->type==='string'?'"':''));
        }else{
            return '"'.$attribute->name.'":'.($attribute->type==='flag'?'true':''.($attribute->type==='string'?'"':'').$attribute->value.($attribute->type==='string'?'"':''));
        }
    }
}
