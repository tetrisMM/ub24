<?php
// Caminho absoluto do ficheiro a executar
$script = '/home/xui/crons/backup_script.php';

// Verifica se o ficheiro existe
if (!file_exists($script)) {
    die("Erro: O ficheiro $script não existe.\n");
}

// Executa o ficheiro PHP com o mesmo intérprete
$output = [];
$return_var = 0;

// Executa e captura a saída
exec("php " . escapeshellarg($script) . " 2>&1", $output, $return_var);

// Mostra o resultado
echo implode("\n", $output) . "\n";
echo "\nCódigo de retorno: $return_var\n";
?>
