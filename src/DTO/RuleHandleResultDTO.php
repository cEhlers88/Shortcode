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

    static public function create(TextFragment $fragment):RuleHandleResultDTO {
        $dto = new RuleHandleResultDTO();
        $dto->fragment = $fragment;
        return $dto;
    }
}