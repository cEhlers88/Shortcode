<?php

namespace CEhlers\Shortcode\Converter;

use CEhlers\Shortcode\DTO\ConvertAssignmentDTO;
use CEhlers\Shortcode\TextFragment;

abstract class AbstractConverterRule implements IConverterRule
{
    public function getDescription(): string
    {
        return "Converts \"".$this->getName()."\"";
    }
}