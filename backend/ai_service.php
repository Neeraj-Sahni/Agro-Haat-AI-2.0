<?php

require_once __DIR__ . '/../config/api_keys.php';

function getAIResponse($prompt)
{
    // Future: Gemini/OpenRouter support
    if (AI_PROVIDER !== 'ollama') {
        return [
            'success' => false,
            'message' => 'Selected AI provider is not configured yet.'
        ];
    }

    $data = [
        'model'  => OLLAMA_MODEL,
        'prompt' => $prompt,
        'stream' => false
    ];

    $ch = curl_init(OLLAMA_URL);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);

    $response = curl_exec($ch);
    $error    = curl_error($ch);

    curl_close($ch);

    if ($error) {
        return [
            'success' => false,
            'message' => 'Agro-AI is currently unavailable.'
        ];
    }

    $decoded = json_decode($response, true);

    if (isset($decoded['response'])) {
        return [
            'success' => true,
            'message' => trim($decoded['response'])
        ];
    }

    return [
        'success' => false,
        'message' => 'Unable to generate AI response.'
    ];
}