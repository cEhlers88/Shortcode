<?php

namespace CEhlers\Shortcode\DTO;

class AttributeDTO
{
    public string $name;
    public string $value;
    public string $type;

    public static function create($name, $value):AttributeDTO {
        $dto = new AttributeDTO();

        $dto->name = $name;
        if(empty($value)){
            $dto->type = 'flag';
        }else{
            $dto->type = str_contains(strtolower($value),'true') || str_contains(strtolower($value),'false') ? 'boolean' : ($value[0]==='"' || $value[0]==="'"?'string':'number');
            $dto->value = ($value[0]==='"' || $value[0]==="'")? substr($value,1,strlen($value)-2) : $value;
        }

        return $dto;
    }

    public function __toString(){
        return $this->name.($this->type==='flag'?'':'='.($this->type==='string'?'"':'').$this->value.($this->type==='string'?'"':''));
    }
}