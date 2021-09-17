<?php

namespace CEhlers\Shortcode\DTO;

use CEhlers\Shortcode\TextFragment;

class RuleHandleResultDTO extends DTO
{
    /**
     * @var MessageDTO[]
     */
    public array $messages=[];
    public TextFragment $fragment;
    public bool $hadError=false;
}