<?php

require_once __DIR__ . '/config.php';

function ollamaRequest(string $endpoint): array
{
    $url = OLLAMA_URL . $endpoint;

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);

        throw new RuntimeException(
            'Could not connect to Ollama: ' . $error
        );
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($httpCode < 200 || $httpCode >= 300) {
        throw new RuntimeException(
            "Ollama returned HTTP $httpCode"
        );
    }

    $data = json_decode($response, true);

    if (!is_array($data)) {
        throw new RuntimeException(
            'Invalid response from Ollama'
        );
    }

    return $data;
}

function ollamaModelExists(string $model): bool
{
    $data = ollamaRequest('/api/tags');

    if (!isset($data['models']) || !is_array($data['models'])) {
        return false;
    }

    foreach ($data['models'] as $installedModel) {
        if (($installedModel['name'] ?? '') === $model) {
            return true;
        }
    }

    return false;
}

function ollamaPullModel(string $model, callable $onProgress = null): void
{
    $url = OLLAMA_URL . '/api/pull';

    $payload = json_encode([
        'model' => $model,
        'stream' => true
    ]);

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json'
        ],
        CURLOPT_RETURNTRANSFER => false,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 0,

        CURLOPT_WRITEFUNCTION => function ($ch, $data) use ($onProgress) {

            $lines = preg_split('/\r\n|\r|\n/', $data);

            foreach ($lines as $line) {

                $line = trim($line);

                if ($line === '') {
                    continue;
                }

                $json = json_decode($line, true);

                if (!is_array($json)) {
                    continue;
                }

                if ($onProgress !== null) {
                    $onProgress($json);
                }
            }

            return strlen($data);
        }
    ]);

    $success = curl_exec($ch);

    if ($success === false) {
        $error = curl_error($ch);
        curl_close($ch);

        throw new RuntimeException(
            'Failed to pull Ollama model: ' . $error
        );
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($httpCode < 200 || $httpCode >= 300) {
        throw new RuntimeException(
            "Ollama returned HTTP $httpCode while pulling model"
        );
    }
}

function ollamaGenerate(
    string $model,
    string $prompt,
    bool $stream = false
): string {
    $url = OLLAMA_URL . '/api/generate';

    $payload = json_encode([
        'model' => $model,
        'prompt' => $prompt,
        'stream' => $stream
    ]);

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json'
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 120,
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);

        throw new RuntimeException(
            'Failed to generate response from Ollama: ' . $error
        );
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($httpCode < 200 || $httpCode >= 300) {
        throw new RuntimeException(
            "Ollama returned HTTP $httpCode while generating"
        );
    }

    $data = json_decode($response, true);

    if (!is_array($data)) {
        throw new RuntimeException(
            'Invalid response from Ollama'
        );
    }

    return $data['response'] ?? '';
}

function ollamaTestModel(string $model): bool
{
    $response = ollamaGenerate(
        $model,
        'ตอบว่า "พร้อมใช้งาน" เท่านั้น'
    );

    return trim($response) !== '';
}

function ollamaGenerateStream(
    string $model,
    string $prompt,
    callable $onChunk
): void {

    $url = OLLAMA_URL . '/api/generate';

    $payload = json_encode([
        'model' => $model,
        'prompt' => $prompt,
        'stream' => true
    ], JSON_UNESCAPED_UNICODE);

    if ($payload === false) {
        throw new RuntimeException(
            'Failed to encode Ollama request'
        );
    }

    $ch = curl_init($url);

    if ($ch === false) {
        throw new RuntimeException(
            'Failed to initialize cURL'
        );
    }

    $buffer = '';

    curl_setopt_array($ch, [

        CURLOPT_POST => true,

        CURLOPT_POSTFIELDS => $payload,

        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json'
        ],

        CURLOPT_RETURNTRANSFER => false,

        CURLOPT_CONNECTTIMEOUT => 10,

        // No artificial timeout.
        // Our i3-2120 needs all the time it can get. 🥔
        CURLOPT_TIMEOUT => 0,

        CURLOPT_NOSIGNAL => true,

        CURLOPT_WRITEFUNCTION =>
            function ($ch, $data) use (&$buffer, $onChunk) {

                $buffer .= $data;

                while (
                    ($position = strpos($buffer, "\n")) !== false
                ) {

                    $line = trim(
                        substr($buffer, 0, $position)
                    );

                    $buffer = substr(
                        $buffer,
                        $position + 1
                    );

                    if ($line === '') {
                        continue;
                    }

                    $json = json_decode(
                        $line,
                        true
                    );

                    if (!is_array($json)) {
                        continue;
                    }

                    $onChunk($json);
                }

                return strlen($data);
            }
    ]);

    $success = curl_exec($ch);

    if ($success === false) {

        $error = curl_error($ch);

        curl_close($ch);

        throw new RuntimeException(
            'Failed to generate streaming response: ' . $error
        );
    }

    // Process anything remaining in the buffer.
    $line = trim($buffer);

    if ($line !== '') {

        $json = json_decode(
            $line,
            true
        );

        if (is_array($json)) {
            $onChunk($json);
        }
    }

    $httpCode =
        curl_getinfo(
            $ch,
            CURLINFO_HTTP_CODE
        );

    curl_close($ch);

    if ($httpCode < 200 || $httpCode >= 300) {

        throw new RuntimeException(
            "Ollama returned HTTP $httpCode while streaming"
        );
    }
}