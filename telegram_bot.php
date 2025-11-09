<?php
/**
 * telegram_bot.php
 * Biblioteca para enviar mensagens e ficheiros via Telegram
 */

function telegram_read_credentials() {
    $credentialsFile = '/home/xui/telegram/credentials.txt';

    if (!is_readable($credentialsFile)) {
        throw new RuntimeException("Ficheiro de credenciais não existe ou não é legível: {$credentialsFile}");
    }

    $content = file_get_contents($credentialsFile);
    if ($content === false) {
        throw new RuntimeException("Erro ao ler o ficheiro de credenciais: {$credentialsFile}");
    }

    $creds = [];

    if (preg_match('/token_id\s*=\s*"([^"]+)"|token_id\s*=\s*([^\s#;]+)/i', $content, $m)) {
        $creds['token'] = $m[1] ?: $m[2];
    }

    if (preg_match('/chat_id\s*=\s*"([^"]+)"|chat_id\s*=\s*([^\s#;]+)/i', $content, $m2)) {
        $creds['chat_id'] = $m2[1] ?: $m2[2];
    }

    if (empty($creds['token']) || empty($creds['chat_id'])) {
        throw new RuntimeException("token_id ou chat_id não encontrados em {$credentialsFile}");
    }

    return $creds;
}

/**
 * Envia mensagem de texto
 */
function telegram_send_message($text) {
    $creds = telegram_read_credentials();
    $token = $creds['token'];
    $chat_id = $creds['chat_id'];

    $url = "https://api.telegram.org/bot{$token}/sendMessage";

    $payload = [
        'chat_id' => $chat_id,
        'text' => $text,
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
    ]);
    $resp = curl_exec($ch);
    if ($resp === false) {
        throw new RuntimeException("Erro ao enviar mensagem: " . curl_error($ch));
    }
    curl_close($ch);
    return json_decode($resp, true);
}

/**
 * Envia ficheiro (documento, txt, log, etc.)
 */
function telegram_send_file($filePath, $caption = '') {
    if (!is_readable($filePath)) {
        throw new RuntimeException("Ficheiro não encontrado ou sem permissão: {$filePath}");
    }

    $creds = telegram_read_credentials();
    $token = $creds['token'];
    $chat_id = $creds['chat_id'];

    $url = "https://api.telegram.org/bot{$token}/sendDocument";

    $postFields = [
        'chat_id' => $chat_id,
        'caption' => $caption,
        'document' => new CURLFile($filePath)
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postFields,
    ]);
    $resp = curl_exec($ch);
    if ($resp === false) {
        throw new RuntimeException("Erro ao enviar ficheiro: " . curl_error($ch));
    }
    curl_close($ch);
    return json_decode($resp, true);
}
