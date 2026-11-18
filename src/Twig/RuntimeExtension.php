<?php

/*
 * This file is part of the Leaky Blog demo, based on the Symfony Demo application.
 */

namespace App\Twig;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Attribute\AsTwigFunction;

/**
 * Shows how the page was served: worker or classic mode, and an id drawn when the
 * container creates this service. In worker mode the id stays the same until the
 * worker restarts; in classic mode it changes on every request. (FrankenPHP runs
 * PHP in threads of a single process, so the process id cannot tell workers apart.)
 */
final class RuntimeExtension
{
    private readonly string $bootId;

    public function __construct(
        #[Autowire(env: 'IGOR_RUNTIME_MODE')]
        private readonly string $runtimeMode,
    ) {
        $this->bootId = bin2hex(random_bytes(3));
    }

    #[AsTwigFunction('runtime_mode')]
    public function runtimeMode(): string
    {
        return $this->runtimeMode;
    }

    #[AsTwigFunction('boot_id')]
    public function bootId(): string
    {
        return $this->bootId;
    }
}
