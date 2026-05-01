<?php

declare(strict_types=1);

namespace JeroenGerits\EmailTriage\Actions;

use Illuminate\Contracts\Container\Container;
use JeroenGerits\EmailTriage\Contracts\AiResponder;
use JeroenGerits\EmailTriage\Exceptions\EmailTriageException;

/**
 * Resolves and validates agent classes declared in package configuration.
 */
final readonly class ResolveConfiguredAgent
{
    private const string CONFIG_NAMESPACE = 'email-triage';

    public function __construct(
        private Container $container,
    ) {}

    public function fromConfig(string $configKey): AiResponder
    {
        $implementation = config(self::CONFIG_NAMESPACE.'.'.$configKey);

        if (! is_string($implementation) || $implementation === '') {
            throw new EmailTriageException('Email triage config ['.self::CONFIG_NAMESPACE.".{$configKey}] must be a class name.");
        }

        if (! class_exists($implementation)) {
            throw new EmailTriageException('Email triage config ['.self::CONFIG_NAMESPACE.".{$configKey}] must reference an existing class.");
        }

        $resolved = $this->container->make($implementation);

        if (! $resolved instanceof AiResponder) {
            throw new EmailTriageException('Email triage config ['.self::CONFIG_NAMESPACE.".{$configKey}] must implement ".AiResponder::class.'.');
        }

        return $resolved;
    }
}

