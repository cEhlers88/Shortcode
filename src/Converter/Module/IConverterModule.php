<?php
namespace CEhlers\Shortcode\Converter\Module;

use CEhlers\Shortcode\DTO\DecisionSupportDTO;
use CEhlers\Shortcode\IDecisionSupportManager;
use CEhlers\Shortcode\TextFragment;

interface IConverterModule extends IDecisionSupportManager
{
    public function getDescription():string;
    public function getName():string;

    /**
     * @return DecisionSupportDTO
     */
    public function getDecisionSupports():array;

    public function hasDecisionSupports():bool;

    /**
     * @param TextFragment[] $fragments
     * @return TextFragment[]
     */
    public function precompileFragments(array $fragments):array;
}
