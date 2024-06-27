<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\UserExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class UserExtension extends AbstractExtension
{
    private UserExtensionRuntime $runtime;

    public function __construct(UserExtensionRuntime $runtime)
    {
        $this->runtime = $runtime;
    }

    public function getFunctions(): array
    {
        return [new TwigFunction('get_user', [$this->runtime, 'getUser']),];
    }
}
