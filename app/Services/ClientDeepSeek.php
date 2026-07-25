<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class ClientDeepSeek
{
    private const MODEL = 'deepseek-v4-flash';

    private const ENDPOINT = 'https://api.deepseek.com/chat/completions';

    private const MAX_TOKENS = 800;

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    public function chat(array $messages): string
    {
        $key = config('services.deepseek.key');

        if (blank($key)) {
            throw new RuntimeException('Clé DeepSeek non configurée.');
        }

        $response = Http::withToken($key)
            ->timeout(30)
            ->post(self::ENDPOINT, [
                'model' => self::MODEL,
                'messages' => $messages,
                'max_tokens' => self::MAX_TOKENS,
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Erreur DeepSeek : '.($response->json('error.message') ?? $response->status())
            );
        }

        $content = $response->json('choices.0.message.content');

        if (blank($content)) {
            throw new RuntimeException('Réponse vide de DeepSeek.');
        }

        return $content;
    }
}
