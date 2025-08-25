<?php
require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getConnection();

if ($method !== 'GET') {
    jsonResponse(['error' => 'Método não permitido'], 405);
}

getRanking($pdo);

function getRanking($pdo) {
    $search = $_GET['search'] ?? '';
    $orderBy = $_GET['order_by'] ?? 'points';
    $orderDir = $_GET['order_dir'] ?? 'DESC';
    $limit = $_GET['limit'] ?? 50;
    
    // Validar parâmetros
    $validOrderBy = ['points', 'username', 'badges_count'];
    $validOrderDir = ['ASC', 'DESC'];
    
    if (!in_array($orderBy, $validOrderBy)) {
        $orderBy = 'points';
    }
    
    if (!in_array($orderDir, $validOrderDir)) {
        $orderDir = 'DESC';
    }
    
    $limit = min(max(1, intval($limit)), 100); // Entre 1 e 100
    
    // Construir query
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
    
    $params = [];
    
    if (!empty($search)) {
        $sql .= " WHERE u.username LIKE ?";
        $params[] = "%$search%";
    }
    
    $sql .= " GROUP BY u.id";
    
    // Ordenação
    switch($orderBy) {
        case 'points':
            $sql .= " ORDER BY u.total_points $orderDir";
            break;
        case 'username':
            $sql .= " ORDER BY u.username $orderDir";
            break;
        case 'badges_count':
            $sql .= " ORDER BY badges_count $orderDir";
            break;
    }
    
    $sql .= " LIMIT $limit";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $ranking = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Adicionar posição no ranking
        foreach ($ranking as $index => &$user) {
            $user['position'] = $index + 1;
            
            // Buscar badges do usuário
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
        jsonResponse(['error' => 'Erro interno do servidor'], 500);
    }
}
?>

