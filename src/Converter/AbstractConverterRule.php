<?php

namespace CEhlers\Shortcode\Converter;

use CEhlers\Shortcode\DecisionSupportManager;
use CEhlers\Shortcode\DTO\ConvertAssignmentDTO;
use CEhlers\Shortcode\DTO\MappingRuleDTO;
use CEhlers\Shortcode\DTO\RuleHandleResultDTO;
use CEhlers\Shortcode\TextFragment;

abstract class AbstractConverterRule extends DecisionSupportManager implements IConverterRule
{
    /** @var MappingRuleDTO[] */
    private array $attributeMappings = [];

    private $connection;

    public function getConnection()
    {
        return $this->connection;
    }
    public function setConnection($connection):AbstractConverterRule
    {
        $this->connection = $connection;
        return $this;
    }

    public function getDescription(): string
    {
        return "Converts \"".$this->getName()."\"";
    }

    public function validate(RuleHandleResultDTO $handleResultDTO,ConvertAssignmentDTO $convertAssignmentDTO):RuleHandleResultDTO {
        return $handleResultDTO;
    }

    public function addAttributeMappingRule(MappingRuleDTO $mappingRuleDTO):AbstractConverterRule {
        $this->attributeMappings[] = $mappingRuleDTO;
        return $this;
    }
    public function getAttributeMappings(): array{
        return $this->attributeMappings;
    }

    public function getMediaByUrl(string $url) {
        if($this->connection===null){ return null; }
        $posWpContent = strpos($url,'wp-content/');
        if($posWpContent>-1){
            $url =  substr($url, $posWpContent);
        }

        $result = mysqli_query($this->connection, 'SELECT * FROM wp_posts WHERE guid LIKE "%'.$url.'%";');
        $obj = $result->fetch_object();
        return $obj;
    }
}
