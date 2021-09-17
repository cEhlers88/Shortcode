<?php

namespace CEhlers\Shortcode\DTO;

use CEhlers\Shortcode\AbstractFragmentObject;

class ConvertAssignmentDTO extends DTO
{
    public array $convertedInnerFragments;
    public AbstractFragmentObject $fragmentObject;
    public array $innerFragments;
    public ?AbstractFragmentObject $parentFragmentObject=null;

    public static function create(
        AbstractFragmentObject $fragmentObject,
        array $innerFragments,
        array $convertedInnerFragments
    ):ConvertAssignmentDTO {
        $dto = new ConvertAssignmentDTO();

        $dto->fragmentObject = $fragmentObject;
        $dto->convertedInnerFragments = $convertedInnerFragments;
        $dto->innerFragments = $innerFragments;

        return $dto;
    }

    public function hasParent():bool {
        return ($this->parentFragmentObject instanceof AbstractFragmentObject);
    }
}