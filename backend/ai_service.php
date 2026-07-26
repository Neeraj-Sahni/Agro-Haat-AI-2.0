<?php

function getAIResponse($prompt)
{
    $ollamaUrl = 'http://localhost:11434/api/generate';

    $data = [
        'model' => 'llama3.2',
        'prompt' => $prompt,
        'stream' => false
    ];

    $ch = curl_init($ollamaUrl);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);

    $response = curl_exec($ch);
    $error = curl_error($ch);

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