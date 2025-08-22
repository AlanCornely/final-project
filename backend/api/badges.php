<?php
require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getConnection();

switch($method) {
    case 'GET':
        if (isset($_GET['user_id'])) {
            getBadgesByUser($_GET['user_id'], $pdo);
        } else {
            getAllBadges($pdo);
        }
        break;
    case 'POST':
        createBadge($pdo);
        break;
    case 'PUT':
        updateBadge($pdo);
        break;
    case 'DELETE':
        deleteBadge($_GET['id'], $pdo);
        break;
    default:
        jsonResponse(['error' => 'Método não permitido'], 405);
}

function getAllBadges($pdo) {
    $stmt = $pdo->query("SELECT * FROM badges ORDER BY points_threshold ASC");
    $badges = $stmt->fetchAll(PDO::FETCH_ASSOC);
    jsonResponse($badges);
}

function getBadgesByUser($userId, $pdo) {
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

function createBadge($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['name']) || !isset($input['points_threshold'])) {
        jsonResponse(['error' => 'Dados obrigatórios não fornecidos'], 400);
    }
    
    $description = $input['description'] ?? '';
    
    try {
        $stmt = $pdo->prepare("INSERT INTO badges (name, description, points_threshold) VALUES (?, ?, ?)");
        $stmt->execute([$input['name'], $description, $input['points_threshold']]);
        
        $badgeId = $pdo->lastInsertId();
        jsonResponse(['id' => $badgeId, 'message' => 'Badge criado com sucesso'], 201);
    } catch(PDOException $e) {
        if ($e->getCode() == 23000) {
            jsonResponse(['error' => 'Nome do badge já existe'], 409);
        } else {
            jsonResponse(['error' => 'Erro interno do servidor'], 500);
        }
    }
}

function updateBadge($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id'])) {
        jsonResponse(['error' => 'ID do badge é obrigatório'], 400);
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
    
    if (isset($input['points_threshold'])) {
        $fields[] = "points_threshold = ?";
        $values[] = $input['points_threshold'];
    }
    
    if (empty($fields)) {
        jsonResponse(['error' => 'Nenhum campo para atualizar'], 400);
    }
    
    $values[] = $input['id'];
    
    try {
        $stmt = $pdo->prepare("UPDATE badges SET " . implode(', ', $fields) . " WHERE id = ?");
        $stmt->execute($values);
        
        if ($stmt->rowCount() > 0) {
            jsonResponse(['message' => 'Badge atualizado com sucesso']);
        } else {
            jsonResponse(['error' => 'Badge não encontrado'], 404);
        }
    } catch(PDOException $e) {
        jsonResponse(['error' => 'Erro interno do servidor'], 500);
    }
}

function deleteBadge($id, $pdo) {
    if (!$id) {
        jsonResponse(['error' => 'ID do badge é obrigatório'], 400);
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM badges WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            jsonResponse(['message' => 'Badge deletado com sucesso']);
        } else {
            jsonResponse(['error' => 'Badge não encontrado'], 404);
        }
    } catch(PDOException $e) {
        jsonResponse(['error' => 'Erro interno do servidor'], 500);
    }
}
?>

