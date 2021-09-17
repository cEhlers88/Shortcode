<?php

namespace cehlers\shortcode\DTO;

use CEhlers\Shortcode\TextFragment;

class RuleHandleResultDTO extends DTO
{
    /**
     * @var MessageDTO[]
     */
    public array $messages=[];
    /**
     * @var TextFragment
     */
    public array $fragment;
    public bool $hadError=false;
}