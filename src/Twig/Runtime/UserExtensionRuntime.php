<?php

namespace App\Twig\Runtime;

use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\RuntimeExtensionInterface;

class UserExtensionRuntime implements RuntimeExtensionInterface
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function getUser()
    {
        return $this->security->getUser();
    }
}
