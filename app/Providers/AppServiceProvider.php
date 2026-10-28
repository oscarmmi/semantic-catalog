<?php

namespace App\Providers;

use App\Services\Llm\GeminiLlmClient;
use App\Services\Llm\LlmClient;
use App\Services\Llm\RetrievalOnlyLlmClient;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LlmClient::class, function (): LlmClient {
            if (app()->environment('testing')) {
                return new RetrievalOnlyLlmClient;
            }

            return filled(config('services.gemini.key'))
                ? new GeminiLlmClient
                : new RetrievalOnlyLlmClient;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
