<?php
require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getConnection();

switch($method) {
    case 'GET':
        if (isset($_GET['user_id'])) {
            getCompletionsByUser($_GET['user_id'], $pdo);
        } elseif (isset($_GET['habit_id'])) {
            getCompletionsByHabit($_GET['habit_id'], $pdo);
        } else {
            getAllCompletions($pdo);
        }
        break;
    case 'POST':
        createCompletion($pdo);
        break;
    case 'DELETE':
        deleteCompletion($_GET['id'], $pdo);
        break;
    default:
        jsonResponse(['error' => 'Método não permitido'], 405);
}

function getAllCompletions($pdo) {
    $stmt = $pdo->query("
        SELECT c.*, h.name as habit_name, u.username 
        FROM completions c 
        JOIN habits h ON c.habit_id = h.id 
        JOIN users u ON c.user_id = u.id 
        ORDER BY c.completion_date DESC
    ");
    $completions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    jsonResponse($completions);
}

function getCompletionsByUser($userId, $pdo) {
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

function getCompletionsByHabit($habitId, $pdo) {
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

function createCompletion($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['habit_id']) || !isset($input['user_id'])) {
        jsonResponse(['error' => 'Dados obrigatórios não fornecidos'], 400);
    }
    
    try {
        // Buscar pontos do hábito
        $stmt = $pdo->prepare("SELECT points_per_completion FROM habits WHERE id = ?");
        $stmt->execute([$input['habit_id']]);
        $habit = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$habit) {
            jsonResponse(['error' => 'Hábito não encontrado'], 404);
        }
        
        $pointsEarned = $habit['points_per_completion'];
        
        // Iniciar transação
        $pdo->beginTransaction();
        
        // Criar completion
        $stmt = $pdo->prepare("INSERT INTO completions (habit_id, user_id, points_earned) VALUES (?, ?, ?)");
        $stmt->execute([$input['habit_id'], $input['user_id'], $pointsEarned]);
        
        // Atualizar pontos do usuário
        $stmt = $pdo->prepare("UPDATE users SET total_points = total_points + ? WHERE id = ?");
        $stmt->execute([$pointsEarned, $input['user_id']]);
        
        // Verificar e atribuir badges
        checkAndAwardBadges($input['user_id'], $pdo);
        
        $pdo->commit();
        
        $completionId = $pdo->lastInsertId();
        jsonResponse([
            'id' => $completionId, 
            'points_earned' => $pointsEarned,
            'message' => 'Hábito completado com sucesso!'
        ], 201);
        
    } catch(PDOException $e) {
        $pdo->rollBack();
        jsonResponse(['error' => 'Erro interno do servidor'], 500);
    }
}

function deleteCompletion($id, $pdo) {
    if (!$id) {
        jsonResponse(['error' => 'ID da completion é obrigatório'], 400);
    }
    
    try {
        // Buscar dados da completion antes de deletar
        $stmt = $pdo->prepare("SELECT user_id, points_earned FROM completions WHERE id = ?");
        $stmt->execute([$id]);
        $completion = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$completion) {
            jsonResponse(['error' => 'Completion não encontrada'], 404);
        }
        
        // Iniciar transação
        $pdo->beginTransaction();
        
        // Deletar completion
        $stmt = $pdo->prepare("DELETE FROM completions WHERE id = ?");
        $stmt->execute([$id]);
        
        // Subtrair pontos do usuário
        $stmt = $pdo->prepare("UPDATE users SET total_points = total_points - ? WHERE id = ?");
        $stmt->execute([$completion['points_earned'], $completion['user_id']]);
        
        $pdo->commit();
        
        jsonResponse(['message' => 'Completion deletada com sucesso']);
        
    } catch(PDOException $e) {
        $pdo->rollBack();
        jsonResponse(['error' => 'Erro interno do servidor'], 500);
    }
}

function checkAndAwardBadges($userId, $pdo) {
    // Buscar pontos totais do usuário
    $stmt = $pdo->prepare("SELECT total_points FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) return;
    
    // Buscar badges disponíveis que o usuário ainda não possui
    $stmt = $pdo->prepare("
        SELECT b.* FROM badges b 
        WHERE b.points_threshold <= ? 
        AND b.id NOT IN (
            SELECT badge_id FROM user_badges WHERE user_id = ?
        )
    ");
    $stmt->execute([$user['total_points'], $userId]);
    $availableBadges = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Atribuir badges
    foreach ($availableBadges as $badge) {
        $stmt = $pdo->prepare("INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?)");
        $stmt->execute([$userId, $badge['id']]);
    }
}
?>

