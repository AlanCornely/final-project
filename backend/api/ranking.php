<?php
/**
 * API DE RANKING - HEALTHY HABITS
 * 
 * Este arquivo gerencia o sistema de ranking dos usuários baseado em:
 * - Pontos totais acumulados
 * - Número de badges conquistados
 * - Número de completações realizadas
 * 
 * Endpoints disponíveis:
 * - GET /ranking.php - Obter ranking geral dos usuários
 * 
 * Parâmetros de consulta suportados:
 * - search: Filtrar por nome de usuário
 * - order_by: Ordenar por (points, username, badges_count)
 * - order_dir: Direção da ordenação (ASC, DESC)
 * - limit: Limite de resultados (1-100)
 */

// Inclui o arquivo de configuração com funções utilitárias
require_once '../config.php';

// ========================================
// VARIÁVEIS INICIAIS
// ========================================
// Obtém o método HTTP da requisição
$method = $_SERVER['REQUEST_METHOD'];
// Estabelece conexão com o banco de dados
$pdo = getConnection();

// ========================================
// VALIDAÇÃO DO MÉTODO HTTP
// ========================================
// Esta API só aceita requisições GET
if ($method !== 'GET') {
    jsonResponse(['error' => 'Método não permitido'], 405);
}

// ========================================
// EXECUÇÃO PRINCIPAL
// ========================================
// Chama a função principal de ranking
getRanking($pdo);

// ========================================
// FUNÇÃO PRINCIPAL DE RANKING
// ========================================
/**
 * Gera o ranking dos usuários com base em diferentes critérios
 * Suporta filtros, ordenação e paginação
 * 
 * @param PDO $pdo Conexão com o banco de dados
 */
function getRanking($pdo) {
    // ========================================
    // PARÂMETROS DE CONSULTA
    // ========================================
    // Obtém parâmetros da URL com valores padrão
    $search = $_GET['search'] ?? '';           // Termo de busca por nome de usuário
    $orderBy = $_GET['order_by'] ?? 'points';  // Campo para ordenação
    $orderDir = $_GET['order_dir'] ?? 'DESC';  // Direção da ordenação
    $limit = $_GET['limit'] ?? 50;             // Limite de resultados
    
    // ========================================
    // VALIDAÇÃO DE PARÂMETROS
    // ========================================
    // Lista de campos válidos para ordenação
    $validOrderBy = ['points', 'username', 'badges_count'];
    // Lista de direções válidas para ordenação
    $validOrderDir = ['ASC', 'DESC'];
    
    // Valida e corrige o campo de ordenação se necessário
    if (!in_array($orderBy, $validOrderBy)) {
        $orderBy = 'points';
    }
    
    // Valida e corrige a direção de ordenação se necessário
    if (!in_array($orderDir, $validOrderDir)) {
        $orderDir = 'DESC';
    }
    
    // Limita o número de resultados entre 1 e 100
    $limit = min(max(1, intval($limit)), 100);
    
    // ========================================
    // CONSTRUÇÃO DA QUERY SQL
    // ========================================
    // Query base que junta usuários com badges e completações
    $sql = "
        SELECT 
            u.id,
            u.username,
            u.email,
            u.total_points as points,
            COUNT(DISTINCT ub.badge_id) as badges_count,
            COUNT(DISTINCT c.id) as completions_count
        FROM users u
        LEFT JOIN user_badges ub ON u.id = ub.user_id
        LEFT JOIN completions c ON u.id = c.user_id
    ";
    
    // Array para parâmetros da query
    $params = [];
    
    // ========================================
    // APLICAÇÃO DE FILTROS
    // ========================================
    // Adiciona filtro de busca por nome de usuário se fornecido
    if (!empty($search)) {
        $sql .= " WHERE u.username LIKE ?";
        $params[] = "%$search%";
    }
    
    // Agrupa resultados por usuário
    $sql .= " GROUP BY u.id";
    
    // ========================================
    // APLICAÇÃO DE ORDENAÇÃO
    // ========================================
    // Define a ordenação baseada no parâmetro orderBy
    switch($orderBy) {
        case 'points':
            // Ordena por pontos totais
            $sql .= " ORDER BY u.total_points $orderDir";
            break;
        case 'username':
            // Ordena por nome de usuário
            $sql .= " ORDER BY u.username $orderDir";
            break;
        case 'badges_count':
            // Ordena por número de badges conquistados
            $sql .= " ORDER BY badges_count $orderDir";
            break;
    }
    
    // Adiciona limite de resultados
    $sql .= " LIMIT $limit";
    
    // ========================================
    // EXECUÇÃO DA QUERY E PROCESSAMENTO
    // ========================================
    try {
        // Prepara e executa a query
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $ranking = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // ========================================
        // ENRIQUECIMENTO DOS DADOS
        // ========================================
        // Adiciona informações extras para cada usuário no ranking
        foreach ($ranking as $index => &$user) {
            // Adiciona a posição no ranking (baseada no índice)
            $user['position'] = $index + 1;
            
            // Busca os badges específicos conquistados pelo usuário
            $stmt = $pdo->prepare("
                SELECT b.name, b.description 
                FROM user_badges ub 
                JOIN badges b ON ub.badge_id = b.id 
                WHERE ub.user_id = ?
                ORDER BY ub.awarded_date DESC
            ");
            $stmt->execute([$user['id']]);
            $user['badges'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // ========================================
        // RESPOSTA FINAL
        // ========================================
        // Retorna o ranking com os filtros aplicados
        jsonResponse([
            'ranking' => $ranking,
            'filters' => [
                'search' => $search,
                'order_by' => $orderBy,
                'order_dir' => $orderDir,
                'limit' => $limit
            ]
        ]);
        
    } catch(PDOException $e) {
        // Retorna erro interno do servidor em caso de falha
        jsonResponse(['error' => 'Erro interno do servidor'], 500);
    }
}
?>

