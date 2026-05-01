<?php

declare(strict_types=1);

namespace JeroenGerits\EmailTriage;

use Illuminate\Support\ServiceProvider;
use JeroenGerits\EmailTriage\Actions\ResolveConfiguredClassifier;
use JeroenGerits\EmailTriage\Contracts\EmailTriageClassifier;

final class EmailTriageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/email-triage.php', 'email-triage');

        $this->app->bind(EmailTriageClassifier::class, fn (): EmailTriageClassifier => $this->app
            ->make(ResolveConfiguredClassifier::class)
            ->fromConfig('ai.classifier'));

        $this->app->singleton(EmailTriage::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/email-triage.php' => config_path('email-triage.php'),
        ], 'email-triage-config');
    }
}
