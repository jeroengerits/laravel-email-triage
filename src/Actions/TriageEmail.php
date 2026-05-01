<?php

declare(strict_types=1);

namespace JeroenGerits\EmailTriage\Actions;

use JeroenGerits\EmailTriage\Contracts\EmailTriageClassifier;
use JeroenGerits\EmailTriage\Data\EmailSnapshot;
use JeroenGerits\EmailTriage\Data\TriageResult;
use JeroenGerits\EmailTriage\Normalizers\DefaultEmailNormalizer;

/**
 * Normalizes an email snapshot and delegates classification to the active classifier.
 */
final readonly class TriageEmail
{
    public function __construct(
        private DefaultEmailNormalizer $normalizer,
        private EmailTriageClassifier $classifier,
    ) {}

    public function handle(EmailSnapshot $email): TriageResult
    {
        return $this->classifier->classify($this->normalizer->normalize($email));
    }
}
