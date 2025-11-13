<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the existing connection script
require_once 'conexao.php';
$con->set_charset("utf8mb4"); // Melhor que utf8mb3 para suporte completo a UTF-8

// Decodifica entrada JSON (se precisar no futuro, mas aqui não usamos)
$input = json_decode(file_get_contents('php://input'), true);

// SQL ajustado para a tabela tipopeca
$sql = "SELECT idTipoPeca, nmTipoPeca FROM tipopeca ORDER BY nmTipoPeca";

$result = $con->query($sql);

$response = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Garante que os dados saiam limpos e em UTF-8
        $response[] = [
            "idTipoPeca" => (int)$row['idTipoPeca'],
            "nmTipoPeca" => $row['nmTipoPeca']
        ];
    }
} else {
    // Retorna array vazio se não houver dados (melhor que inventar registro fake)
    $response = [];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_UNESCAPED_UNICODE);

$con->close();
?>