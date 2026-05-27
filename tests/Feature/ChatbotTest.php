<?php

namespace Tests\Feature;

use Tests\TestCase;

class ChatbotTest extends TestCase
{
    public function test_chatbot_api_validates_message(): void
    {
        $response = $this->postJson('/api/chatbot/ask', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['message']);
    }

    public function test_chatbot_api_rejects_long_message(): void
    {
        $response = $this->postJson('/api/chatbot/ask', [
            'message' => str_repeat('a', 501),
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['message']);
    }

    public function test_chatbot_api_returns_fallback_without_api_key(): void
    {
        // Without CHATBOT_API_KEY configured, should use fallback
        $response = $this->postJson('/api/chatbot/ask', [
            'message' => 'Comment faire une demande d\'aide ?',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'message', 'source']);
        $response->assertJson(['success' => true, 'source' => 'fallback']);
        $this->assertStringContainsString('demande', strtolower($response->json('message')));
    }

    public function test_chatbot_fallback_handles_unknown_question(): void
    {
        $response = $this->postJson('/api/chatbot/ask', [
            'message' => 'xyzzy random nonsense',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'source' => 'fallback']);
    }

    public function test_chatbot_accepts_history(): void
    {
        $response = $this->postJson('/api/chatbot/ask', [
            'message' => 'Merci pour l\'info',
            'history' => [
                ['role' => 'user', 'content' => 'Comment faire un don ?'],
                ['role' => 'assistant', 'content' => 'Vous pouvez faire un don via Wave ou Orange Money.'],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_chatbot_rejects_invalid_history_format(): void
    {
        $response = $this->postJson('/api/chatbot/ask', [
            'message' => 'Bonjour',
            'history' => [
                ['role' => 'invalid_role', 'content' => 'test'],
            ],
        ]);

        $response->assertStatus(422);
    }
}
