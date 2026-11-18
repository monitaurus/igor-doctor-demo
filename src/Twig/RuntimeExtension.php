<?php

/*
 * This file is part of the Leaky Blog demo, based on the Symfony Demo application.
 */

namespace App\Twig;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Attribute\AsTwigFunction;

/**
 * Shows how the page was served (worker or classic mode, and the PHP process id),
 * so a visitor can see that two browsers are served by the same long-lived worker.
 */
final class RuntimeExtension
{
    public function __construct(
        #[Autowire(env: 'IGOR_RUNTIME_MODE')]
        private readonly string $runtimeMode,
    ) {
    }

    #[AsTwigFunction('runtime_mode')]
    public function runtimeMode(): string
    {
        return $this->runtimeMode;
    }

    #[AsTwigFunction('worker_pid')]
    public function workerPid(): int
    {
        return (int) getmypid();
    }
}
