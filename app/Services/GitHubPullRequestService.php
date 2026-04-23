<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class GitHubPullRequestService
{
    public function fetchDiff(array $payload): string
    {
        $diffUrl = $payload['pull_request']['diff_url'] ?? null;
        $repo = $payload['repository']['full_name'] ?? null;
        $prNumber = $payload['pull_request']['number'] ?? null;

        if (! $diffUrl && $repo && $prNumber) {
            $diffUrl = "https://api.github.com/repos/{$repo}/pulls/{$prNumber}";
        }

        if (! $diffUrl) {
            throw new \RuntimeException('No se ha encontrado la URL del diff de la pull request.');
        }

        $request = Http::timeout(30)
            ->accept('application/vnd.github.v3.diff');

        if (filled(config('services.github.token'))) {
            $request = $request->withToken(config('services.github.token'));
        }

        try {
            return $request->get($diffUrl)->throw()->body();
        } catch (RequestException $exception) {
            throw new \RuntimeException('No se pudo descargar el diff de GitHub.', previous: $exception);
        }
    }

    public function postReviewComment(string $repository, int $pullRequestNumber, string $comment): bool
    {
        $token = config('services.github.token');

        if (! filled($token)) {
            return false;
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->post("https://api.github.com/repos/{$repository}/issues/{$pullRequestNumber}/comments", [
                'body' => $comment,
            ]);

        return $response->successful();
    }
}
