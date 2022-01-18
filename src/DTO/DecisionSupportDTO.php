<?php

namespace CEhlers\Shortcode\DTO;

class DecisionSupportDTO extends DTO
{
    public const DecisionSupportType_Select = "select";
    public const DecisionSupportType_Text = "text";
    public const DecisionSupportType_YesNo = "yesno";

    public string $name = "undefined";
    public string $displayName = "";
    public string $type = self::DecisionSupportType_Text;
    public array $options = [];
    public string $value = "";

    public static function create(
        string $name,
        string $displayName = "",
        string $type = DecisionSupportDTO::DecisionSupportType_Text,
        string $value = "",
        array $options = []
    ):DecisionSupportDTO{
        $dto = new DecisionSupportDTO();

        $dto->name = $name;
        $dto->displayName = ($displayName === '' ? $name : $displayName);
        $dto->type = $type;
        $dto->value = $value;
        $dto->options = $options;

        return $dto;
    }
}
