<?php

namespace CEhlers\Shortcode\Converter;

use CEhlers\Shortcode\AbstractFragmentObject;
use CEhlers\Shortcode\DTO\ConvertAssignmentDTO;
use CEhlers\Shortcode\DTO\RuleHandleResultDTO;
use CEhlers\Shortcode\TextFragment;

interface IConverterRule
{
    public function getDescription():string;
    public function getName():string;
    public function canHandle(ConvertAssignmentDTO $convertAssignmentDTO):bool;
    public function handle(ConvertAssignmentDTO $convertAssignmentDTO):RuleHandleResultDTO;
}