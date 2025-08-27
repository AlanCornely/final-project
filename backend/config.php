<?php
/**
 * ARQUIVO DE CONFIGURAÇÃO PRINCIPAL
 * 
 * Este arquivo contém todas as configurações essenciais para o funcionamento
 * do backend da aplicação Healthy Habits, incluindo:
 * - Configurações do banco de dados
 * - Configurações de CORS para desenvolvimento
 * - Funções utilitárias para conexão e resposta JSON
 * - Configurações de debug e logs
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
// CONFIGURAÇÕES DE DEBUG E LOGS
// ========================================
// Habilita todos os tipos de erro para facilitar o debug durante desenvolvimento
error_reporting(E_ALL);
// Exibe erros na tela (deve ser desabilitado em produção)
ini_set('display_errors', 1);

// ========================================
// CONFIGURAÇÕES DE CORS (Cross-Origin Resource Sharing)
// ========================================
// Permite requisições de qualquer origem (configuração para desenvolvimento)
header('Access-Control-Allow-Origin: *');
// Define os métodos HTTP permitidos
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
// Define os headers permitidos nas requisições
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
// Permite o envio de cookies e credenciais
header('Access-Control-Allow-Credentials: true');
// Define o tipo de conteúdo da resposta como JSON com suporte a UTF-8
header('Content-Type: application/json; charset=utf-8');

// ========================================
// TRATAMENTO DE REQUISIÇÕES OPTIONS (PREFLIGHT)
// ========================================
// Responde automaticamente a requisições OPTIONS (usadas pelo navegador para verificar CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ========================================
// LOG DE REQUISIÇÕES PARA DEBUG
// ========================================
// Registra no log todas as requisições recebidas para facilitar o debug
error_log("Request received: " . $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REQUEST_URI']);

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
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        
        // Configura o PDO para lançar exceções em caso de erro
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Define o modo padrão de fetch como array associativo
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        return $pdo;
    } catch(PDOException $e) {
        // Em caso de erro na conexão, registra o erro no log
        error_log("Database connection error: " . $e->getMessage());
        
        // Retorna erro 500 (Internal Server Error)
        http_response_code(500);
        
        // Retorna mensagem de erro em JSON
        echo json_encode(['error' => 'Erro de conexão com o banco de dados: ' . $e->getMessage()]);
        exit();
    }
}

// ========================================
// FUNÇÃO PARA RESPOSTA JSON PADRONIZADA
// ========================================
/**
 * Envia uma resposta JSON padronizada e encerra a execução
 * 
 * @param mixed $data Dados a serem enviados na resposta
 * @param int $status Código de status HTTP (padrão: 200)
 */
function jsonResponse($data, $status = 200) {
    // Define o código de status HTTP
    http_response_code($status);
    
    // Converte os dados para JSON com suporte a caracteres especiais
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    
    // Encerra a execução do script
    exit();
}
?>

