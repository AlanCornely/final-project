<?php
/**
 * API DE AUTENTICAÇÃO - HEALTHY HABITS
 * 
 * Este arquivo gerencia todas as operações relacionadas à autenticação de usuários:
 * - Login de usuários existentes
 * - Registro de novos usuários
 * - Logout de usuários
 * - Verificação de status de autenticação
 * 
 * Endpoints disponíveis:
 * - POST /auth.php?action=login - Fazer login
 * - POST /auth.php?action=register - Registrar novo usuário
 * - POST /auth.php?action=logout - Fazer logout
 * - GET /auth.php?action=check - Verificar status de autenticação
 */

// Inclui o arquivo de configuração com funções utilitárias
require_once '../config.php';

// ========================================
// CONFIGURAÇÕES DE DEBUG
// ========================================
// Habilita todos os tipos de erro para facilitar o debug
error_reporting(E_ALL);
// Exibe erros na tela (deve ser desabilitado em produção)
ini_set('display_errors', 1);

// ========================================
// VARIÁVEIS INICIAIS
// ========================================
// Obtém o método HTTP da requisição (GET, POST, etc.)
$method = $_SERVER['REQUEST_METHOD'];
// Estabelece conexão com o banco de dados
$pdo = getConnection();

// ========================================
// ROTEAMENTO PRINCIPAL
// ========================================
// Direciona a requisição para a função apropriada baseada no método HTTP
switch($method) {
    case 'POST':
        // Verifica se foi especificada uma ação na URL
        if (isset($_GET['action'])) {
            switch($_GET['action']) {
                case 'login':
                    login($pdo);           // Chama função de login
                    break;
                case 'register':
                    register($pdo);        // Chama função de registro
                    break;
                case 'logout':
                    logout();              // Chama função de logout
                    break;
                default:
                    // Retorna erro se a ação não for reconhecida
                    jsonResponse(['error' => 'Ação não reconhecida'], 400);
            }
        } else {
            // Retorna erro se nenhuma ação foi especificada
            jsonResponse(['error' => 'Ação não especificada'], 400);
        }
        break;
    case 'GET':
        // Verifica se a ação é 'check' para verificar autenticação
        if (isset($_GET['action']) && $_GET['action'] === 'check') {
            checkAuth();
        } else {
            // Retorna erro para outros métodos GET
            jsonResponse(['error' => 'Método não permitido'], 405);
        }
        break;
    default:
        // Retorna erro para métodos HTTP não suportados
        jsonResponse(['error' => 'Método não permitido'], 405);
}

// ========================================
// FUNÇÃO DE LOGIN
// ========================================
/**
 * Autentica um usuário existente usando username e password
 * 
 * @param PDO $pdo Conexão com o banco de dados
 */
function login($pdo) {
    // Lê os dados JSON enviados na requisição
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Log para debug - registra tentativa de login
    error_log("Login attempt - Username: " . ($input['username'] ?? 'not provided'));
    
    // Valida se username e password foram fornecidos
    if (!isset($input['username']) || !isset($input['password'])) {
        jsonResponse(['error' => 'Username e password são obrigatórios'], 400);
    }
    
    try {
        // Prepara e executa consulta para buscar usuário pelo username
        $stmt = $pdo->prepare("SELECT id, username, email, password, total_points FROM users WHERE username = ?");
        $stmt->execute([$input['username']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Logs para debug - verifica se usuário foi encontrado e se a senha está correta
        error_log("User found: " . ($user ? 'yes' : 'no'));
        if ($user) {
            error_log("Password verification: " . (password_verify($input['password'], $user['password']) ? 'success' : 'failed'));
        }
        
        // Verifica se usuário existe e se a senha está correta
        if ($user && password_verify($input['password'], $user['password'])) {
            // Inicia a sessão PHP
            session_start();
            
            // Armazena dados do usuário na sessão
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            
            // Remove a senha dos dados retornados por segurança
            unset($user['password']);
            
            // Retorna sucesso com dados do usuário
            jsonResponse([
                'message' => 'Login realizado com sucesso',
                'user' => $user
            ]);
        } else {
            // Retorna erro se credenciais estiverem incorretas
            jsonResponse(['error' => 'Username ou password incorretos'], 401);
        }
    } catch(PDOException $e) {
        // Log do erro e retorna erro interno do servidor
        error_log("Database error in login: " . $e->getMessage());
        jsonResponse(['error' => 'Erro interno do servidor'], 500);
    }
}

// ========================================
// FUNÇÃO DE REGISTRO
// ========================================
/**
 * Registra um novo usuário no sistema
 * 
 * @param PDO $pdo Conexão com o banco de dados
 */
function register($pdo) {
    // Lê os dados JSON enviados na requisição
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Log para debug - registra tentativa de registro
    error_log("Register attempt - Username: " . ($input['username'] ?? 'not provided') . ", Email: " . ($input['email'] ?? 'not provided'));
    
    // Valida se todos os campos obrigatórios foram fornecidos
    if (!isset($input['username']) || !isset($input['email']) || !isset($input['password'])) {
        jsonResponse(['error' => 'Username, email e password são obrigatórios'], 400);
    }
    
    // Valida formato do email usando função nativa do PHP
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['error' => 'Email inválido'], 400);
    }
    
    // Valida tamanho mínimo da senha (6 caracteres)
    if (strlen($input['password']) < 6) {
        jsonResponse(['error' => 'Password deve ter pelo menos 6 caracteres'], 400);
    }
    
    try {
        // Verifica se o username já existe no banco
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$input['username']]);
        if ($stmt->fetch()) {
            jsonResponse(['error' => 'Username já existe'], 409);
        }
        
        // Verifica se o email já existe no banco
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$input['email']]);
        if ($stmt->fetch()) {
            jsonResponse(['error' => 'Email já existe'], 409);
        }
        
        // Cria o novo usuário no banco de dados
        // A senha é criptografada usando hash seguro
        $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$input['username'], $input['email'], $hashedPassword]);
        
        // Obtém o ID do usuário recém-criado
        $userId = $pdo->lastInsertId();
        
        // Inicia a sessão PHP para o usuário recém-registrado
        session_start();
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $input['username'];
        $_SESSION['email'] = $input['email'];
        
        // Log de sucesso
        error_log("User registered successfully - ID: $userId");
        
        // Retorna sucesso com dados do usuário criado
        jsonResponse([
            'message' => 'Usuário criado com sucesso',
            'user' => [
                'id' => $userId,
                'username' => $input['username'],
                'email' => $input['email'],
                'total_points' => 0  // Novo usuário começa com 0 pontos
            ]
        ], 201);  // Código 201 = Created
    } catch(PDOException $e) {
        // Log do erro e retorna erro interno do servidor
        error_log("Database error in register: " . $e->getMessage());
        jsonResponse(['error' => 'Erro interno do servidor'], 500);
    }
}

// ========================================
// FUNÇÃO DE LOGOUT
// ========================================
/**
 * Encerra a sessão do usuário atual
 */
function logout() {
    // Inicia a sessão para poder destruí-la
    session_start();
    // Destrói completamente a sessão
    session_destroy();
    // Retorna mensagem de sucesso
    jsonResponse(['message' => 'Logout realizado com sucesso']);
}

// ========================================
// FUNÇÃO DE VERIFICAÇÃO DE AUTENTICAÇÃO
// ========================================
/**
 * Verifica se o usuário atual está autenticado
 * Retorna os dados do usuário se estiver logado
 */
function checkAuth() {
    // Inicia a sessão para verificar se existe
    session_start();
    
    // Verifica se existe um user_id na sessão (usuário logado)
    if (isset($_SESSION['user_id'])) {
        // Retorna dados do usuário autenticado
        jsonResponse([
            'authenticated' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'email' => $_SESSION['email']
            ]
        ]);
    } else {
        // Retorna que não está autenticado
        jsonResponse(['authenticated' => false], 401);
    }
}
?>
