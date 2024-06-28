<?php
namespace App\Service\CustomBBCodeDefinitions;

use JBBCode\CodeDefinition;
use JBBCode\CodeDefinitionBuilder;
use JBBCode\InputValidator;

class HeadingDefinition extends CodeDefinition
{
    public static function build(): CodeDefinition
    {
        return (new CodeDefinitionBuilder('h', '<h{option}>{param}</h{option}>'))
            ->setUseOption(true)
            ->build();
    }
}