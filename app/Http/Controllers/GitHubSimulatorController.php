<?php

namespace App\Http\Controllers;

use App\Models\IntegrationEvent;
use App\Services\GitHubEventDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class GitHubSimulatorController extends Controller
{
    public function index(): Response
    {
        $scenarios = $this->scenarios();
        $latestGitHubEvents = IntegrationEvent::query()
            ->where('source', 'github')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (IntegrationEvent $event) => [
                'id' => $event->id,
                'event_name' => $event->event_name,
                'status' => $event->status,
                'summary' => $event->summary,
                'external_reference' => $event->external_reference,
                'created_at' => $event->created_at?->format('d/m/Y H:i'),
            ]);

        return Inertia::render('Integrations/GitHubSimulator', [
            'scenarios' => array_values($scenarios),
            'latestGitHubEvents' => $latestGitHubEvents,
        ]);
    }

    public function store(Request $request, GitHubEventDispatcher $gitHubEventDispatcher): RedirectResponse
    {
        $scenarios = $this->scenarios();
        $scenarioKeys = array_keys($scenarios);

        $validated = $request->validate([
            'scenario' => ['required', Rule::in([...$scenarioKeys, 'all'])],
        ]);

        $selectedKeys = $validated['scenario'] === 'all'
            ? $scenarioKeys
            : [$validated['scenario']];

        foreach ($selectedKeys as $key) {
            $scenario = $scenarios[$key];
            $gitHubEventDispatcher->dispatch($scenario['github_event'], $scenario['payload']);
        }

        return back()->with(
            'status',
            $validated['scenario'] === 'all'
                ? 'Se han enviado las 4 simulaciones de GitHub a RabbitMQ.'
                : 'Simulación enviada a RabbitMQ: '.$scenarios[$validated['scenario']]['event_name'].'.'
        );
    }

    private function scenarios(): array
    {
        return [
            'commit_pushed' => [
                'key' => 'commit_pushed',
                'event_name' => 'commit.pushed',
                'github_event' => 'push',
                'title' => 'Push de commits',
                'description' => 'Simula un push en la rama principal con varios commits.',
                'payload' => [
                    'simulated' => true,
                    'ref' => 'refs/heads/main',
                    'after' => '9f1c3b7a4d5e6f7081920abcedf123456789abcd',
                    'repository' => [
                        'full_name' => 'anaskha7/JuegosLaravel',
                    ],
                    'pusher' => [
                        'name' => 'simulador-bot',
                    ],
                    'commits' => [
                        ['id' => '9f1c3b7', 'message' => 'Actualiza flujo RabbitMQ'],
                        ['id' => '4c7a1d2', 'message' => 'Añade simulador de eventos GitHub'],
                    ],
                ],
            ],
            'pull_request_opened' => [
                'key' => 'pull_request_opened',
                'event_name' => 'pull_request.opened',
                'github_event' => 'pull_request',
                'title' => 'Pull request abierta',
                'description' => 'Simula la apertura de una PR nueva para entrar en la cola de GitHub.',
                'payload' => [
                    'simulated' => true,
                    'action' => 'opened',
                    'repository' => [
                        'full_name' => 'anaskha7/JuegosLaravel',
                    ],
                    'pull_request' => [
                        'number' => 302,
                        'title' => 'Simulación de apertura de pull request',
                        'diff_url' => 'https://example.com/simulated-pr-302.diff',
                    ],
                ],
            ],
            'pull_request_merged' => [
                'key' => 'pull_request_merged',
                'event_name' => 'pull_request.merged',
                'github_event' => 'pull_request',
                'title' => 'Pull request fusionada',
                'description' => 'Simula el cierre de una PR con merge completado.',
                'payload' => [
                    'simulated' => true,
                    'action' => 'closed',
                    'repository' => [
                        'full_name' => 'anaskha7/JuegosLaravel',
                    ],
                    'pull_request' => [
                        'number' => 303,
                        'title' => 'Simulación de merge de pull request',
                        'merged' => true,
                        'merged_by' => [
                            'login' => 'simulador-bot',
                        ],
                    ],
                ],
            ],
            'issue_created' => [
                'key' => 'issue_created',
                'event_name' => 'issue.created',
                'github_event' => 'issues',
                'title' => 'Issue creada',
                'description' => 'Simula una incidencia nueva creada en GitHub.',
                'payload' => [
                    'simulated' => true,
                    'action' => 'opened',
                    'repository' => [
                        'full_name' => 'anaskha7/JuegosLaravel',
                    ],
                    'issue' => [
                        'number' => 88,
                        'title' => 'Simulación de issue creada',
                        'user' => [
                            'login' => 'simulador-bot',
                        ],
                    ],
                ],
            ],
        ];
    }
}
