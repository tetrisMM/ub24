<?php
/**
 * telegram_bot.php
 * Biblioteca para enviar mensagens e ficheiros via Telegram
 * Com logging local e limpeza automática do log > 2MB
 */

function telegram_log($message) {
    $logFile = __DIR__ . '/telegram_log.txt';
    $maxSize = 2 * 1024 * 1024; // 2 MB

    // Se o ficheiro existe e é maior que 2 MB, apaga-o
    if (file_exists($logFile) && filesize($logFile) > $maxSize) {
        unlink($logFile);
        // recria o log vazio com aviso
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Log limpo automaticamente (excedeu 2MB)\n", FILE_APPEND);
    }

    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[{$timestamp}] {$message}\n", FILE_APPEND);
}

function telegram_read_credentials() {
    $credentialsFile = '/home/xui/telegram/credentials.txt';

    if (!is_readable($credentialsFile)) {
        telegram_log("ERRO: Ficheiro de credenciais não existe ou não é legível: {$credentialsFile}");
        throw new RuntimeException("Ficheiro de credenciais não existe ou não é legível: {$credentialsFile}");
    }

    $content = file_get_contents($credentialsFile);
    if ($content === false) {
        telegram_log("ERRO: Falha ao ler o ficheiro de credenciais: {$credentialsFile}");
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
        telegram_log("ERRO: token_id ou chat_id não encontrados em {$credentialsFile}");
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
        $err = curl_error($ch);
        telegram_log("ERRO ao enviar mensagem: {$err}");
        curl_close($ch);
        return ['ok' => false, 'error' => $err];
    }

    curl_close($ch);
    $decoded = json_decode($resp, true);

    if (empty($decoded['ok'])) {
        telegram_log("ERRO Telegram: {$resp}");
    } else {
        telegram_log("Mensagem enviada com sucesso: '{$text}'");
    }

    return $decoded;
}

/**
 * Envia ficheiro (documento, txt, log, etc.)
 */
function telegram_send_file($filePath, $caption = '') {
    if (!is_readable($filePath)) {
        telegram_log("ERRO: Ficheiro não encontrado ou sem permissão: {$filePath}");
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
        $err = curl_error($ch);
        telegram_log("ERRO ao enviar ficheiro '{$filePath}': {$err}");
        curl_close($ch);
        return ['ok' => false, 'error' => $err];
    }

    curl_close($ch);
    $decoded = json_decode($resp, true);

    if (empty($decoded['ok'])) {
        telegram_log("ERRO Telegram ao enviar ficheiro: {$resp}");
    } else {
        telegram_log("Ficheiro enviado com sucesso: '{$filePath}' com legenda '{$caption}'");
    }

    return $decoded;
}
?>
