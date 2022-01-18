<?php

namespace CEhlers\Shortcode\Converter\Module;

use CEhlers\Shortcode\DecisionSupportManager;
use CEhlers\Shortcode\DTO\DecisionSupportDTO;

abstract class AbstractConverterModule extends DecisionSupportManager implements IConverterModule
{
    public function precompileFragments(array $fragments): array
    {
        return $fragments;
    }
}
