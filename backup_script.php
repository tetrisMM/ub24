<?php
// ======================================================
// 📝 CONFIGURAÇÃO
// ======================================================
$logFile = '/home/xui/crons/backup.log';
$credentialsFile = '/home/xui/config/credentials.txt';
$painelName = ''; // inicialmente vazio

// ======================================================
// 📝 LER NOME DO PAINEL
// ======================================================
if (file_exists($credentialsFile)) {
    $contents = file_get_contents($credentialsFile);
    if (preg_match('/name\s*=\s*"([^"]+)"|name\s*=\s*([^\s#;]+)/i', $contents, $matches)) {
        $painelName = trim($matches[1] ?: $matches[2]);
    }
}

// ======================================================
// ⚠️ VALIDAR PAINEL
// ======================================================
if (empty($painelName)) {
    $log = "===== Backup cancelado em: " . date('Y-m-d H:i:s') . " =====\n";
    $log .= "❌ Nenhum nome de painel definido no ficheiro de credenciais ($credentialsFile). Backup não será executado.\n";
    $log .= "===== Fim da execução =====\n";
    file_put_contents($logFile, $log);
    echo "<p style='color:red;'>❌ Backup cancelado: Nenhum nome de painel definido.</p>";
    exit; // cancela execução
}

// ======================================================
// 📝 CONFIGURAÇÕES DE DESTINO
// ======================================================
$destFolder = "backups_" . $painelName;
$log = "===== Backup iniciado em: " . date('Y-m-d H:i:s') . " =====\n\n";

// ======================================================
// 🔄 SERVIDORES E CRIAÇÃO DE PASTAS REMOTAS
// ======================================================

// Servidor 1
$remoteUser1 = "root";
$remoteHost1 = "168.119.37.186";
$remoteDir1 = "/home/xui/{$destFolder}";
$sshPass1 = "bXRnsfJBDRi7uP";

// Cria pasta remota se não existir
$cmdCreateDir1 = "HOME=/tmp sshpass -p '{$sshPass1}' ssh -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null {$remoteUser1}@{$remoteHost1} 'mkdir -p {$remoteDir1}'";
exec($cmdCreateDir1, $outputDir1, $returnDir1);
$log .= "Servidor 1 - criação da pasta {$remoteDir1}: " . ($returnDir1 === 0 ? "✅ OK" : "❌ Erro") . "\n";

// Servidor 2
$remoteUser2 = "admin";
$remoteHost2 = "95.95.85.93";
$remoteDir2 = "/share/CACHEDEV2_DATA/NAS01/Backup_Painel/{$destFolder}";
$sshOptions2 = "-p 22000 -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null";
$sshPass2 = "Lisbon2019.";

// Cria pasta remota se não existir
$cmdCreateDir2 = "HOME=/tmp sshpass -p '{$sshPass2}' ssh {$sshOptions2} {$remoteUser2}@{$remoteHost2} 'mkdir -p {$remoteDir2}'";
exec($cmdCreateDir2, $outputDir2, $returnDir2);
$log .= "Servidor 2 - criação da pasta {$remoteDir2}: " . ($returnDir2 === 0 ? "✅ OK" : "❌ Erro") . "\n\n";

// ======================================================
// 🔄 RSYNC
// ======================================================
$cmd1 = "HOME=/tmp sshpass -p '{$sshPass1}' rsync -avz --delete -vv -e 'ssh -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null' /home/xui/backups/ {$remoteUser1}@{$remoteHost1}:{$remoteDir1}";
$cmd2 = "HOME=/tmp sshpass -p '{$sshPass2}' rsync -avz --delete -vv -e " . escapeshellarg("ssh {$sshOptions2}") . " /home/xui/backups/ {$remoteUser2}@{$remoteHost2}:{$remoteDir2}";

// Executa o rsync
exec($cmd1 . " 2>&1", $output1, $return1);
exec($cmd2 . " 2>&1", $output2, $return2);

// ======================================================
// 📄 LOG
// ======================================================
$log .= ">>> Resultado do Primeiro Servidor ({$remoteHost1}):\n";
$log .= implode("\n", $output1) . "\n";
$log .= "Código de Retorno: $return1\n\n";

$log .= ">>> Resultado do Segundo Servidor ({$remoteHost2}):\n";
$log .= implode("\n", $output2) . "\n";
$log .= "Código de Retorno: $return2\n\n";

if ($return1 !== 0) $log .= "⚠️ Erro no rsync do servidor 1!\n";
if ($return2 !== 0) $log .= "⚠️ Erro no rsync do servidor 2!\n";

$log .= "\n===== Fim da execução: " . date('Y-m-d H:i:s') . " =====\n";
file_put_contents($logFile, $log);

// ======================================================
// 📊 RESUMO NA TELA
// ======================================================
echo "<h3>Resumo da execução:</h3>";
echo $return1 === 0 ? "<p style='color:green;'>✅ Backup para o Servidor 1 concluído com sucesso.</p>" : "<p style='color:red;'>❌ Erro no backup para o Servidor 1.</p>";
echo $return2 === 0 ? "<p style='color:green;'>✅ Backup para o Servidor 2 concluído com sucesso.</p>" : "<p style='color:red;'>❌ Erro no backup para o Servidor 2.</p>";

// ======================================================
// 📨 ENVIO PARA TELEGRAM
// ======================================================
try {
    $telegramFile = '/home/xui/telegram/telegram_bot.php';
    if (file_exists($telegramFile)) {
        require_once $telegramFile;

        $mensagem = "💾 *Backup concluído*\n"
            . "Servidor 1: " . ($return1 === 0 ? "✅ OK" : "❌ Erro") . "\n"
            . "Servidor 2: " . ($return2 === 0 ? "✅ OK" : "❌ Erro") . "\n"
            . "Pasta destino: $destFolder\n"
            . "Hora: " . date('Y-m-d H:i:s');

        telegram_send_message($mensagem);
        telegram_send_file($logFile, "📄 Log completo do backup");
    } else {
        $log .= "\n[ERRO Telegram] Arquivo telegram_bot.php não encontrado em /home/xui/telegram/\n";
        file_put_contents($logFile, $log);
    }
} catch (Exception $e) {
    $log .= "\n[ERRO Telegram] " . $e->getMessage() . "\n";
    file_put_contents($logFile, $log);
}
?>
