<?php

namespace CEhlers\Shortcode\DTO;

class MappingRuleDTO
{
    public const UNSET_DEFAULT_VALUE = "UNSET_DEFAULT_VALUE";

    public string $originalName;
    public string $targetName;
    public string $defaultValue=self::UNSET_DEFAULT_VALUE;

    public bool $required;

    public static function create(string $originalName, string $targetName='', bool $required = false, $defaultValue = self::UNSET_DEFAULT_VALUE):MappingRuleDTO {
        $dto = new MappingRuleDTO();
        $dto->originalName = $originalName;
        $dto->targetName = ($targetName===''?$originalName:$targetName);
        $dto->required = $required;
        if($defaultValue!==self::UNSET_DEFAULT_VALUE){
            $dto->defaultValue = $defaultValue;
        }
        return $dto;
    }
}