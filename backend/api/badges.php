<?php
/**
 * API DE GERENCIAMENTO DE BADGES - HEALTHY HABITS
 * 
 * Este arquivo gerencia todas as operações relacionadas aos badges (conquistas) do sistema:
 * - Criar novos badges
 * - Listar badges (todos ou de um usuário específico)
 * - Atualizar badges existentes
 * - Deletar badges
 * 
 * Endpoints disponíveis:
 * - GET /badges.php - Listar todos os badges
 * - GET /badges.php?user_id=X - Listar badges de um usuário
 * - POST /badges.php - Criar novo badge
 * - PUT /badges.php - Atualizar badge existente
 * - DELETE /badges.php?id=X - Deletar badge
 */

// Inclui o arquivo de configuração com funções utilitárias
require_once '../config.php';

// ========================================
// VARIÁVEIS INICIAIS
// ========================================
// Obtém o método HTTP da requisição (GET, POST, PUT, DELETE)
$method = $_SERVER['REQUEST_METHOD'];
// Estabelece conexão com o banco de dados
$pdo = getConnection();

// ========================================
// ROTEAMENTO PRINCIPAL
// ========================================
// Direciona a requisição para a função apropriada baseada no método HTTP
switch($method) {
    case 'GET':
        // Verifica se foi solicitado badges de um usuário específico
        if (isset($_GET['user_id'])) {
            getBadgesByUser($_GET['user_id'], $pdo);
        } 
        // Se não especificado, retorna todos os badges
        else {
            getAllBadges($pdo);
        }
        break;
    case 'POST':
        // Cria um novo badge
        createBadge($pdo);
        break;
    case 'PUT':
        // Atualiza um badge existente
        updateBadge($pdo);
        break;
    case 'DELETE':
        // Deleta um badge específico
        deleteBadge($_GET['id'], $pdo);
        break;
    default:
        // Retorna erro para métodos HTTP não suportados
        jsonResponse(['error' => 'Método não permitido'], 405);
}

// ========================================
// FUNÇÃO: LISTAR TODOS OS BADGES
// ========================================
/**
 * Retorna todos os badges disponíveis no sistema, ordenados por limite de pontos
 * 
 * @param PDO $pdo Conexão com o banco de dados
 */
function getAllBadges($pdo) {
    // Consulta SQL para buscar todos os badges ordenados por pontos necessários
    $stmt = $pdo->query("SELECT * FROM badges ORDER BY points_threshold ASC");
    // Busca todos os resultados como array associativo
    $badges = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Retorna a lista de badges
    jsonResponse($badges);
}

// ========================================
// FUNÇÃO: LISTAR BADGES POR USUÁRIO
// ========================================
/**
 * Retorna todos os badges conquistados por um usuário específico
 * 
 * @param int $userId ID do usuário
 * @param PDO $pdo Conexão com o banco de dados
 */
function getBadgesByUser($userId, $pdo) {
    // Consulta SQL que junta badges com informações de quando foram conquistados
    $stmt = $pdo->prepare("
        SELECT b.*, ub.awarded_date 
        FROM user_badges ub 
        JOIN badges b ON ub.badge_id = b.id 
        WHERE ub.user_id = ? 
        ORDER BY ub.awarded_date DESC
    ");
    $stmt->execute([$userId]);
    $badges = $stmt->fetchAll(PDO::FETCH_ASSOC);
    jsonResponse($badges);
}

// ========================================
// FUNÇÃO: CRIAR NOVO BADGE
// ========================================
/**
 * Cria um novo badge no sistema
 * 
 * @param PDO $pdo Conexão com o banco de dados
 */
function createBadge($pdo) {
    // Lê os dados JSON enviados na requisição
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Valida se os campos obrigatórios foram fornecidos
    if (!isset($input['name']) || !isset($input['points_threshold'])) {
        jsonResponse(['error' => 'Dados obrigatórios não fornecidos'], 400);
    }
    
    // Define valor padrão para descrição (campo opcional)
    $description = $input['description'] ?? '';
    
    try {
        // Prepara e executa a inserção do novo badge
        $stmt = $pdo->prepare("INSERT INTO badges (name, description, points_threshold) VALUES (?, ?, ?)");
        $stmt->execute([$input['name'], $description, $input['points_threshold']]);
        
        // Obtém o ID do badge recém-criado
        $badgeId = $pdo->lastInsertId();
        // Retorna sucesso com o ID do badge criado
        jsonResponse(['id' => $badgeId, 'message' => 'Badge criado com sucesso'], 201);
    } catch(PDOException $e) {
        // Verifica se o erro é de duplicação (nome já existe)
        if ($e->getCode() == 23000) {
            jsonResponse(['error' => 'Nome do badge já existe'], 409);
        } else {
            // Retorna erro interno do servidor para outros tipos de erro
            jsonResponse(['error' => 'Erro interno do servidor'], 500);
        }
    }
}

// ========================================
// FUNÇÃO: ATUALIZAR BADGE
// ========================================
/**
 * Atualiza um badge existente no sistema
 * 
 * @param PDO $pdo Conexão com o banco de dados
 */
function updateBadge($pdo) {
    // Lê os dados JSON enviados na requisição
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Valida se o ID do badge foi fornecido
    if (!isset($input['id'])) {
        jsonResponse(['error' => 'ID do badge é obrigatório'], 400);
    }
    
    // Arrays para construir a query de atualização dinamicamente
    $fields = [];
    $values = [];
    
    // Verifica cada campo opcional e adiciona à query se fornecido
    if (isset($input['name'])) {
        $fields[] = "name = ?";
        $values[] = $input['name'];
    }
    
    if (isset($input['description'])) {
        $fields[] = "description = ?";
        $values[] = $input['description'];
    }
    
    if (isset($input['points_threshold'])) {
        $fields[] = "points_threshold = ?";
        $values[] = $input['points_threshold'];
    }
    
    // Verifica se pelo menos um campo foi fornecido para atualizar
    if (empty($fields)) {
        jsonResponse(['error' => 'Nenhum campo para atualizar'], 400);
    }
    
    // Adiciona o ID do badge ao final dos valores
    $values[] = $input['id'];
    
    try {
        // Constrói e executa a query de atualização
        $stmt = $pdo->prepare("UPDATE badges SET " . implode(', ', $fields) . " WHERE id = ?");
        $stmt->execute($values);
        
        // Verifica se algum registro foi afetado
        if ($stmt->rowCount() > 0) {
            jsonResponse(['message' => 'Badge atualizado com sucesso']);
        } else {
            jsonResponse(['error' => 'Badge não encontrado'], 404);
        }
    } catch(PDOException $e) {
        // Retorna erro interno do servidor em caso de falha
        jsonResponse(['error' => 'Erro interno do servidor'], 500);
    }
}

// ========================================
// FUNÇÃO: DELETAR BADGE
// ========================================
/**
 * Remove um badge do sistema
 * 
 * @param int $id ID do badge a ser deletado
 * @param PDO $pdo Conexão com o banco de dados
 */
function deleteBadge($id, $pdo) {
    // Valida se o ID foi fornecido
    if (!$id) {
        jsonResponse(['error' => 'ID do badge é obrigatório'], 400);
    }
    
    try {
        // Prepara e executa a exclusão do badge
        $stmt = $pdo->prepare("DELETE FROM badges WHERE id = ?");
        $stmt->execute([$id]);
        
        // Verifica se algum registro foi afetado
        if ($stmt->rowCount() > 0) {
            jsonResponse(['message' => 'Badge deletado com sucesso']);
        } else {
            jsonResponse(['error' => 'Badge não encontrado'], 404);
        }
    } catch(PDOException $e) {
        // Retorna erro interno do servidor em caso de falha
        jsonResponse(['error' => 'Erro interno do servidor'], 500);
    }
}
?>

