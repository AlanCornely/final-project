<?php
require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getConnection();

switch($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            getUserById($_GET['id'], $pdo);
        } else {
            getAllUsers($pdo);
        }
        break;
    case 'POST':
        createUser($pdo);
        break;
    case 'PUT':
        updateUser($pdo);
        break;
    case 'DELETE':
        deleteUser($_GET['id'], $pdo);
        break;
    default:
        jsonResponse(['error' => 'Método não permitido'], 405);
}

function getAllUsers($pdo) {
    $stmt = $pdo->query("SELECT id, username, email, total_points FROM users ORDER BY total_points DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    jsonResponse($users);
}

function getUserById($id, $pdo) {
    $stmt = $pdo->prepare("SELECT id, username, email, total_points FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Buscar badges do usuário
        $stmt = $pdo->prepare("
            SELECT b.name, b.description, ub.awarded_date 
            FROM user_badges ub 
            JOIN badges b ON ub.badge_id = b.id 
            WHERE ub.user_id = ?
        ");
        $stmt->execute([$id]);
        $user['badges'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        jsonResponse($user);
    } else {
        jsonResponse(['error' => 'Usuário não encontrado'], 404);
    }
}

function createUser($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['username']) || !isset($input['email']) || !isset($input['password'])) {
        jsonResponse(['error' => 'Dados obrigatórios não fornecidos'], 400);
    }
    
    $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$input['username'], $input['email'], $hashedPassword]);
        
        $userId = $pdo->lastInsertId();
        jsonResponse(['id' => $userId, 'message' => 'Usuário criado com sucesso'], 201);
    } catch(PDOException $e) {
        if ($e->getCode() == 23000) {
            jsonResponse(['error' => 'Username ou email já existem'], 409);
        } else {
            jsonResponse(['error' => 'Erro interno do servidor'], 500);
        }
    }
}

function updateUser($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id'])) {
        jsonResponse(['error' => 'ID do usuário é obrigatório'], 400);
    }
    
    $fields = [];
    $values = [];
    
    if (isset($input['username'])) {
        $fields[] = "username = ?";
        $values[] = $input['username'];
    }
    
    if (isset($input['email'])) {
        $fields[] = "email = ?";
        $values[] = $input['email'];
    }
    
    if (isset($input['total_points'])) {
        $fields[] = "total_points = ?";
        $values[] = $input['total_points'];
    }
    
    if (empty($fields)) {
        jsonResponse(['error' => 'Nenhum campo para atualizar'], 400);
    }
    
    $values[] = $input['id'];
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?");
        $stmt->execute($values);
        
        if ($stmt->rowCount() > 0) {
            jsonResponse(['message' => 'Usuário atualizado com sucesso']);
        } else {
            jsonResponse(['error' => 'Usuário não encontrado'], 404);
        }
    } catch(PDOException $e) {
        jsonResponse(['error' => 'Erro interno do servidor'], 500);
    }
}

function deleteUser($id, $pdo) {
    if (!$id) {
        jsonResponse(['error' => 'ID do usuário é obrigatório'], 400);
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            jsonResponse(['message' => 'Usuário deletado com sucesso']);
        } else {
            jsonResponse(['error' => 'Usuário não encontrado'], 404);
        }
    } catch(PDOException $e) {
        jsonResponse(['error' => 'Erro interno do servidor'], 500);
    }
}
?>

