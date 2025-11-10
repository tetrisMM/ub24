<?php
header("Content-Type: application/json");

$results = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get raw JSON input
    $json_data = file_get_contents("php://input");
    $data = json_decode($json_data, true);

    // Check if decoding failed
    if ($data === null) {
        $results['success'] = false;
        $results['error'] = 'Invalid JSON data.';
        echo json_encode($results);
        exit;
    }

    // Extract values from JSON
    $id = $data["Id"] ?? '';
    $ip = $data["Ip"] ?? '';
    $broadcast = $data["Broadcast"] ?? '';
    $dns = $data["Dns"] ?? '';
    $user_host = $data["User_host"] ?? 'localhost:3306';
    $user_user = $data["User"] ?? 'root';
    $user_password = $data["User_password"] ?? '.';
    $user_bd = $data["User_bd"] ?? 'xui';

    // Split host:port if provided
    $host_parts = explode(':', $user_host);
    $db_host = $host_parts[0] ?? 'localhost';
    $db_port = $host_parts[1] ?? 3306;

    // Connect to database
    $conn = mysqli_connect($db_host, $user_user, $user_password, $user_bd, (int)$db_port);

    if (!$conn) {
        $results['success'] = false;
        $results['error'] = 'Database connection failed: ' . mysqli_connect_error();
        echo json_encode($results);
        exit;
    }

    mysqli_set_charset($conn, 'utf8');

    if (!empty($id)) {
        // Caso tenha IP, atualiza o server_ip
        if (!empty($ip)) {
            $stmt = $conn->prepare("UPDATE servers SET server_ip = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("ss", $ip, $id);
                if ($stmt->execute()) {
                    $results['success'] = true;
                } else {
                    $results['success'] = false;
                    $results['error'] = 'Execution failed: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                $results['success'] = false;
                $results['error'] = 'Prepare failed: ' . $conn->error;
            }
        }

        // Caso tenha DNS, atualiza o domain_name
        if (!empty($dns)) {
            $stmt = $conn->prepare("UPDATE servers SET domain_name = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("ss", $dns, $id);
                if ($stmt->execute()) {
                    $results['success'] = true;
                } else {
                    $results['success'] = false;
                    $results['error'] = 'Execution failed: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                $results['success'] = false;
                $results['error'] = 'Prepare failed: ' . $conn->error;
            }
        }

        if (empty($ip) && empty($dns)) {
            $results['success'] = false;
            $results['error'] = 'Missing "Ip" or "Dns" in JSON.';
        }
    } else {
        $results['success'] = false;
        $results['error'] = 'Missing "Id" in JSON.';
    }

    mysqli_close($conn);

    if ($results['success'] === true) {
        $results['Final'] = [
            'broadcast' => $broadcast,
            'ip' => $ip,
            'dns' => $dns,
            'id' => $id
        ];
    }

} else {
    $results['success'] = false;
    $results['error'] = 'Invalid request method. Use POST.';
}

// Converter resposta em JSON
$response_json = json_encode($results);

// === LOG rotativo em /home/xui/helperfordummieslog.txt (máx 2MB) ===
$log_path = "/home/xui/helperfordummieslog.txt";
$max_bytes = 2 * 1024 * 1024; // 2 MB

$log_entry  = "==== " . date("Y-m-d H:i:s") . " ====\n";
$log_entry .= "INPUT: " . ($json_data ?? '') . "\n";
$log_entry .= "OUTPUT: " . $response_json . "\n\n";

// Se existir e for >= 2MB, apagar para começar um novo
if (file_exists($log_path)) {
    clearstatcache(true, $log_path);
    $size = @filesize($log_path);
    if ($size !== false && $size >= $max_bytes) {
        @unlink($log_path);
    }
}

// Definir flags: append se existir, senão sobrescreve (cria novo)
$flags = (file_exists($log_path) ? FILE_APPEND : 0) | LOCK_EX;
$write_ok = @file_put_contents($log_path, $log_entry, $flags);

// Se falhar o log, anexar nota na resposta (não quebra o fluxo da API)
if ($write_ok === false) {
    $results['log_warning'] = 'Could not write to ' . $log_path;
    $response_json = json_encode($results);
}

// Enviar resposta
echo $response_json;
