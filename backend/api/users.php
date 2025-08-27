<?php
/**
 * API DE GERENCIAMENTO DE USUÁRIOS - HEALTHY HABITS
 * 
 * Este arquivo gerencia todas as operações relacionadas aos usuários do sistema:
 * - Criar novos usuários
 * - Listar usuários (todos ou específico)
 * - Atualizar dados de usuários existentes
 * - Deletar usuários
 * - Obter informações detalhadas de usuários (incluindo badges)
 * 
 * Endpoints disponíveis:
 * - GET /users.php - Listar todos os usuários
 * - GET /users.php?id=X - Obter usuário específico
 * - POST /users.php - Criar novo usuário
 * - PUT /users.php - Atualizar usuário existente
 * - DELETE /users.php?id=X - Deletar usuário
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
        // Verifica se foi solicitado um usuário específico por ID
        if (isset($_GET['id'])) {
            getUserById($_GET['id'], $pdo);
        } 
        // Se não especificado, retorna todos os usuários
        else {
            getAllUsers($pdo);
        }
        break;
    case 'POST':
        // Cria um novo usuário
        createUser($pdo);
        break;
    case 'PUT':
        // Atualiza um usuário existente
        updateUser($pdo);
        break;
    case 'DELETE':
        // Deleta um usuário específico
        deleteUser($_GET['id'], $pdo);
        break;
    default:
        // Retorna erro para métodos HTTP não suportados
        jsonResponse(['error' => 'Método não permitido'], 405);
}

// ========================================
// FUNÇÃO: LISTAR TODOS OS USUÁRIOS
// ========================================
/**
 * Retorna todos os usuários do sistema ordenados por pontos totais
 * 
 * @param PDO $pdo Conexão com o banco de dados
 */
function getAllUsers($pdo) {
    // Consulta SQL para buscar todos os usuários ordenados por pontos
    $stmt = $pdo->query("SELECT id, username, email, total_points FROM users ORDER BY total_points DESC");
    // Busca todos os resultados como array associativo
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Retorna a lista de usuários
    jsonResponse($users);
}

// ========================================
// FUNÇÃO: OBTER USUÁRIO POR ID
// ========================================
/**
 * Retorna um usuário específico por ID, incluindo seus badges conquistados
 * 
 * @param int $id ID do usuário a ser buscado
 * @param PDO $pdo Conexão com o banco de dados
 */
function getUserById($id, $pdo) {
    // Consulta SQL para buscar o usuário específico
    $stmt = $pdo->prepare("SELECT id, username, email, total_points FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Se o usuário foi encontrado
    if ($user) {
        // Busca todos os badges conquistados pelo usuário
        $stmt = $pdo->prepare("
            SELECT b.name, b.description, ub.awarded_date 
            FROM user_badges ub 
            JOIN badges b ON ub.badge_id = b.id 
            WHERE ub.user_id = ?
        ");
        $stmt->execute([$id]);
        // Adiciona os badges aos dados do usuário
        $user['badges'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Retorna o usuário com seus badges
        jsonResponse($user);
    } else {
        // Retorna erro se o usuário não foi encontrado
        jsonResponse(['error' => 'Usuário não encontrado'], 404);
    }
}

// ========================================
// FUNÇÃO: CRIAR NOVO USUÁRIO
// ========================================
/**
 * Cria um novo usuário no sistema
 * 
 * @param PDO $pdo Conexão com o banco de dados
 */
function createUser($pdo) {
    // Lê os dados JSON enviados na requisição
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Valida se os campos obrigatórios foram fornecidos
    if (!isset($input['username']) || !isset($input['email']) || !isset($input['password'])) {
        jsonResponse(['error' => 'Dados obrigatórios não fornecidos'], 400);
    }
    
    // Criptografa a senha usando hash seguro
    $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
    
    try {
        // Prepara e executa a inserção do novo usuário
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$input['username'], $input['email'], $hashedPassword]);
        
        // Obtém o ID do usuário recém-criado
        $userId = $pdo->lastInsertId();
        // Retorna sucesso com o ID do usuário criado
        jsonResponse(['id' => $userId, 'message' => 'Usuário criado com sucesso'], 201);
    } catch(PDOException $e) {
        // Verifica se o erro é de duplicação (username ou email já existem)
        if ($e->getCode() == 23000) {
            jsonResponse(['error' => 'Username ou email já existem'], 409);
        } else {
            // Retorna erro interno do servidor para outros tipos de erro
            jsonResponse(['error' => 'Erro interno do servidor'], 500);
        }
    }
}

// ========================================
// FUNÇÃO: ATUALIZAR USUÁRIO
// ========================================
/**
 * Atualiza um usuário existente no sistema
 * 
 * @param PDO $pdo Conexão com o banco de dados
 */
function updateUser($pdo) {
    // Lê os dados JSON enviados na requisição
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Valida se o ID do usuário foi fornecido
    if (!isset($input['id'])) {
        jsonResponse(['error' => 'ID do usuário é obrigatório'], 400);
    }
    
    // Arrays para construir a query de atualização dinamicamente
    $fields = [];
    $values = [];
    
    // Verifica cada campo opcional e adiciona à query se fornecido
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
    
    // Verifica se pelo menos um campo foi fornecido para atualizar
    if (empty($fields)) {
        jsonResponse(['error' => 'Nenhum campo para atualizar'], 400);
    }
    
    // Adiciona o ID do usuário ao final dos valores
    $values[] = $input['id'];
    
    try {
        // Constrói e executa a query de atualização
        $stmt = $pdo->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?");
        $stmt->execute($values);
        
        // Verifica se algum registro foi afetado
        if ($stmt->rowCount() > 0) {
            jsonResponse(['message' => 'Usuário atualizado com sucesso']);
        } else {
            jsonResponse(['error' => 'Usuário não encontrado'], 404);
        }
    } catch(PDOException $e) {
        // Retorna erro interno do servidor em caso de falha
        jsonResponse(['error' => 'Erro interno do servidor'], 500);
    }
}

// ========================================
// FUNÇÃO: DELETAR USUÁRIO
// ========================================
/**
 * Remove um usuário do sistema
 * 
 * @param int $id ID do usuário a ser deletado
 * @param PDO $pdo Conexão com o banco de dados
 */
function deleteUser($id, $pdo) {
    // Valida se o ID foi fornecido
    if (!$id) {
        jsonResponse(['error' => 'ID do usuário é obrigatório'], 400);
    }
    
    try {
        // Prepara e executa a exclusão do usuário
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        
        // Verifica se algum registro foi afetado
        if ($stmt->rowCount() > 0) {
            jsonResponse(['message' => 'Usuário deletado com sucesso']);
        } else {
            jsonResponse(['error' => 'Usuário não encontrado'], 404);
        }
    } catch(PDOException $e) {
        // Retorna erro interno do servidor em caso de falha
        jsonResponse(['error' => 'Erro interno do servidor'], 500);
    }
}
?>

