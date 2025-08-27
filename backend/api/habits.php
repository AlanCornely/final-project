<?php
/**
 * API DE GERENCIAMENTO DE HÁBITOS - HEALTHY HABITS
 * 
 * Este arquivo gerencia todas as operações relacionadas aos hábitos dos usuários:
 * - Criar novos hábitos
 * - Listar hábitos (todos, por usuário, ou específico)
 * - Atualizar hábitos existentes
 * - Deletar hábitos
 * 
 * Endpoints disponíveis:
 * - GET /habits.php - Listar todos os hábitos
 * - GET /habits.php?id=X - Obter hábito específico
 * - GET /habits.php?user_id=X - Listar hábitos de um usuário
 * - POST /habits.php - Criar novo hábito
 * - PUT /habits.php - Atualizar hábito existente
 * - DELETE /habits.php?id=X - Deletar hábito
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
        // Verifica se foi solicitado um hábito específico por ID
        if (isset($_GET['id'])) {
            getHabitById($_GET['id'], $pdo);
        } 
        // Verifica se foi solicitado hábitos de um usuário específico
        elseif (isset($_GET['user_id'])) {
            getHabitsByUser($_GET['user_id'], $pdo);
        } 
        // Se não especificado, retorna todos os hábitos
        else {
            getAllHabits($pdo);
        }
        break;
    case 'POST':
        // Cria um novo hábito
        createHabit($pdo);
        break;
    case 'PUT':
        // Atualiza um hábito existente
        updateHabit($pdo);
        break;
    case 'DELETE':
        // Deleta um hábito específico
        deleteHabit($_GET['id'], $pdo);
        break;
    default:
        // Retorna erro para métodos HTTP não suportados
        jsonResponse(['error' => 'Método não permitido'], 405);
}

// ========================================
// FUNÇÃO: LISTAR TODOS OS HÁBITOS
// ========================================
/**
 * Retorna todos os hábitos do sistema com informações do usuário criador
 * 
 * @param PDO $pdo Conexão com o banco de dados
 */
function getAllHabits($pdo) {
    // Consulta SQL que junta hábitos com informações do usuário criador
    $stmt = $pdo->query("
        SELECT h.*, u.username 
        FROM habits h 
        JOIN users u ON h.user_id = u.id 
        ORDER BY h.id DESC
    ");
    // Busca todos os resultados como array associativo
    $habits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Retorna a lista de hábitos
    jsonResponse($habits);
}

// ========================================
// FUNÇÃO: OBTER HÁBITO POR ID
// ========================================
/**
 * Retorna um hábito específico por ID, incluindo suas completações
 * 
 * @param int $id ID do hábito a ser buscado
 * @param PDO $pdo Conexão com o banco de dados
 */
function getHabitById($id, $pdo) {
    // Consulta SQL para buscar o hábito específico com informações do usuário
    $stmt = $pdo->prepare("
        SELECT h.*, u.username 
        FROM habits h 
        JOIN users u ON h.user_id = u.id 
        WHERE h.id = ?
    ");
    $stmt->execute([$id]);
    $habit = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Se o hábito foi encontrado
    if ($habit) {
        // Busca todas as completações deste hábito
        $stmt = $pdo->prepare("
            SELECT * FROM completions 
            WHERE habit_id = ? 
            ORDER BY completion_date DESC
        ");
        $stmt->execute([$id]);
        // Adiciona as completações aos dados do hábito
        $habit['completions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Retorna o hábito com suas completações
        jsonResponse($habit);
    } else {
        // Retorna erro se o hábito não foi encontrado
        jsonResponse(['error' => 'Hábito não encontrado'], 404);
    }
}

// ========================================
// FUNÇÃO: LISTAR HÁBITOS POR USUÁRIO
// ========================================
/**
 * Retorna todos os hábitos de um usuário específico com estatísticas
 * 
 * @param int $userId ID do usuário
 * @param PDO $pdo Conexão com o banco de dados
 */
function getHabitsByUser($userId, $pdo) {
    // Consulta SQL que junta hábitos com estatísticas de completação
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

// ========================================
// FUNÇÃO: CRIAR NOVO HÁBITO
// ========================================
/**
 * Cria um novo hábito no sistema
 * 
 * @param PDO $pdo Conexão com o banco de dados
 */
function createHabit($pdo) {
    // Lê os dados JSON enviados na requisição
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Valida se os campos obrigatórios foram fornecidos
    if (!isset($input['user_id']) || !isset($input['name'])) {
        jsonResponse(['error' => 'Dados obrigatórios não fornecidos'], 400);
    }
    
    // Define valores padrão para campos opcionais
    $description = $input['description'] ?? '';
    $pointsPerCompletion = $input['points_per_completion'] ?? 10;
    $rewardDescription = $input['reward_description'] ?? '';
    
    try {
        // Prepara e executa a inserção do novo hábito
        $stmt = $pdo->prepare("INSERT INTO habits (user_id, name, description, points_per_completion, reward_description) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$input['user_id'], $input['name'], $description, $pointsPerCompletion, $rewardDescription]);
        
        // Obtém o ID do hábito recém-criado
        $habitId = $pdo->lastInsertId();
        // Retorna sucesso com o ID do hábito criado
        jsonResponse(['id' => $habitId, 'message' => 'Hábito criado com sucesso'], 201);
    } catch(PDOException $e) {
        // Retorna erro interno do servidor em caso de falha
        jsonResponse(['error' => 'Erro interno do servidor'], 500);
    }
}

// ========================================
// FUNÇÃO: ATUALIZAR HÁBITO
// ========================================
/**
 * Atualiza um hábito existente no sistema
 * 
 * @param PDO $pdo Conexão com o banco de dados
 */
function updateHabit($pdo) {
    // Lê os dados JSON enviados na requisição
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Valida se o ID do hábito foi fornecido
    if (!isset($input['id'])) {
        jsonResponse(['error' => 'ID do hábito é obrigatório'], 400);
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
    
    if (isset($input['points_per_completion'])) {
        $fields[] = "points_per_completion = ?";
        $values[] = $input['points_per_completion'];
    }
    
    if (isset($input['reward_description'])) {
        $fields[] = "reward_description = ?";
        $values[] = $input['reward_description'];
    }
    
    // Verifica se pelo menos um campo foi fornecido para atualizar
    if (empty($fields)) {
        jsonResponse(['error' => 'Nenhum campo para atualizar'], 400);
    }
    
    // Adiciona o ID do hábito ao final dos valores
    $values[] = $input['id'];
    
    try {
        // Constrói e executa a query de atualização
        $stmt = $pdo->prepare("UPDATE habits SET " . implode(', ', $fields) . " WHERE id = ?");
        $stmt->execute($values);
        
        // Verifica se algum registro foi afetado
        if ($stmt->rowCount() > 0) {
            jsonResponse(['message' => 'Hábito atualizado com sucesso']);
        } else {
            jsonResponse(['error' => 'Hábito não encontrado'], 404);
        }
    } catch(PDOException $e) {
        // Retorna erro interno do servidor em caso de falha
        jsonResponse(['error' => 'Erro interno do servidor'], 500);
    }
}

// ========================================
// FUNÇÃO: DELETAR HÁBITO
// ========================================
/**
 * Remove um hábito do sistema
 * 
 * @param int $id ID do hábito a ser deletado
 * @param PDO $pdo Conexão com o banco de dados
 */
function deleteHabit($id, $pdo) {
    // Valida se o ID foi fornecido
    if (!$id) {
        jsonResponse(['error' => 'ID do hábito é obrigatório'], 400);
    }
    
    try {
        // Prepara e executa a exclusão do hábito
        $stmt = $pdo->prepare("DELETE FROM habits WHERE id = ?");
        $stmt->execute([$id]);
        
        // Verifica se algum registro foi afetado
        if ($stmt->rowCount() > 0) {
            jsonResponse(['message' => 'Hábito deletado com sucesso']);
        } else {
            jsonResponse(['error' => 'Hábito não encontrado'], 404);
        }
    } catch(PDOException $e) {
        // Retorna erro interno do servidor em caso de falha
        jsonResponse(['error' => 'Erro interno do servidor'], 500);
    }
}
?>

