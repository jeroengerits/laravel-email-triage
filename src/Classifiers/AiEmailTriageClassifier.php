<?php

declare(strict_types=1);

namespace JeroenGerits\EmailTriage\Classifiers;

use JeroenGerits\EmailTriage\Actions\DetermineEmailAction;
use JeroenGerits\EmailTriage\Contracts\EmailTriageClassifier;
use JeroenGerits\EmailTriage\Data\EmailSnapshot;
use JeroenGerits\EmailTriage\Data\TriageResult;
use JeroenGerits\EmailTriage\Enums\ActionType;
use JeroenGerits\EmailTriage\Enums\EmailCategory;
use JeroenGerits\EmailTriage\Enums\Sentiment;
use JeroenGerits\EmailTriage\Enums\Urgency;
use JeroenGerits\EmailTriage\Exceptions\InvalidTriageResultException;
use ValueError;

abstract readonly class AiEmailTriageClassifier implements EmailTriageClassifier
{
    private const REQUIRED_FIELDS = [
        'summary',
        'urgency',
        'action_needed',
        'action_type',
        'category',
        'sentiment',
        'spam',
        'confidence',
    ];

    public function __construct(
        private DetermineEmailAction $determineEmailAction = new DetermineEmailAction,
    ) {}

    public function classify(EmailSnapshot $email): TriageResult
    {
        $result = $this->map($this->classifyRaw($email));

        return new TriageResult(
            summary: $result->summary,
            urgency: $result->urgency,
            actionType: $this->determineEmailAction->handle($result),
            actionNeeded: $result->actionNeeded,
            spam: $result->spam,
            confidence: $result->confidence,
            category: $result->category,
            sentiment: $result->sentiment,
            metadata: array_merge($result->metadata, ['classifier' => 'ai']),
        );
    }

    /**
     * @return array<string, mixed>
     */
    abstract protected function classifyRaw(EmailSnapshot $email): array;

    /**
     * @param  array<string, mixed>  $result
     */
    private function map(array $result): TriageResult
    {
        $this->assertRequiredFields($result);

        if (! is_string($result['summary']) || trim($result['summary']) === '') {
            throw new InvalidTriageResultException('AI triage summary must be a non-empty string.');
        }

        if (! is_bool($result['action_needed']) || ! is_bool($result['spam'])) {
            throw new InvalidTriageResultException('AI triage booleans are invalid.');
        }

        if (! is_numeric($result['confidence'])) {
            throw new InvalidTriageResultException('AI triage confidence must be numeric.');
        }

        $confidence = (float) $result['confidence'];

        if ($confidence < 0 || $confidence > 1) {
            throw new InvalidTriageResultException('AI triage confidence must be between 0 and 1.');
        }

        try {
            return new TriageResult(
                summary: trim($result['summary']),
                urgency: $this->mapEnum($result['urgency'], 'urgency', Urgency::class),
                actionType: $this->mapEnum($result['action_type'], 'action_type', ActionType::class),
                actionNeeded: $result['action_needed'],
                spam: $result['spam'],
                confidence: $confidence,
                category: $this->mapEnum($result['category'], 'category', EmailCategory::class),
                sentiment: $this->mapEnum($result['sentiment'], 'sentiment', Sentiment::class),
                metadata: $this->mapMetadata($result['metadata'] ?? null),
            );
        } catch (ValueError $exception) {
            throw new InvalidTriageResultException($exception->getMessage(), $exception->getCode(), previous: $exception);
        }
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function assertRequiredFields(array $result): void
    {
        foreach (self::REQUIRED_FIELDS as $field) {
            if (! array_key_exists($field, $result)) {
                throw new InvalidTriageResultException("Missing required AI triage field [{$field}].");
            }
        }
    }

    /**
     * @param  class-string<Urgency|ActionType|EmailCategory|Sentiment>  $enumClass
     */
    private function mapEnum(mixed $value, string $field, string $enumClass): Urgency|ActionType|EmailCategory|Sentiment
    {
        return $enumClass::from($this->requireString($value, $field));
    }

    private function mapMetadata(mixed $metadata): array
    {
        return is_array($metadata) ? $metadata : [];
    }

    private function requireString(mixed $value, string $field): string
    {
        if (! is_string($value) || trim($value) === '') {
            throw new InvalidTriageResultException("AI triage field [{$field}] must be a non-empty string.");
        }

        return trim($value);
    }
}
