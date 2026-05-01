<?php

declare(strict_types=1);

namespace JeroenGerits\EmailTriage\Data;

final readonly class EmailSnapshot
{
    public function __construct(
        public string $subject,
        public string $from,
        public string $body,
        public ?string $to = null,
        public ?string $cc = null,
        public ?string $replyTo = null,
        public ?string $inbox = null,
        public array $headers = [],
        public array $metadata = [],
    ) {}

    public function content(): string
    {
        return mb_strtolower(implode("\n", array_filter([
            $this->subject,
            $this->body,
            $this->from,
            $this->to,
            $this->cc,
            $this->replyTo,
            $this->inbox,
        ], static fn (mixed $value): bool => is_string($value) && $value !== '')));
    }
}
