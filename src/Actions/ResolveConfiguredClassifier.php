<?php

declare(strict_types=1);

namespace JeroenGerits\EmailTriage\Actions;

use Illuminate\Contracts\Container\Container;
use JeroenGerits\EmailTriage\Contracts\EmailTriageClassifier;
use JeroenGerits\EmailTriage\Exceptions\EmailTriageException;

/**
 * Resolves and validates classifier classes declared in package configuration.
 */
final readonly class ResolveConfiguredClassifier
{
    private const string CONFIG_NAMESPACE = 'email-triage';

    public function __construct(
        private Container $container,
    ) {}

    public function fromConfig(string $configKey): EmailTriageClassifier
    {
        $implementation = config(self::CONFIG_NAMESPACE.'.'.$configKey);

        if (! is_string($implementation) || $implementation === '') {
            throw new EmailTriageException('Email triage config ['.self::CONFIG_NAMESPACE.".{$configKey}] must be a class name.");
        }

        if (! class_exists($implementation)) {
            throw new EmailTriageException('Email triage config ['.self::CONFIG_NAMESPACE.".{$configKey}] must reference an existing class.");
        }

        $resolved = $this->container->make($implementation);

        if (! $resolved instanceof EmailTriageClassifier) {
            throw new EmailTriageException('Email triage config ['.self::CONFIG_NAMESPACE.".{$configKey}] must implement ".EmailTriageClassifier::class.'.');
        }

        return $resolved;
    }
}
