<?php

namespace CEhlers\Shortcode\DTO;

use CEhlers\Shortcode\TextFragment;

class ConverterResultDTO extends HandleFragmentResultDTO
{
    public array $fragments=[];
    public ?array $originalFragments=null;
    public array $unregulatedFragmentNames=[];

    public function __toString(){
        $result = "";
        foreach ($this->fragments as $fragment){$result.=$fragment;}
        return $result;
    }
}
