<?php

namespace App\Service;

use App\Service\CustomBBCodeDefinitions\HeadingDefinition;
use App\Service\CustomBBCodeDefinitions\LineBreakDefinition;
use JBBCode\Parser;
use JBBCode\DefaultCodeDefinitionSet;

class BBCodeParser
{
    private $parser;

    public function __construct()
    {
        $this->parser = new Parser();
        $this->parser->addCodeDefinitionSet(new DefaultCodeDefinitionSet());
        $this->parser->addCodeDefinition(LineBreakDefinition::build());
        $this->parser->addCodeDefinition(HeadingDefinition::build());
    }

    public function parse($string, $safe = true) : string
    {
        if ($safe) {
            $string = htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
        }
        $this->parser->parse($string);
        return nl2br($this->parser->getAsHtml());
    }
}