<?php
require_once 'config.php';

echo "=== Reset Completo do Banco de Dados ===\n\n";

try {
    $pdo = getConnection();
    
    // Dropar todas as tabelas existentes
    echo "🗑️ Removendo tabelas existentes...\n";
    $tables = ['user_badges', 'completions', 'habits', 'badges', 'users'];
    foreach ($tables as $table) {
        try {
            $pdo->exec("DROP TABLE IF EXISTS $table");
            echo "✅ Tabela '$table' removida\n";
        } catch(PDOException $e) {
            echo "⚠️ Erro ao remover tabela '$table': " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n📋 Criando novas tabelas...\n";
    
    // Criar tabela users
    $pdo->exec("
        CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) NOT NULL UNIQUE,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            total_points INT DEFAULT 0
        )
    ");
    echo "✅ Tabela 'users' criada\n";
    
    // Criar tabela habits
    $pdo->exec("
        CREATE TABLE habits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            points_per_completion INT DEFAULT 10,
            reward_description TEXT,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");
    echo "✅ Tabela 'habits' criada\n";
    
    // Criar tabela completions
    $pdo->exec("
        CREATE TABLE completions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            habit_id INT NOT NULL,
            user_id INT NOT NULL,
            completion_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            points_earned INT,
            FOREIGN KEY (habit_id) REFERENCES habits(id),
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");
    echo "✅ Tabela 'completions' criada\n";
    
    // Criar tabela badges
    $pdo->exec("
        CREATE TABLE badges (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL UNIQUE,
            description TEXT,
            points_threshold INT
        )
    ");
    echo "✅ Tabela 'badges' criada\n";
    
    // Criar tabela user_badges
    $pdo->exec("
        CREATE TABLE user_badges (
            user_id INT NOT NULL,
            badge_id INT NOT NULL,
            awarded_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (user_id, badge_id),
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (badge_id) REFERENCES badges(id)
        )
    ");
    echo "✅ Tabela 'user_badges' criada\n";
    
    echo "\n🏆 Inserindo badges padrão...\n";
    
    // Inserir badges padrão
    $badges = [
        ['name' => 'Primeiro Passo', 'description' => 'Complete seu primeiro hábito', 'points_threshold' => 10],
        ['name' => 'Dedicado', 'description' => 'Complete 10 hábitos', 'points_threshold' => 100],
        ['name' => 'Consistente', 'description' => 'Complete hábitos por 7 dias seguidos', 'points_threshold' => 200],
        ['name' => 'Mestre dos Hábitos', 'description' => 'Complete 50 hábitos', 'points_threshold' => 500],
        ['name' => 'Lenda', 'description' => 'Complete 100 hábitos', 'points_threshold' => 1000]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO badges (name, description, points_threshold) VALUES (?, ?, ?)");
    foreach ($badges as $badge) {
        $stmt->execute([$badge['name'], $badge['description'], $badge['points_threshold']]);
        echo "✅ Badge '{$badge['name']}' inserido\n";
    }
    
    echo "\n👤 Criando usuário de demonstração...\n";
    
    // Criar usuário demo
    $hashedPassword = password_hash('123456', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute(['demo_user', 'demo@example.com', $hashedPassword]);
    
    $userId = $pdo->lastInsertId();
    echo "✅ Usuário demo criado - ID: $userId\n";
    
    // Verificar se o usuário foi criado corretamente
    $stmt = $pdo->prepare("SELECT id, username, email, total_points FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "\n📋 Usuário demo criado com sucesso:\n";
        echo "- ID: {$user['id']}\n";
        echo "- Username: {$user['username']}\n";
        echo "- Email: {$user['email']}\n";
        echo "- Pontos: {$user['total_points']}\n";
    }
    
    // Testar login do usuário demo
    echo "\n🔐 Testando login do usuário demo...\n";
    $stmt = $pdo->prepare("SELECT password FROM users WHERE username = ?");
    $stmt->execute(['demo_user']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify('123456', $user['password'])) {
        echo "✅ Login do usuário demo funcionando corretamente!\n";
    } else {
        echo "❌ Erro no login do usuário demo!\n";
    }
    
    echo "\n🎉 Reset do banco de dados concluído com sucesso!\n";
    echo "\n📝 Credenciais do usuário demo:\n";
    echo "- Username: demo_user\n";
    echo "- Password: 123456\n";
    echo "- Email: demo@example.com\n";
    
} catch(PDOException $e) {
    echo "❌ Erro durante o reset: " . $e->getMessage() . "\n";
    echo "\nPossíveis soluções:\n";
    echo "1. Verifique se o MySQL está rodando\n";
    echo "2. Verifique se o banco 'healthy_habits' existe\n";
    echo "3. Verifique as credenciais no config.php\n";
    echo "4. Execute: CREATE DATABASE healthy_habits;\n";
}

echo "\n=== Fim do Reset ===\n";
?>
