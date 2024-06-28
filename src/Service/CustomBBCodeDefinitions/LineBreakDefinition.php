<?php

namespace App\Service\CustomBBCodeDefinitions;

use JBBCode\CodeDefinition;
use JBBCode\CodeDefinitionBuilder;

class LineBreakDefinition extends CodeDefinition
{
    public static function build(): CodeDefinition
    {
        return (new CodeDefinitionBuilder('br', '<br />'))
            ->setParseContent(false)
            ->build();
    }
}