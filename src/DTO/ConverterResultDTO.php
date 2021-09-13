<?php

namespace CEhlers\Shortcode\DTO;

use CEhlers\Shortcode\TextFragment;

class ConverterResultDTO
{
    /**
     * @var MessageDTO[]
     */
    public array $messages=[];
    public array $fragments=[];
    public bool $hadError=false;

    public function __toString(){
        $result = "";
        foreach ($this->fragments as $fragment){$result.=$fragment;}
        return $result;
    }
}