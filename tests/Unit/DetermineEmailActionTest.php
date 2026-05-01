<?php

declare(strict_types=1);

use JeroenGerits\EmailTriage\Actions\DetermineEmailAction;
use JeroenGerits\EmailTriage\Data\TriageResult;
use JeroenGerits\EmailTriage\Enums\ActionType;
use JeroenGerits\EmailTriage\Enums\Urgency;

it('returns delete for spam', function (): void {
    $action = app(DetermineEmailAction::class)->handle(new TriageResult(
        summary: 'spam',
        urgency: Urgency::None,
        actionType: ActionType::None,
        actionNeeded: false,
        spam: true,
        confidence: 1.0,
    ));

    expect($action)->toBe(ActionType::Delete);
});

it('returns archive when no action is needed', function (): void {
    $action = app(DetermineEmailAction::class)->handle(new TriageResult(
        summary: 'fyi',
        urgency: Urgency::Low,
        actionType: ActionType::Reply,
        actionNeeded: false,
        spam: false,
        confidence: 0.7,
    ));

    expect($action)->toBe(ActionType::Archive);
});
