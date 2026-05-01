<?php

declare(strict_types=1);

namespace JeroenGerits\EmailTriage\Normalizers;

use JeroenGerits\EmailTriage\Data\EmailSnapshot;

final readonly class DefaultEmailNormalizer
{
    public function normalize(EmailSnapshot $email): EmailSnapshot
    {
        return new EmailSnapshot(
            subject: trim($email->subject),
            from: strtolower(trim($email->from)),
            body: $this->normalizeBody($email->body),
            to: $this->normalizeAddress($email->to),
            cc: $this->normalizeAddress($email->cc),
            replyTo: $this->normalizeAddress($email->replyTo),
            inbox: $email->inbox !== null ? trim($email->inbox) : null,
            headers: $email->headers,
            metadata: $email->metadata,
        );
    }

    private function normalizeAddress(?string $value): ?string
    {
        return $value !== null ? strtolower(trim($value)) : null;
    }

    private function normalizeBody(string $body): string
    {
        $body = strip_tags($body);
        $body = preg_replace('/\R+/u', "\n", $body) ?? $body;

        return trim($body);
    }
}
