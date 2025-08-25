<?php
require_once 'config.php';

echo "=== Teste de Conexão com Banco de Dados ===\n\n";

try {
    $pdo = getConnection();
    echo "✅ Conexão com banco de dados estabelecida com sucesso!\n";
    
    // Verificar se a tabela users existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabela 'users' encontrada!\n";
        
        // Verificar se há usuários cadastrados
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "📊 Total de usuários cadastrados: " . $result['total'] . "\n";
        
        if ($result['total'] > 0) {
            // Listar usuários
            $stmt = $pdo->query("SELECT id, username, email, total_points FROM users");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "\n📋 Usuários cadastrados:\n";
            foreach ($users as $user) {
                echo "- ID: {$user['id']}, Username: {$user['username']}, Email: {$user['email']}, Pontos: {$user['total_points']}\n";
            }
        } else {
            echo "❌ Nenhum usuário encontrado. Execute o script setup_database.php primeiro.\n";
        }
        
    } else {
        echo "❌ Tabela 'users' não encontrada. Execute o script setup_database.php primeiro.\n";
    }
    
    // Verificar outras tabelas
    $tables = ['habits', 'completions', 'badges', 'user_badges'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Tabela '$table' encontrada!\n";
        } else {
            echo "❌ Tabela '$table' não encontrada!\n";
        }
    }
    
} catch(PDOException $e) {
    echo "❌ Erro de conexão: " . $e->getMessage() . "\n";
    echo "\nPossíveis soluções:\n";
    echo "1. Verifique se o MySQL está rodando\n";
    echo "2. Verifique se o banco 'healthy_habits' existe\n";
    echo "3. Verifique as credenciais no config.php\n";
    echo "4. Execute: CREATE DATABASE healthy_habits;\n";
}

echo "\n=== Fim do Teste ===\n";
?>
