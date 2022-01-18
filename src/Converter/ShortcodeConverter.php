<?php

namespace CEhlers\Shortcode\Converter;

use CEhlers\Shortcode\AbstractFragmentObject;
use CEhlers\Shortcode\Converter\Module\IConverterModule;
use CEhlers\Shortcode\DecisionSupportManager;
use CEhlers\Shortcode\DTO\AttributeDTO;
use CEhlers\Shortcode\DTO\ConvertAssignmentDTO;
use CEhlers\Shortcode\DTO\ConverterResultDTO;
use CEhlers\Shortcode\DTO\DecisionSupportDTO;
use CEhlers\Shortcode\DTO\MappingRuleDTO;
use CEhlers\Shortcode\DTO\MessageDTO;
use CEhlers\Shortcode\Shortcode;
use CEhlers\Shortcode\ShortcodeParser;
use CEhlers\Shortcode\TextFragment;

class ShortcodeConverter extends DecisionSupportManager
{
    private const EXECUTION_TYPE_CONVERT = "EXECUTION_TYPE_CONVERT";
    private const EXECUTION_TYPE_VALIDATE = "EXECUTION_TYPE_VALIDATE";
    private string $executionType;
    private ?\mysqli $connection;

    /**
     * @var IConverterRule[]
     */
    private array $rules;

    //private DecisionSupportManager $decisionSupportManager;

    private ?AbstractConverterRule $fallbackRule;

    /** @var IConverterModule[]  */
    private array $converterModules = [];

    public function __construct(array $rules = [], ?AbstractConverterRule $fallbackRule = null){
        $this->rules = $rules;
        $this->fallbackRule = $fallbackRule;
        $this->connection = null;

        $this->addDecisionSupport(DecisionSupportDTO::create(
            'precompile_ltgt',
            '&lt; und &gt; vorkompilieren',
            DecisionSupportDTO::DecisionSupportType_YesNo,
            'false',
        ));
        $this->addDecisionSupport(DecisionSupportDTO::create(
            'ignore_disabled',
            'Deaktivierte ignorieren',
            DecisionSupportDTO::DecisionSupportType_YesNo,
            'true',
        ));
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

    /**
     * @param MessageDTO[] $messages
     * @return MessageDTO[]
     */
    private function unifyMessagesByKeys(array $messages):array {
        $usedKeys = [];
        $result = [];
        foreach ($messages as $messageDTO){
            if($messageDTO->key!==''){
                if(in_array($messageDTO->key,$usedKeys)){continue;}
                $usedKeys[] = $messageDTO->key;
            }
            $result[] =  $messageDTO;
        }
        return $result;
    }

    private function __handleRule(
        AbstractFragmentObject $originalFragment,
        AbstractConverterRule $rule,
        ConvertAssignmentDTO $assignmentDTO,
        ConverterResultDTO $resultDTO
    ):ConverterResultDTO {
        $handleResult = $rule->handle($assignmentDTO);
        if($handleResult->hadError){$resultDTO->hadError=true;}

        if($handleResult->fragment instanceof AbstractFragmentObject){
            $attrMapping = $this->mapAttributes($rule->getAttributeMappings(),$originalFragment,$handleResult->fragment);
            $parsedFragment = $attrMapping->fragments[0];
        }else{
            $parsedFragment = $handleResult->fragment;
        }

        $validateResult = $rule->validate($handleResult,$assignmentDTO);

        $resultDTO->messages = array_merge($resultDTO->messages, $attrMapping->messages);
        $resultDTO->messages = array_merge($resultDTO->messages, $handleResult->messages);
        $resultDTO->messages = $this->unifyMessagesByKeys(array_merge($resultDTO->messages, $validateResult->messages));

        $resultDTO->fragments = [$parsedFragment];

        return $resultDTO;
    }

    private function __execute(ConverterResultDTO $resultDTO, $shortcodes, ?AbstractFragmentObject $parentFragment=null, ?int $depth=0):ConverterResultDTO{
        if($shortcodes instanceof TextFragment){
            $parsedFragments = [$shortcodes];
        }elseif (is_array($shortcodes)){
            $parsedFragments = $shortcodes;
        }else{
            if($this->getDecisionSupportByName('precompile_ltgt')->value==='true'){
                $shortcodes = str_replace(['&lt;','&gt;'],['<','>'],$shortcodes);
            }

            $parsedFragments = ShortcodeParser::parse($shortcodes);
        }

        foreach ($this->converterModules as $converterModule){
            $parsedFragments = $converterModule->precompileFragments($parsedFragments);
        }

        if(is_null($resultDTO->originalFragments)){
            $resultDTO->originalFragments = $parsedFragments;
        }

        foreach ($parsedFragments as $parsedFragment){
            $syncKey = $parsedFragment->getSyncKey();

            if($parsedFragment instanceof AbstractFragmentObject){
                $ruleFound=false;
                $originalFragment = $parsedFragment;
                $convertedInner = $this->__execute(new ConverterResultDTO(),$parsedFragment->getInnerFragments(), $parsedFragment,$depth+1);
                if($convertedInner->hadError){$resultDTO->hadError=true;}

                $resultDTO->unregulatedFragmentNames = array_merge($resultDTO->unregulatedFragmentNames,$convertedInner->unregulatedFragmentNames);
                $resultDTO->messages = array_merge($resultDTO->messages,$convertedInner->messages);

                $assignmentDTO = ConvertAssignmentDTO::create($parsedFragment,$parsedFragment->getInnerFragments(),$convertedInner->fragments);
                $assignmentDTO->parentFragmentObject = $parentFragment;
                $runRules = true;

                if($assignmentDTO->fragmentObject->getAttributeValue('disabled','off')==='on'){
                    // disabled
                    $parsedFragment->addMetaInfo('disabled','true');

                    if($this->getDecisionSupportByName('ignore_disabled')->value==='true'){
                        $runRules = false;
                    }
                }

                if($runRules){
                    foreach ($this->rules as $rule){
                        if(!$rule->canHandle($assignmentDTO)){continue;}

                        $ruleFound = true;
                        $handleResult = $rule->setConnection($this->connection)->handle($assignmentDTO);
                        if($handleResult->hadError){$resultDTO->hadError=true;}

                        $attrMapping = $this->mapAttributes($rule->getAttributeMappings(),$originalFragment,$handleResult->fragment);
                        $parsedFragment = $attrMapping->fragments[0];

                        $validateResult = $rule->validate($handleResult,$assignmentDTO);

                        $resultDTO->messages = array_merge($resultDTO->messages, $attrMapping->messages);
                        $resultDTO->messages = array_merge($resultDTO->messages, $handleResult->messages);
                        $resultDTO->messages = $this->unifyMessagesByKeys(array_merge($resultDTO->messages, $validateResult->messages));

                    }

                    if($this->fallbackRule instanceof AbstractConverterRule && !$ruleFound){
                        if($this->fallbackRule->canHandle($assignmentDTO)){
                            $ruleFound = true;
                            $handleResult = $this->fallbackRule->setConnection($this->connection)->handle($assignmentDTO);
                            if($handleResult->hadError){$resultDTO->hadError=true;}

                            $attrMapping = $this->mapAttributes($this->fallbackRule->getAttributeMappings(),$originalFragment,$handleResult->fragment);
                            $parsedFragment = $attrMapping->fragments[0];

                            $validateResult = $this->fallbackRule->validate($handleResult,$assignmentDTO);

                            $resultDTO->messages = array_merge($resultDTO->messages, $attrMapping->messages);
                            $resultDTO->messages = array_merge($resultDTO->messages, $handleResult->messages);
                            $resultDTO->messages = $this->unifyMessagesByKeys(array_merge($resultDTO->messages, $validateResult->messages));
                        }
                    }

                    if(!$ruleFound){
                        $parsedFragment->addMetaInfo('rule-found','false');
                        if($this->executionType===self::EXECUTION_TYPE_VALIDATE){
                            $resultDTO->unregulatedFragmentNames[] = $parsedFragment->getName();
                        }
                    }
                }

            }
            $parsedFragment->setSyncKey($syncKey);

            $messageKeys = [];
            foreach($resultDTO->messages as $message){
                $messageKeys[] = $message->key;
            }
            $parsedFragment->addMetaInfo('messageKeys',json_encode($messageKeys));

            $resultDTO->fragments[] = $parsedFragment;

        }
        if(is_null($parentFragment) && count($resultDTO->unregulatedFragmentNames)>0){
            $resultDTO->messages = $this->unifyMessagesByKeys(
                array_merge(
                    $resultDTO->messages,
                    $this->evalUnregulatedFragments(array_unique($resultDTO->unregulatedFragmentNames)
                    )
                )
            );
        }

        return $resultDTO;
    }

    /**
     * @param MappingRuleDTO[] $ruleDTOS
     */
    private function mapAttributes(array $ruleDTOS, AbstractFragmentObject $originalFragment, AbstractFragmentObject $targetFragment):ConverterResultDTO{
        $resultDto = new ConverterResultDTO();
        $undefinedKey = uniqid();
        foreach ($ruleDTOS as $ruleDTO){
            $value = $targetFragment->getAttributeValue($ruleDTO->targetName,$undefinedKey);
            $isUndefined = ($value === $undefinedKey);
            if(!$isUndefined){continue;}

            $value = $originalFragment->getAttributeValue($ruleDTO->originalName,$undefinedKey);
            $isUndefined = ($value === $undefinedKey);
            if($ruleDTO->required && $isUndefined){
                if($ruleDTO->defaultValue === MappingRuleDTO::UNSET_DEFAULT_VALUE){
                    $resultDto->messages[] =  MessageDTO::create(
                        MessageDTO::MESSAGE_TYPE_ERROR,
                        sprintf("<b>%s</b> missing required attribute <b>%s</b> in <b>%s</b> to create <b>%s</b>",
                        $targetFragment->getName(),
                        $ruleDTO->originalName,
                        $originalFragment->getName(),
                        $ruleDTO->targetName
                    ),'MISSING_ATTRIBUTE_'.$ruleDTO->originalName);
                }else{
                    $value = $ruleDTO->defaultValue;
                }
            }

            $isUndefined = ($value === $undefinedKey);
            if(!$isUndefined){
                $targetFragment->addAttribute(AttributeDTO::create($ruleDTO->targetName,$value));
            }
        }
        $resultDto->fragments = [$targetFragment];

        return $resultDto;
    }

    public function addModule(IConverterModule $converterModule):ShortcodeConverter {
        $this->converterModules[] = $converterModule;

        return $this;
    }

    public function getDecisionSupports():array{
        $result = ["general"=>parent::getDecisionSupports()];

        foreach ($this->converterModules as $converterModule){
            if($converterModule->hasDecisionSupports()){
                $result['Module "'.$converterModule->getName().'"'] = $converterModule->getDecisionSupports();
            }
        }

        foreach ($this->getRules() as $rule){
            if($rule->hasDecisionSupports()){
                $result[$rule->getName()] = $rule->getDecisionSupports();
            }
        }

        return $result;
    }

    public function setDecisionSupportValue(string $name, string $value):ShortcodeConverter {
        parent::setDecisionSupportValue($name, $value);
        foreach ($this->converterModules as $converterModule){
            $converterModule->setDecisionSupportValue($name, $value);
        }
        foreach ($this->rules as $rule){
            $rule->setDecisionSupportValue($name, $value);
        }
        return $this;
    }

    public function convertByDatabaseId(int $id):ConverterResultDTO {
        mysqli_set_charset( $this->connection, 'utf8');
        $result = mysqli_query($this->connection, "SELECT post_content FROM wp_posts WHERE ID=".$id.";");
        $result = $result->fetch_row()[0];
        return $this->convert($result);
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

    public function connectDB(string $server, string $database, string $username, string $password):ShortcodeConverter {

        $this->connection = mysqli_connect($server, $username,$password, $database);

        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }

        return $this;
    }
}
