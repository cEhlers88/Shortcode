<?php

namespace CEhlers\Shortcode\DTO;

class HandleFragmentResultDTO extends DTO
{
    /**
     * @var MessageDTO[]
     */
    public array $messages=[];
    public bool $hadError=false;
}