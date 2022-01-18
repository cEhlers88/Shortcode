<?php

namespace CEhlers\Shortcode\Converter;

use CEhlers\Shortcode\AbstractFragmentObject;
use CEhlers\Shortcode\DTO\ConvertAssignmentDTO;
use CEhlers\Shortcode\DTO\DecisionSupportDTO;
use CEhlers\Shortcode\DTO\MappingRuleDTO;
use CEhlers\Shortcode\DTO\RuleHandleResultDTO;
use CEhlers\Shortcode\TextFragment;

interface IConverterRule
{
    public function getConnection();
    public function setConnection($connection);

    public function getDescription():string;
    public function getName():string;
    public function canHandle(ConvertAssignmentDTO $convertAssignmentDTO):bool;
    public function handle(ConvertAssignmentDTO $convertAssignmentDTO):RuleHandleResultDTO;
    public function validate(RuleHandleResultDTO $handleResultDTO,ConvertAssignmentDTO $convertAssignmentDTO):RuleHandleResultDTO;

    public function getMediaByUrl(string $url);
    /**
     * @return MappingRuleDTO[]
     */
    public function getAttributeMappings():array;
}
