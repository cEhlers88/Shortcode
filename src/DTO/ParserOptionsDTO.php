<?php

namespace CEhlers\Shortcode\DTO;

class ParserOptionsDTO extends DTO
{
    public bool $composeParser;

    public static function create():ParserOptionsDTO {
        $dto = new ParserOptionsDTO();

        $dto->composeParser = true;

        return $dto;
    }
}
