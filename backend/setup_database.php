<?php
/**
 * SCRIPT DE SETUP DO BANCO DE DADOS - HEALTHY HABITS
 * 
 * Este arquivo configura apenas o banco de dados da aplicação:
 * - Cria todas as tabelas necessárias
 * - Insere badges padrão do sistema
 * - Cria um usuário de demonstração
 * 
 * USO: Execute este arquivo para configurar apenas o banco de dados
 * Exemplo: php setup_database.php
 * 
 * DIFERENÇA DO setup.php: Este não cria o banco de dados, apenas as tabelas
 */

// Inclui o arquivo de configuração com constantes do banco de dados
require_once 'config.php';

// ========================================
// SETUP DO BANCO DE DADOS
// ========================================
try {
    // ========================================
    // CONEXÃO COM O BANCO DE DADOS
    // ========================================
    // Conecta ao banco de dados usando a função do config.php
    $pdo = getConnection();
    
    // ========================================
    // CRIAÇÃO DAS TABELAS
    // ========================================
    // Lê o arquivo SQL com a estrutura das tabelas
    $sql = file_get_contents('database.sql');
    // Executa o script SQL para criar todas as tabelas
    $pdo->exec($sql);
    
    // ========================================
    // INSERÇÃO DE BADGES PADRÃO
    // ========================================
    // Array com badges padrão do sistema
    // Cada badge tem: nome, descrição e pontos necessários
    $badges = [
        ['name' => 'Primeiro Passo', 'description' => 'Complete seu primeiro hábito', 'points_threshold' => 10],
        ['name' => 'Dedicado', 'description' => 'Complete 10 hábitos', 'points_threshold' => 100],
        ['name' => 'Consistente', 'description' => 'Complete hábitos por 7 dias seguidos', 'points_threshold' => 200],
        ['name' => 'Mestre dos Hábitos', 'description' => 'Complete 50 hábitos', 'points_threshold' => 500],
        ['name' => 'Lenda', 'description' => 'Complete 100 hábitos', 'points_threshold' => 1000]
    ];
    
    // Prepara a query para inserção de badges
    $stmt = $pdo->prepare("INSERT INTO badges (name, description, points_threshold) VALUES (?, ?, ?)");
    
    // Insere cada badge no banco de dados
    foreach ($badges as $badge) {
        $stmt->execute([$badge['name'], $badge['description'], $badge['points_threshold']]);
    }
    
    // ========================================
    // CRIAÇÃO DE USUÁRIO DE DEMONSTRAÇÃO
    // ========================================
    // Criptografa a senha do usuário de demonstração
    $hashedPassword = password_hash('123456', PASSWORD_DEFAULT);
    
    // Prepara e executa a inserção do usuário de demonstração
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute(['demo_user', 'demo@example.com', $hashedPassword]);
    
    // ========================================
    // CONFIRMAÇÃO DE CONCLUSÃO
    // ========================================
    echo "Database setup completed successfully!\n";
    echo "Sample user created:\n";
    echo "Username: demo_user\n";
    echo "Password: 123456\n";
    
} catch(PDOException $e) {
    // Em caso de erro, exibe a mensagem de erro
    echo "Error setting up database: " . $e->getMessage() . "\n";
}
?>

