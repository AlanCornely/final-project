-- ========================================
-- ESQUEMA DO BANCO DE DADOS - HEALTHY HABITS
-- ========================================
-- Este arquivo contém a estrutura completa do banco de dados
-- para a aplicação Healthy Habits, incluindo todas as tabelas
-- necessárias para gerenciar usuários, hábitos, completações e badges

-- ========================================
-- TABELA: users (Usuários)
-- ========================================
-- Armazena informações dos usuários da aplicação
-- Cada usuário pode ter múltiplos hábitos e ganhar pontos
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,        -- Identificador único do usuário (auto-incremento)
    username VARCHAR(255) NOT NULL UNIQUE,    -- Nome de usuário único (obrigatório)
    email VARCHAR(255) NOT NULL UNIQUE,       -- Email único do usuário (obrigatório)
    password VARCHAR(255) NOT NULL,           -- Senha criptografada do usuário (obrigatório)
    total_points INT DEFAULT 0                -- Total de pontos acumulados pelo usuário (padrão: 0)
);

-- ========================================
-- TABELA: habits (Hábitos)
-- ========================================
-- Armazena os hábitos criados pelos usuários
-- Cada hábito pertence a um usuário específico
CREATE TABLE habits (
    id INT AUTO_INCREMENT PRIMARY KEY,        -- Identificador único do hábito (auto-incremento)
    user_id INT NOT NULL,                     -- ID do usuário que criou o hábito (obrigatório)
    name VARCHAR(255) NOT NULL,               -- Nome do hábito (obrigatório)
    description TEXT,                         -- Descrição detalhada do hábito (opcional)
    points_per_completion INT DEFAULT 10,     -- Pontos ganhos por cada completação (padrão: 10)
    reward_description TEXT,                  -- Descrição da recompensa do hábito (opcional)
    FOREIGN KEY (user_id) REFERENCES users(id) -- Chave estrangeira para a tabela users
);

-- ========================================
-- TABELA: completions (Completações)
-- ========================================
-- Registra cada vez que um usuário completa um hábito
-- Permite rastrear o histórico de completações e pontos ganhos
CREATE TABLE completions (
    id INT AUTO_INCREMENT PRIMARY KEY,        -- Identificador único da completação (auto-incremento)
    habit_id INT NOT NULL,                    -- ID do hábito que foi completado (obrigatório)
    user_id INT NOT NULL,                     -- ID do usuário que completou o hábito (obrigatório)
    completion_date DATETIME DEFAULT CURRENT_TIMESTAMP, -- Data/hora da completação (padrão: agora)
    points_earned INT,                        -- Pontos ganhos nesta completação específica
    FOREIGN KEY (habit_id) REFERENCES habits(id), -- Chave estrangeira para a tabela habits
    FOREIGN KEY (user_id) REFERENCES users(id)    -- Chave estrangeira para a tabela users
);

-- ========================================
-- TABELA: badges (Conquistas/Badges)
-- ========================================
-- Define as conquistas disponíveis na aplicação
-- Cada badge tem um limite de pontos para ser desbloqueado
CREATE TABLE badges (
    id INT AUTO_INCREMENT PRIMARY KEY,        -- Identificador único do badge (auto-incremento)
    name VARCHAR(255) NOT NULL UNIQUE,        -- Nome único do badge (obrigatório)
    description TEXT,                         -- Descrição do que é necessário para ganhar o badge
    points_threshold INT                      -- Limite de pontos necessário para desbloquear o badge
);

-- ========================================
-- TABELA: user_badges (Badges dos Usuários)
-- ========================================
-- Tabela de relacionamento entre usuários e badges
-- Registra quais badges cada usuário conquistou e quando
CREATE TABLE user_badges (
    user_id INT NOT NULL,                     -- ID do usuário que conquistou o badge (obrigatório)
    badge_id INT NOT NULL,                    -- ID do badge conquistado (obrigatório)
    awarded_date DATETIME DEFAULT CURRENT_TIMESTAMP, -- Data/hora em que o badge foi conquistado (padrão: agora)
    PRIMARY KEY (user_id, badge_id),          -- Chave primária composta (usuário pode ter cada badge apenas uma vez)
    FOREIGN KEY (user_id) REFERENCES users(id), -- Chave estrangeira para a tabela users
    FOREIGN KEY (badge_id) REFERENCES badges(id) -- Chave estrangeira para a tabela badges
);

-- ========================================
-- RELACIONAMENTOS ENTRE TABELAS:
-- ========================================
-- 1. users (1) -> (N) habits: Um usuário pode ter múltiplos hábitos
-- 2. users (1) -> (N) completions: Um usuário pode ter múltiplas completações
-- 3. habits (1) -> (N) completions: Um hábito pode ter múltiplas completações
-- 4. users (N) -> (N) badges: Usuários podem conquistar múltiplos badges (via user_badges)
-- 5. badges (N) -> (N) users: Badges podem ser conquistados por múltiplos usuários (via user_badges)

