<?php

namespace CEhlers\Shortcode\Converter;

use CEhlers\Shortcode\DTO\ConverterResultDTO;
use CEhlers\Shortcode\DTO\MessageDTO;
use CEhlers\Shortcode\Shortcode;
use CEhlers\Shortcode\ShortcodeParser;
use CEhlers\Shortcode\TextFragment;

class ShortcodeConverter
{
    private const EXECUTION_TYPE_CONVERT = "EXECUTION_TYPE_CONVERT";
    private const EXECUTION_TYPE_VALIDATE = "EXECUTION_TYPE_VALIDATE";
    private string $executionType;
    /**
     * @var IConverterRule
     */
    private array $rules;

    public function __construct(array $rules = []){
        $this->rules = $rules;
    }

    private function __execute(ConverterResultDTO $resultDTO, $shortcodes):ConverterResultDTO{
        if($shortcodes instanceof TextFragment){
            $parsedFragments = [$shortcodes];
        }elseif (is_array($shortcodes)){
            $parsedFragments = $shortcodes;
        }else{
            $parsedFragments = ShortcodeParser::parse($shortcodes);
        }

        foreach ($parsedFragments as $parsedFragment){
            if($parsedFragment instanceof Shortcode){
                foreach ($this->rules as $rule){
                    if($rule->canHandle($parsedFragment)){
                        $parsedFragment = $rule->handle($parsedFragment);
                    }
                }
            }
            $resultDTO->fragments[] = $parsedFragment;
        }

        return $resultDTO;
    }

    public function convert($shortcodes):ConverterResultDTO{
        $resultDto = new ConverterResultDTO();
        $this->executionType = self::EXECUTION_TYPE_CONVERT;

        return $this->__execute($resultDto, $shortcodes);
    }

    public function validate($shortcodes):ConverterResultDTO{
        $resultDto = new ConverterResultDTO();
        $this->executionType = self::EXECUTION_TYPE_VALIDATE;

        if(count($this->rules)===0){
            $resultDto->messages[] = MessageDTO::create(MessageDTO::MESSAGE_TYPE_WARNING,'No converter rules defined. Execution will stop.');
            $resultDto->hadError = true;
            return $resultDto;
        }

        return $this->__execute($resultDto, $shortcodes);
    }
}