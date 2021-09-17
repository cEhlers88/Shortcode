<?php

namespace CEhlers\Shortcode\DTO;

class MessageDTO extends DTO
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

    public function getCssClass():string{
        switch ($this->type){
            case MessageDTO::MESSAGE_TYPE_DEBUG: return 'alert-debug'; break;
            case MessageDTO::MESSAGE_TYPE_INFO: return 'alert-info'; break;
            case MessageDTO::MESSAGE_TYPE_ERROR: return 'alert-error'; break;
            case MessageDTO::MESSAGE_TYPE_WARNING: return 'alert-warning'; break;
            default: return "alert-warning"; break;
        }
    }
}