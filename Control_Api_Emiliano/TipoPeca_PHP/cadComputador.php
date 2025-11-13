<?php

// === CONFIGURAÇÃO DE ERROS (DESENVOLVIMENTO) ===
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// === CABEÇALHO DA RESPOSTA ===
header('Content-Type: application/json');

// === CONEXÃO COM O BANCO ===
require_once 'conexao.php';
$con->set_charset("utf8mb4"); // Melhor que utf8, suporta emojis e mais caracteres

// === LER DADOS JSON DA REQUISIÇÃO ===
$jsonParam = json_decode(file_get_contents('php://input'), true);

if (!$jsonParam) {
    echo json_encode([
        'success' => false,
        'message' => 'Dados JSON inválidos ou ausentes.'
    ]);
    exit;
}

// === EXTRAIR E VALIDAR DADOS ===
$nmPeca       = trim($jsonParam['nmPeca'] ?? '');
$deMarca      = trim($jsonParam['deMarca'] ?? '');
$deModelo     = trim($jsonParam['deModelo'] ?? '');
$idTipoPeca   = intval($jsonParam['idTipoPeca'] ?? 0);

// Campos opcionais (podem ser nulos)
$vlPotencia   = isset($jsonParam['vlPotencia']) && $jsonParam['vlPotencia'] !== '' ? intval($jsonParam['vlPotencia']) : null;
$vlCapacidade = isset($jsonParam['vlCapacidade']) && $jsonParam['vlCapacidade'] !== '' ? intval($jsonParam['vlCapacidade']) : null;
$vlPolegadas  = isset($jsonParam['vlPolegadas']) && $jsonParam['vlPolegadas'] !== '' ? intval($jsonParam['vlPolegadas']) : null;
$vlDpi        = isset($jsonParam['vlDpi']) && $jsonParam['vlDpi'] !== '' ? intval($jsonParam['vlDpi']) : null;

// Validação de campos obrigatórios
if (empty($nmPeca) || empty($deMarca) || empty($deModelo) || $idTipoPeca <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Campos obrigatórios ausentes ou inválidos.'
    ]);
    exit;
}

// === PREPARAR A QUERY (INSERT) ===
$stmt = $con->prepare("
    INSERT INTO peca (
        nmPeca, deMarca, vlPotencia, deModelo, vlCapacidade, 
        idTipoPeca, vlPolegadas, vlDpi
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao preparar consulta: ' . $con->error
    ]);
    exit;
}

// === BIND DOS PARÂMETROS ===
// Tipos: s = string, i = integer, NULL aceita null
$stmt->bind_param(
    "ssisiiii", 
    $nmPeca, 
    $deMarca, 
    $vlPotencia, 
    $deModelo, 
    $vlCapacidade, 
    $idTipoPeca, 
    $vlPolegadas, 
    $vlDpi
);

// === EXECUTAR E RETORNAR RESULTADO ===
if ($stmt->execute()) {
    $idInserido = $con->insert_id; // Pega o ID gerado
    echo json_encode([
        'success' => true,
        'message' => 'Peça cadastrada com sucesso!',
        'idPeca' => $idInserido
    ]);
} else {
    // Verifica se é erro de chave estrangeira
    $erro = $stmt->error;
    if (strpos($erro, 'foreign key') !== false || strpos($erro, 'Cannot add or update a child row') !== false) {
        $mensagem = 'Erro: idTipoPeca inválido. Verifique se o tipo de peça existe.';
    } else {
        $mensagem = 'Erro ao cadastrar peça: ' . $erro;
    }

    echo json_encode([
        'success' => false,
        'message' => $mensagem
    ]);
}

// === FECHAR RECURSOS ===
$stmt->close();
$con->close();
?>