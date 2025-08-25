<?php
require_once '../config.php';

// Habilitar logs de erro para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getConnection();

switch($method) {
    case 'POST':
        if (isset($_GET['action'])) {
            switch($_GET['action']) {
                case 'login':
                    login($pdo);
                    break;
                case 'register':
                    register($pdo);
                    break;
                case 'logout':
                    logout();
                    break;
                default:
                    jsonResponse(['error' => 'Ação não reconhecida'], 400);
            }
        } else {
            jsonResponse(['error' => 'Ação não especificada'], 400);
        }
        break;
    case 'GET':
        if (isset($_GET['action']) && $_GET['action'] === 'check') {
            checkAuth();
        } else {
            jsonResponse(['error' => 'Método não permitido'], 405);
        }
        break;
    default:
        jsonResponse(['error' => 'Método não permitido'], 405);
}

function login($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Log para debug
    error_log("Login attempt - Username: " . ($input['username'] ?? 'not provided'));
    
    if (!isset($input['username']) || !isset($input['password'])) {
        jsonResponse(['error' => 'Username e password são obrigatórios'], 400);
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, password, total_points FROM users WHERE username = ?");
        $stmt->execute([$input['username']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Log para debug
        error_log("User found: " . ($user ? 'yes' : 'no'));
        if ($user) {
            error_log("Password verification: " . (password_verify($input['password'], $user['password']) ? 'success' : 'failed'));
        }
        
        if ($user && password_verify($input['password'], $user['password'])) {
            // Iniciar sessão
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            
            // Remover password da resposta
            unset($user['password']);
            
            jsonResponse([
                'message' => 'Login realizado com sucesso',
                'user' => $user
            ]);
        } else {
            jsonResponse(['error' => 'Username ou password incorretos'], 401);
        }
    } catch(PDOException $e) {
        error_log("Database error in login: " . $e->getMessage());
        jsonResponse(['error' => 'Erro interno do servidor'], 500);
    }
}

function register($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Log para debug
    error_log("Register attempt - Username: " . ($input['username'] ?? 'not provided') . ", Email: " . ($input['email'] ?? 'not provided'));
    
    if (!isset($input['username']) || !isset($input['email']) || !isset($input['password'])) {
        jsonResponse(['error' => 'Username, email e password são obrigatórios'], 400);
    }
    
    // Validar email
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['error' => 'Email inválido'], 400);
    }
    
    // Validar password (mínimo 6 caracteres)
    if (strlen($input['password']) < 6) {
        jsonResponse(['error' => 'Password deve ter pelo menos 6 caracteres'], 400);
    }
    
    try {
        // Verificar se username já existe
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$input['username']]);
        if ($stmt->fetch()) {
            jsonResponse(['error' => 'Username já existe'], 409);
        }
        
        // Verificar se email já existe
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$input['email']]);
        if ($stmt->fetch()) {
            jsonResponse(['error' => 'Email já existe'], 409);
        }
        
        // Criar usuário
        $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$input['username'], $input['email'], $hashedPassword]);
        
        $userId = $pdo->lastInsertId();
        
        // Iniciar sessão
        session_start();
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $input['username'];
        $_SESSION['email'] = $input['email'];
        
        error_log("User registered successfully - ID: $userId");
        
        jsonResponse([
            'message' => 'Usuário criado com sucesso',
            'user' => [
                'id' => $userId,
                'username' => $input['username'],
                'email' => $input['email'],
                'total_points' => 0
            ]
        ], 201);
    } catch(PDOException $e) {
        error_log("Database error in register: " . $e->getMessage());
        jsonResponse(['error' => 'Erro interno do servidor'], 500);
    }
}

function logout() {
    session_start();
    session_destroy();
    jsonResponse(['message' => 'Logout realizado com sucesso']);
}

function checkAuth() {
    session_start();
    if (isset($_SESSION['user_id'])) {
        jsonResponse([
            'authenticated' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'email' => $_SESSION['email']
            ]
        ]);
    } else {
        jsonResponse(['authenticated' => false], 401);
    }
}
?>
