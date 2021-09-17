<?php

namespace CEhlers\Shortcode\DTO;

use CEhlers\Shortcode\TextFragment;

class ConverterResultDTO extends DTO
{
    /**
     * @var MessageDTO[]
     */
    public array $messages=[];
    public array $fragments=[];
    public array $unregulatedFragmentNames=[];
    public bool $hadError=false;

    public function __toString(){
        $result = "";
        foreach ($this->fragments as $fragment){$result.=$fragment;}
        return $result;
    }
}