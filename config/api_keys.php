<?php

require_once __DIR__ . '/env.php';

define('AI_PROVIDER', $_ENV['AI_PROVIDER'] ?? 'ollama');

define('AI_API_KEY', $_ENV['AI_API_KEY'] ?? '');

define('WEATHER_API_KEY', $_ENV['WEATHER_API_KEY'] ?? '');

define('OLLAMA_URL', $_ENV['OLLAMA_URL'] ?? 'http://localhost:11434/api/generate');

define('OLLAMA_MODEL', $_ENV['OLLAMA_MODEL'] ?? 'llama3.2');