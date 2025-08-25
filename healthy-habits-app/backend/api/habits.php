<?php
require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getConnection();

switch($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            getHabitById($_GET['id'], $pdo);
        } elseif (isset($_GET['user_id'])) {
            getHabitsByUser($_GET['user_id'], $pdo);
        } else {
            getAllHabits($pdo);
        }
        break;
    case 'POST':
        createHabit($pdo);
        break;
    case 'PUT':
        updateHabit($pdo);
        break;
    case 'DELETE':
        deleteHabit($_GET['id'], $pdo);
        break;
    default:
        jsonResponse(['error' => 'Método não permitido'], 405);
}

function getAllHabits($pdo) {
    $stmt = $pdo->query("
        SELECT h.*, u.username 
        FROM habits h 
        JOIN users u ON h.user_id = u.id 
        ORDER BY h.id DESC
    ");
    $habits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    jsonResponse($habits);
}

function getHabitById($id, $pdo) {
    $stmt = $pdo->prepare("
        SELECT h.*, u.username 
        FROM habits h 
        JOIN users u ON h.user_id = u.id 
        WHERE h.id = ?
    ");
    $stmt->execute([$id]);
    $habit = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($habit) {
        // Buscar completions do hábito
        $stmt = $pdo->prepare("
            SELECT * FROM completions 
            WHERE habit_id = ? 
            ORDER BY completion_date DESC
        ");
        $stmt->execute([$id]);
        $habit['completions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        jsonResponse($habit);
    } else {
        jsonResponse(['error' => 'Hábito não encontrado'], 404);
    }
}

function getHabitsByUser($userId, $pdo) {
    $stmt = $pdo->prepare("
        SELECT h.*, 
               COUNT(c.id) as total_completions,
               MAX(c.completion_date) as last_completion
        FROM habits h 
        LEFT JOIN completions c ON h.id = c.habit_id 
        WHERE h.user_id = ? 
        GROUP BY h.id 
        ORDER BY h.id DESC
    ");
    $stmt->execute([$userId]);
    $habits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    jsonResponse($habits);
}

function createHabit($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['user_id']) || !isset($input['name'])) {
        jsonResponse(['error' => 'Dados obrigatórios não fornecidos'], 400);
    }
    
    $description = $input['description'] ?? '';
    $pointsPerCompletion = $input['points_per_completion'] ?? 10;
    $rewardDescription = $input['reward_description'] ?? '';
    
    try {
        $stmt = $pdo->prepare("INSERT INTO habits (user_id, name, description, points_per_completion, reward_description) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$input['user_id'], $input['name'], $description, $pointsPerCompletion, $rewardDescription]);
        
        $habitId = $pdo->lastInsertId();
        jsonResponse(['id' => $habitId, 'message' => 'Hábito criado com sucesso'], 201);
    } catch(PDOException $e) {
        jsonResponse(['error' => 'Erro interno do servidor'], 500);
    }
}

function updateHabit($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id'])) {
        jsonResponse(['error' => 'ID do hábito é obrigatório'], 400);
    }
    
    $fields = [];
    $values = [];
    
    if (isset($input['name'])) {
        $fields[] = "name = ?";
        $values[] = $input['name'];
    }
    
    if (isset($input['description'])) {
        $fields[] = "description = ?";
        $values[] = $input['description'];
    }
    
    if (isset($input['points_per_completion'])) {
        $fields[] = "points_per_completion = ?";
        $values[] = $input['points_per_completion'];
    }
    
    if (isset($input['reward_description'])) {
        $fields[] = "reward_description = ?";
        $values[] = $input['reward_description'];
    }
    
    if (empty($fields)) {
        jsonResponse(['error' => 'Nenhum campo para atualizar'], 400);
    }
    
    $values[] = $input['id'];
    
    try {
        $stmt = $pdo->prepare("UPDATE habits SET " . implode(', ', $fields) . " WHERE id = ?");
        $stmt->execute($values);
        
        if ($stmt->rowCount() > 0) {
            jsonResponse(['message' => 'Hábito atualizado com sucesso']);
        } else {
            jsonResponse(['error' => 'Hábito não encontrado'], 404);
        }
    } catch(PDOException $e) {
        jsonResponse(['error' => 'Erro interno do servidor'], 500);
    }
}

function deleteHabit($id, $pdo) {
    if (!$id) {
        jsonResponse(['error' => 'ID do hábito é obrigatório'], 400);
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM habits WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            jsonResponse(['message' => 'Hábito deletado com sucesso']);
        } else {
            jsonResponse(['error' => 'Hábito não encontrado'], 404);
        }
    } catch(PDOException $e) {
        jsonResponse(['error' => 'Erro interno do servidor'], 500);
    }
}
?>

