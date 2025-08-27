<?php
/**
 * API DE GERENCIAMENTO DE COMPLETAÇÕES - HEALTHY HABITS
 * 
 * Este arquivo gerencia todas as operações relacionadas às completações de hábitos:
 * - Registrar quando um usuário completa um hábito
 * - Listar completações (todas, por usuário, ou por hábito)
 * - Deletar completações (com ajuste automático de pontos)
 * - Sistema automático de atribuição de badges
 * 
 * Endpoints disponíveis:
 * - GET /completions.php - Listar todas as completações
 * - GET /completions.php?user_id=X - Listar completações de um usuário
 * - GET /completions.php?habit_id=X - Listar completações de um hábito
 * - POST /completions.php - Registrar nova completação
 * - DELETE /completions.php?id=X - Deletar completação
 */

// Inclui o arquivo de configuração com funções utilitárias
require_once '../config.php';

// ========================================
// VARIÁVEIS INICIAIS
// ========================================
// Obtém o método HTTP da requisição (GET, POST, DELETE)
$method = $_SERVER['REQUEST_METHOD'];
// Estabelece conexão com o banco de dados
$pdo = getConnection();

// ========================================
// ROTEAMENTO PRINCIPAL
// ========================================
// Direciona a requisição para a função apropriada baseada no método HTTP
switch($method) {
    case 'GET':
        // Verifica se foi solicitado completações de um usuário específico
        if (isset($_GET['user_id'])) {
            getCompletionsByUser($_GET['user_id'], $pdo);
        } 
        // Verifica se foi solicitado completações de um hábito específico
        elseif (isset($_GET['habit_id'])) {
            getCompletionsByHabit($_GET['habit_id'], $pdo);
        } 
        // Se não especificado, retorna todas as completações
        else {
            getAllCompletions($pdo);
        }
        break;
    case 'POST':
        // Registra uma nova completação de hábito
        createCompletion($pdo);
        break;
    case 'DELETE':
        // Remove uma completação específica
        deleteCompletion($_GET['id'], $pdo);
        break;
    default:
        // Retorna erro para métodos HTTP não suportados
        jsonResponse(['error' => 'Método não permitido'], 405);
}

// ========================================
// FUNÇÃO: LISTAR TODAS AS COMPLETAÇÕES
// ========================================
/**
 * Retorna todas as completações do sistema com informações do hábito e usuário
 * 
 * @param PDO $pdo Conexão com o banco de dados
 */
function getAllCompletions($pdo) {
    // Consulta SQL que junta completações com informações do hábito e usuário
    $stmt = $pdo->query("
        SELECT c.*, h.name as habit_name, u.username 
        FROM completions c 
        JOIN habits h ON c.habit_id = h.id 
        JOIN users u ON c.user_id = u.id 
        ORDER BY c.completion_date DESC
    ");
    // Busca todos os resultados como array associativo
    $completions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Retorna a lista de completações
    jsonResponse($completions);
}

// ========================================
// FUNÇÃO: LISTAR COMPLETAÇÕES POR USUÁRIO
// ========================================
/**
 * Retorna todas as completações de um usuário específico
 * 
 * @param int $userId ID do usuário
 * @param PDO $pdo Conexão com o banco de dados
 */
function getCompletionsByUser($userId, $pdo) {
    // Consulta SQL que junta completações com informações do hábito
    $stmt = $pdo->prepare("
        SELECT c.*, h.name as habit_name 
        FROM completions c 
        JOIN habits h ON c.habit_id = h.id 
        WHERE c.user_id = ? 
        ORDER BY c.completion_date DESC
    ");
    $stmt->execute([$userId]);
    $completions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    jsonResponse($completions);
}

// ========================================
// FUNÇÃO: LISTAR COMPLETAÇÕES POR HÁBITO
// ========================================
/**
 * Retorna todas as completações de um hábito específico
 * 
 * @param int $habitId ID do hábito
 * @param PDO $pdo Conexão com o banco de dados
 */
function getCompletionsByHabit($habitId, $pdo) {
    // Consulta SQL que junta completações com informações do usuário
    $stmt = $pdo->prepare("
        SELECT c.*, u.username 
        FROM completions c 
        JOIN users u ON c.user_id = u.id 
        WHERE c.habit_id = ? 
        ORDER BY c.completion_date DESC
    ");
    $stmt->execute([$habitId]);
    $completions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    jsonResponse($completions);
}

// ========================================
// FUNÇÃO: CRIAR NOVA COMPLETAÇÃO
// ========================================
/**
 * Registra uma nova completação de hábito e atualiza pontos do usuário
 * Também verifica e atribui badges automaticamente
 * 
 * @param PDO $pdo Conexão com o banco de dados
 */
function createCompletion($pdo) {
    // Lê os dados JSON enviados na requisição
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Valida se os campos obrigatórios foram fornecidos
    if (!isset($input['habit_id']) || !isset($input['user_id'])) {
        jsonResponse(['error' => 'Dados obrigatórios não fornecidos'], 400);
    }
    
    try {
        // Busca os pontos que o hábito oferece por completação
        $stmt = $pdo->prepare("SELECT points_per_completion FROM habits WHERE id = ?");
        $stmt->execute([$input['habit_id']]);
        $habit = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verifica se o hábito existe
        if (!$habit) {
            jsonResponse(['error' => 'Hábito não encontrado'], 404);
        }
        
        // Define os pontos ganhos baseado no hábito
        $pointsEarned = $habit['points_per_completion'];
        
        // Inicia uma transação para garantir consistência dos dados
        $pdo->beginTransaction();
        
        // Cria o registro da completação
        $stmt = $pdo->prepare("INSERT INTO completions (habit_id, user_id, points_earned) VALUES (?, ?, ?)");
        $stmt->execute([$input['habit_id'], $input['user_id'], $pointsEarned]);
        
        // Atualiza os pontos totais do usuário
        $stmt = $pdo->prepare("UPDATE users SET total_points = total_points + ? WHERE id = ?");
        $stmt->execute([$pointsEarned, $input['user_id']]);
        
        // Verifica se o usuário conquistou novos badges
        checkAndAwardBadges($input['user_id'], $pdo);
        
        // Confirma todas as operações
        $pdo->commit();
        
        // Obtém o ID da completação recém-criada
        $completionId = $pdo->lastInsertId();
        
        // Retorna sucesso com informações da completação
        jsonResponse([
            'id' => $completionId, 
            'points_earned' => $pointsEarned,
            'message' => 'Hábito completado com sucesso!'
        ], 201);
        
    } catch(PDOException $e) {
        // Em caso de erro, desfaz todas as operações
        $pdo->rollBack();
        jsonResponse(['error' => 'Erro interno do servidor'], 500);
    }
}

// ========================================
// FUNÇÃO: DELETAR COMPLETAÇÃO
// ========================================
/**
 * Remove uma completação e ajusta os pontos do usuário automaticamente
 * 
 * @param int $id ID da completação a ser deletada
 * @param PDO $pdo Conexão com o banco de dados
 */
function deleteCompletion($id, $pdo) {
    // Valida se o ID foi fornecido
    if (!$id) {
        jsonResponse(['error' => 'ID da completion é obrigatório'], 400);
    }
    
    try {
        // Busca os dados da completação antes de deletar
        $stmt = $pdo->prepare("SELECT user_id, points_earned FROM completions WHERE id = ?");
        $stmt->execute([$id]);
        $completion = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verifica se a completação existe
        if (!$completion) {
            jsonResponse(['error' => 'Completion não encontrada'], 404);
        }
        
        // Inicia uma transação para garantir consistência dos dados
        $pdo->beginTransaction();
        
        // Remove a completação
        $stmt = $pdo->prepare("DELETE FROM completions WHERE id = ?");
        $stmt->execute([$id]);
        
        // Subtrai os pontos do usuário
        $stmt = $pdo->prepare("UPDATE users SET total_points = total_points - ? WHERE id = ?");
        $stmt->execute([$completion['points_earned'], $completion['user_id']]);
        
        // Confirma todas as operações
        $pdo->commit();
        
        // Retorna mensagem de sucesso
        jsonResponse(['message' => 'Completion deletada com sucesso']);
        
    } catch(PDOException $e) {
        // Em caso de erro, desfaz todas as operações
        $pdo->rollBack();
        jsonResponse(['error' => 'Erro interno do servidor'], 500);
    }
}

// ========================================
// FUNÇÃO: VERIFICAR E ATRIBUIR BADGES
// ========================================
/**
 * Verifica se o usuário conquistou novos badges baseado em seus pontos totais
 * e atribui automaticamente os badges desbloqueados
 * 
 * @param int $userId ID do usuário
 * @param PDO $pdo Conexão com o banco de dados
 */
function checkAndAwardBadges($userId, $pdo) {
    // Busca os pontos totais atuais do usuário
    $stmt = $pdo->prepare("SELECT total_points FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Se o usuário não existe, interrompe a execução
    if (!$user) return;
    
    // Busca badges disponíveis que o usuário ainda não possui
    // Filtra por badges cujo limite de pontos foi atingido
    $stmt = $pdo->prepare("
        SELECT b.* FROM badges b 
        WHERE b.points_threshold <= ? 
        AND b.id NOT IN (
            SELECT badge_id FROM user_badges WHERE user_id = ?
        )
    ");
    $stmt->execute([$user['total_points'], $userId]);
    $availableBadges = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Atribui cada badge disponível ao usuário
    foreach ($availableBadges as $badge) {
        $stmt = $pdo->prepare("INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?)");
        $stmt->execute([$userId, $badge['id']]);
    }
}
?>

