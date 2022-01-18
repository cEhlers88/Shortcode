<?php

namespace CEhlers\Shortcode;

use CEhlers\Shortcode\DTO\DecisionSupportDTO;

interface IDecisionSupportManager
{
    public function setDecisionSupportValue(string $name, string $value):DecisionSupportManager;

    public function addDecisionSupport(DecisionSupportDTO $decisionSupport):DecisionSupportManager;

    public function getDecisionSupports(): array;

    public function getDecisionSupportByName(string $name):?DecisionSupportDTO;

    public function getDecisionSupportValue(string $name): ?string;

    public function hasDecisionSupports(): bool;
}
