<?php
/**
 * SCRIPT DE SETUP STANDALONE - HEALTHY HABITS
 * 
 * Este arquivo é uma versão independente do setup que não depende do config.php
 * Configura o ambiente completo da aplicação de forma autônoma:
 * - Define suas próprias configurações de banco de dados
 * - Cria o banco de dados se não existir
 * - Cria todas as tabelas necessárias
 * - Insere dados de exemplo para demonstração
 * 
 * USO: Execute este arquivo quando quiser uma configuração independente
 * Exemplo: php setup_standalone.php
 * 
 * VANTAGEM: Não depende de outros arquivos, pode ser executado isoladamente
 */

// ========================================
// CONFIGURAÇÕES DO BANCO DE DADOS
// ========================================
// Define as constantes para conexão com o banco de dados MySQL
define('DB_HOST', 'localhost');        // Endereço do servidor MySQL
define('DB_NAME', 'healthy_habits');   // Nome do banco de dados
define('DB_USER', 'root');             // Usuário do banco de dados
define('DB_PASS', 'password123');      // Senha do banco de dados

// ========================================
// FUNÇÃO DE CONEXÃO COM O BANCO DE DADOS
// ========================================
/**
 * Estabelece conexão com o banco de dados MySQL usando PDO
 * 
 * @return PDO Objeto de conexão com o banco de dados
 * @throws PDOException Se houver erro na conexão
 */
function getConnection() {
    try {
        // Cria uma nova conexão PDO com o MySQL
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        // Configura o PDO para lançar exceções em caso de erro
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        // Propaga a exceção para ser tratada no código principal
        throw $e;
    }
}

// ========================================
// SETUP PRINCIPAL
// ========================================
try {
    // ========================================
    // CONEXÃO INICIAL COM MYSQL
    // ========================================
    // Conecta ao MySQL sem especificar um banco de dados específico
    // Isso permite criar o banco de dados se ele não existir
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    // Configura o PDO para lançar exceções em caso de erro
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // ========================================
    // CRIAÇÃO DO BANCO DE DADOS
    // ========================================
    // Cria o banco de dados se ele não existir
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    echo "Database criado com sucesso!\n";
    
    // ========================================
    // CONEXÃO COM O BANCO ESPECÍFICO
    // ========================================
    // Agora conecta ao banco de dados específico da aplicação
    $pdo = getConnection();
    
    // ========================================
    // CRIAÇÃO DAS TABELAS
    // ========================================
    // Lê o arquivo SQL com a estrutura das tabelas
    $sql = file_get_contents('database.sql');
    // Executa o script SQL para criar todas as tabelas
    $pdo->exec($sql);
    echo "Tabelas criadas com sucesso!\n";
    
    // ========================================
    // INSERÇÃO DE DADOS DE EXEMPLO
    // ========================================
    // Insere dados de demonstração para testar a aplicação
    insertSampleData($pdo);
    echo "Dados de exemplo inseridos com sucesso!\n";
    
    // ========================================
    // CONFIRMAÇÃO DE CONCLUSÃO
    // ========================================
    echo "Setup concluído!\n";
    
} catch(PDOException $e) {
    // Em caso de erro, exibe a mensagem de erro
    echo "Erro: " . $e->getMessage() . "\n";
}

// ========================================
// FUNÇÃO: INSERIR DADOS DE EXEMPLO
// ========================================
/**
 * Insere dados de demonstração no banco de dados
 * Inclui badges padrão, usuários de exemplo e hábitos de teste
 * 
 * @param PDO $pdo Conexão com o banco de dados
 */
function insertSampleData($pdo) {
    // ========================================
    // INSERÇÃO DE BADGES PADRÃO
    // ========================================
    // Array com badges padrão do sistema
    // Cada badge tem: [nome, descrição, pontos necessários]
    $badges = [
        ['Iniciante', 'Primeiros passos na jornada saudável', 0],
        ['Dedicado', 'Alcançou 50 pontos', 50],
        ['Persistente', 'Alcançou 100 pontos', 100],
        ['Determinado', 'Alcançou 250 pontos', 250],
        ['Campeão', 'Alcançou 500 pontos', 500],
        ['Lenda', 'Alcançou 1000 pontos', 1000]
    ];
    
    // Insere cada badge no banco de dados
    foreach ($badges as $badge) {
        $stmt = $pdo->prepare("INSERT INTO badges (name, description, points_threshold) VALUES (?, ?, ?)");
        $stmt->execute($badge);
    }
    
    // ========================================
    // INSERÇÃO DE USUÁRIOS DE EXEMPLO
    // ========================================
    // Array com usuários de demonstração
    // Cada usuário tem: [username, email, senha_criptografada]
    $users = [
        ['admin', 'admin@healthyhabits.com', password_hash('admin123', PASSWORD_DEFAULT)],
        ['joao_silva', 'joao@email.com', password_hash('123456', PASSWORD_DEFAULT)],
        ['maria_santos', 'maria@email.com', password_hash('123456', PASSWORD_DEFAULT)]
    ];
    
    // Insere cada usuário no banco de dados
    foreach ($users as $user) {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute($user);
    }
    
    // ========================================
    // INSERÇÃO DE HÁBITOS DE EXEMPLO
    // ========================================
    // Array com hábitos de demonstração
    // Cada hábito tem: [user_id, nome, descrição, pontos_por_completação]
    $habits = [
        [2, 'Beber 2L de água', 'Manter-se hidratado bebendo pelo menos 2 litros de água por dia', 10],
        [2, 'Exercitar-se 30min', 'Fazer pelo menos 30 minutos de exercício físico', 15],
        [2, 'Meditar 10min', 'Praticar meditação por 10 minutos', 12],
        [3, 'Caminhar 10.000 passos', 'Atingir a meta de 10.000 passos por dia', 20],
        [3, 'Dormir 8 horas', 'Ter uma noite de sono reparador de 8 horas', 15]
    ];
    
    // Insere cada hábito no banco de dados
    foreach ($habits as $habit) {
        $stmt = $pdo->prepare("INSERT INTO habits (user_id, name, description, points_per_completion) VALUES (?, ?, ?, ?)");
        $stmt->execute($habit);
    }
}
?>

