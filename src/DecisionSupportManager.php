<?php

namespace CEhlers\Shortcode;

use CEhlers\Shortcode\DTO\DecisionSupportDTO;

class DecisionSupportManager
{
    /** @var DecisionSupportDTO[]  */
    private array $decisionSupports = [];

    public function addDecisionSupport(DecisionSupportDTO $decisionSupport):DecisionSupportManager {
        $this->decisionSupports[] = $decisionSupport;
        return $this;
    }
    /**
     * @return DecisionSupportDTO[]
     */
    public function getDecisionSupports(): array
    {
        return $this->decisionSupports;
    }

    public function getDecisionSupportByName(string $name):?DecisionSupportDTO {
        foreach ($this->decisionSupports as $decisionSupport){
            if($decisionSupport->name === $name){
                return $decisionSupport;
            }
        }
    }

    public function getDecisionSupportValue(string $name): ?string
    {
        foreach ($this->decisionSupports as $decisionSupport){
            if($decisionSupport->name === $name){
                return $decisionSupport->value;
            }
        }
    }

    public function hasDecisionSupports(): bool
    {
        return count($this->decisionSupports) > 0;
    }

    public function setDecisionSupportValue(string $name, string $value):DecisionSupportManager {
        foreach ($this->decisionSupports as $decisionSupport){
            if($decisionSupport->name === $name){
                $decisionSupport->value = $value;
                break;
            }
        }
        return $this;
    }
}
