<?php

namespace CEhlers\Shortcode\DTO;

class AttributeDTO extends DTO
{
    public string $name;
    public $value;
    public string $type;

    public static function create($name, $value):AttributeDTO {
        $dto = new AttributeDTO();

        $dto->name = $name;
        if(empty($value)){
            $dto->type = 'flag';
        }else{
            $dto->type = str_contains(strtolower($value),'true') || str_contains(strtolower($value),'false') ? 'boolean' :(is_numeric($value)?'number':'string');
            $dto->value = str_replace(['"',"'"],['',''],$value);// $value;// ($dto->type==='string' && ($value[0]==='"' || $value[0]==="'"))? substr($value,1,strlen($value)-2) : $value;
            if($dto->type==='boolean'){
                $dto->value = str_contains(strtolower($value),'true');
            }
        }
        if($dto->name==='bb_built'){
            /*echo "<pre>";
            var_dump($dto);
            die();*/
        }
        //die($dto->name);
        return $dto;
    }

    public function __toString(){
        return $this->name.($this->type==='flag'?'':'='.($this->type==='string'?'"':'').$this->value.($this->type==='string'?'"':''));
    }
}