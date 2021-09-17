<?php

namespace CEhlers\Shortcode\Converter;

use CEhlers\Shortcode\AbstractFragmentObject;
use CEhlers\Shortcode\DTO\ConvertAssignmentDTO;
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
     * @var IConverterRule[]
     */
    private array $rules;

    public function __construct(array $rules = []){
        $this->rules = $rules;
    }

    public function addRule(IConverterRule $converterRule):ShortcodeConverter {
        $this->rules[] = $converterRule;
        return $this;
    }
    public function getRules():array {
        return $this->rules;
    }

    /**
     * @param string[] $unregulatedFragmentNames
     * @return MessageDTO[]
     *
     */
    private function evalUnregulatedFragments(array $unregulatedFragmentNames):array{
        $result = [];
        foreach ($unregulatedFragmentNames as $name){
            $result[] = MessageDTO::create(MessageDTO::MESSAGE_TYPE_WARNING,'No rule defined for fragment "'.$name.'". Converter will return original value.');
        }
        return $result;
    }

    private function __execute(ConverterResultDTO $resultDTO, $shortcodes, ?AbstractFragmentObject $parentFragment=null, ?int $depth=0):ConverterResultDTO{
        if($shortcodes instanceof TextFragment){
            $parsedFragments = [$shortcodes];
        }elseif (is_array($shortcodes)){
            $parsedFragments = $shortcodes;
        }else{
            $parsedFragments = ShortcodeParser::parse($shortcodes);
        }

        foreach ($parsedFragments as $parsedFragment){
            if($parsedFragment instanceof AbstractFragmentObject){
                $ruleFound=false;
                $convertedInner = $this->__execute(new ConverterResultDTO(),$parsedFragment->getInnerFragments(), $parsedFragment,$depth+1);
                if($convertedInner->hadError){$resultDTO->hadError=true;}
                $resultDTO->unregulatedFragmentNames = array_merge($resultDTO->unregulatedFragmentNames,$convertedInner->unregulatedFragmentNames);
                $resultDTO->messages = array_merge($resultDTO->messages,$convertedInner->messages);

                $assignmentDTO = ConvertAssignmentDTO::create($parsedFragment,$parsedFragment->getInnerFragments(),$convertedInner->fragments);
                $assignmentDTO->parentFragmentObject = $parentFragment;
                foreach ($this->rules as $rule){
                    if($rule->canHandle($assignmentDTO)){
                        $ruleFound = true;
                        $syncKey = $parsedFragment->getSyncKey();
                        $handleResult = $rule->handle($assignmentDTO);
                        if($handleResult->hadError){$resultDTO->hadError=true;}
                        $parsedFragment = $handleResult->fragment;
                        $resultDTO->messages = array_merge($resultDTO->messages, $handleResult->messages);

                        if($parsedFragment instanceof AbstractFragmentObject){
                            $parsedFragment->setSyncKey($syncKey);
                        }
                    }
                }
                if(!$ruleFound){
                    if($this->executionType===self::EXECUTION_TYPE_VALIDATE){
                        $resultDTO->unregulatedFragmentNames[] = $parsedFragment->getName();
                    }
                }
            }
            $resultDTO->fragments[] = $parsedFragment;
        }
        if(is_null($parentFragment) && count($resultDTO->unregulatedFragmentNames)>0){
            $resultDTO->messages = array_merge($resultDTO->messages, $this->evalUnregulatedFragments(array_unique($resultDTO->unregulatedFragmentNames)));
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