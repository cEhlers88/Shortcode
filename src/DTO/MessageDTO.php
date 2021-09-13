<?php

namespace CEhlers\Shortcode\DTO;

class MessageDTO
{
    public const MESSAGE_TYPE_DEBUG = "DEBUG";
    public const MESSAGE_TYPE_INFO = "INFORMATION";
    public const MESSAGE_TYPE_WARNING = "WARNING";
    public const MESSAGE_TYPE_ERROR = "ERROR";

    public string $text;
    public string $type;

    public static function create(string $type, string $text):MessageDTO {
        $dto =  new MessageDTO();
        $dto->type = $type;
        $dto->text = $text;
        return $dto;
    }
}