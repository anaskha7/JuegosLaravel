<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PullRequestReviewService
{
    public function review(string $diff, array $context = []): array
    {
        $provider = $this->resolveProvider();

        if (! $provider) {
            return [
                'provider' => 'none',
                'body' => "Revisión automática pendiente. Configura `OPENAI_API_KEY` o `GEMINI_API_KEY` para activar el análisis.",
                'skipped' => true,
            ];
        }

        return match ($provider) {
            'openai' => $this->reviewWithOpenAi($diff, $context),
            'gemini' => $this->reviewWithGemini($diff, $context),
            default => [
                'provider' => $provider,
                'body' => 'Proveedor de IA no soportado para la revisión.',
                'skipped' => true,
            ],
        };
    }

    private function resolveProvider(): ?string
    {
        $configuredProvider = config('services.ai_review.provider');

        if ($configuredProvider === 'openai' && filled(config('services.ai_review.openai_key'))) {
            return 'openai';
        }

        if ($configuredProvider === 'gemini' && filled(config('services.ai_review.gemini_key'))) {
            return 'gemini';
        }

        if (filled(config('services.ai_review.openai_key'))) {
            return 'openai';
        }

        if (filled(config('services.ai_review.gemini_key'))) {
            return 'gemini';
        }

        return null;
    }

    private function reviewWithOpenAi(string $diff, array $context): array
    {
        $prompt = $this->buildPrompt($diff, $context);
        $response = Http::withToken(config('services.ai_review.openai_key'))
            ->timeout(60)
            ->acceptJson()
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('services.ai_review.openai_model', 'gpt-4o-mini'),
                'temperature' => 0.2,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Eres un revisor técnico de código. Responde en español, en markdown, con hallazgos concretos y propuestas accionables.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ]);

        if (! $response->successful()) {
            return [
                'provider' => 'openai',
                'body' => 'OpenAI no pudo generar la revisión automática.',
                'skipped' => true,
            ];
        }

        return [
            'provider' => 'openai',
            'body' => trim((string) $response->json('choices.0.message.content')),
            'skipped' => false,
        ];
    }

    private function reviewWithGemini(string $diff, array $context): array
    {
        $prompt = $this->buildPrompt($diff, $context);
        $response = Http::timeout(60)
            ->acceptJson()
            ->post(
                'https://generativelanguage.googleapis.com/v1beta/models/'.
                config('services.ai_review.gemini_model', 'gemini-1.5-flash').
                ':generateContent?key='.config('services.ai_review.gemini_key'),
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                ]
            );

        if (! $response->successful()) {
            return [
                'provider' => 'gemini',
                'body' => 'Gemini no pudo generar la revisión automática.',
                'skipped' => true,
            ];
        }

        return [
            'provider' => 'gemini',
            'body' => trim((string) $response->json('candidates.0.content.parts.0.text')),
            'skipped' => false,
        ];
    }

    private function buildPrompt(string $diff, array $context): string
    {
        $title = $context['title'] ?? 'Sin título';
        $repository = $context['repository'] ?? 'Repositorio no indicado';
        $pullRequestNumber = $context['pull_request_number'] ?? '?';
        $diffExcerpt = Str::limit($diff, 15000);

        return <<<PROMPT
Analiza esta pull request de GitHub y devuelve una revisión útil para el equipo.

Repositorio: {$repository}
Pull request: #{$pullRequestNumber}
Título: {$title}

Qué debes revisar:
- posibles errores funcionales
- riesgos de seguridad
- problemas de rendimiento
- deuda técnica clara
- mejoras concretas

Formato esperado:
- un bloque breve de resumen
- una lista de hallazgos
- una lista de mejoras recomendadas

Diff:
{$diffExcerpt}
PROMPT;
    }
}
