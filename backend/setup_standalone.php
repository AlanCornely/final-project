<?php
// Configuração do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'healthy_habits');
define('DB_USER', 'root');
define('DB_PASS', 'password123');

// Função para conectar ao banco de dados
function getConnection() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        throw $e;
    }
}

try {
    // Conectar ao MySQL sem especificar database
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Criar database se não existir
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    echo "Database criado com sucesso!\n";
    
    // Conectar ao database específico
    $pdo = getConnection();
    
    // Ler e executar o arquivo SQL
    $sql = file_get_contents('database.sql');
    $pdo->exec($sql);
    echo "Tabelas criadas com sucesso!\n";
    
    // Inserir dados de exemplo
    insertSampleData($pdo);
    echo "Dados de exemplo inseridos com sucesso!\n";
    
    echo "Setup concluído!\n";
    
} catch(PDOException $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}

function insertSampleData($pdo) {
    // Inserir badges padrão
    $badges = [
        ['Iniciante', 'Primeiros passos na jornada saudável', 0],
        ['Dedicado', 'Alcançou 50 pontos', 50],
        ['Persistente', 'Alcançou 100 pontos', 100],
        ['Determinado', 'Alcançou 250 pontos', 250],
        ['Campeão', 'Alcançou 500 pontos', 500],
        ['Lenda', 'Alcançou 1000 pontos', 1000]
    ];
    
    foreach ($badges as $badge) {
        $stmt = $pdo->prepare("INSERT INTO badges (name, description, points_threshold) VALUES (?, ?, ?)");
        $stmt->execute($badge);
    }
    
    // Inserir usuários de exemplo
    $users = [
        ['admin', 'admin@healthyhabits.com', password_hash('admin123', PASSWORD_DEFAULT)],
        ['joao_silva', 'joao@email.com', password_hash('123456', PASSWORD_DEFAULT)],
        ['maria_santos', 'maria@email.com', password_hash('123456', PASSWORD_DEFAULT)]
    ];
    
    foreach ($users as $user) {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute($user);
    }
    
    // Inserir hábitos de exemplo
    $habits = [
        [2, 'Beber 2L de água', 'Manter-se hidratado bebendo pelo menos 2 litros de água por dia', 10],
        [2, 'Exercitar-se 30min', 'Fazer pelo menos 30 minutos de exercício físico', 15],
        [2, 'Meditar 10min', 'Praticar meditação por 10 minutos', 12],
        [3, 'Caminhar 10.000 passos', 'Atingir a meta de 10.000 passos por dia', 20],
        [3, 'Dormir 8 horas', 'Ter uma noite de sono reparador de 8 horas', 15]
    ];
    
    foreach ($habits as $habit) {
        $stmt = $pdo->prepare("INSERT INTO habits (user_id, name, description, points_per_completion) VALUES (?, ?, ?, ?)");
        $stmt->execute($habit);
    }
}
?>

