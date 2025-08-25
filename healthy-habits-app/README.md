# Healthy Habits App

Uma aplicação web para gerenciar hábitos saudáveis com sistema de pontuação, badges e ranking.

## Funcionalidades

- ✅ **Sistema de Autenticação**: Login e registro de usuários
- ✅ **Gerenciamento de Hábitos**: Criar, editar e excluir hábitos
- ✅ **Sistema de Pontuação**: Pontos fixos por conclusão de hábito
- ✅ **Recompensas Personalizadas**: Campo opcional para definir recompensas
- ✅ **Sistema de Badges**: Conquistas baseadas em pontos
- ✅ **Ranking**: Comparação entre usuários
- ✅ **Dashboard**: Visão geral das atividades
- ✅ **Perfil do Usuário**: Gerenciamento de informações pessoais

## Correções Implementadas

1. **Sistema de Login**: Implementado sistema completo de autenticação
2. **Criação de Hábitos**: Corrigido e melhorado com campo de recompensas
3. **Sistema de Pontuação**: Pontuação fixa por hábito
4. **Interface**: Adicionado botão de logout e indicação clara do usuário logado

## Requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Servidor web (Apache/Nginx)

## Instalação

### 1. Configurar o Banco de Dados

```sql
CREATE DATABASE healthy_habits;
```

### 2. Configurar as Credenciais

Edite o arquivo `backend/config.php` com suas credenciais do banco de dados:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'healthy_habits');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');
```

### 3. Configurar o Banco de Dados

Execute o script de configuração:

```bash
cd backend
php setup_database.php
```

### 4. Iniciar o Servidor

```bash
cd backend
php -S localhost:8000
```

### 5. Acessar a Aplicação

Abra o arquivo `frontend/index.html` no navegador ou configure um servidor web.

## Usuário de Demonstração

Após executar o script de configuração, você pode usar:

- **Username**: demo_user
- **Password**: 123456

## Estrutura do Projeto

```
healthy-habits-app/
├── backend/
│   ├── api/
│   │   ├── auth.php          # Autenticação
│   │   ├── habits.php        # Gerenciamento de hábitos
│   │   ├── completions.php   # Conclusões de hábitos
│   │   ├── badges.php        # Sistema de badges
│   │   ├── ranking.php       # Ranking de usuários
│   │   └── users.php         # Gerenciamento de usuários
│   ├── config.php            # Configurações
│   ├── database.sql          # Schema do banco
│   └── setup_database.php    # Script de configuração
├── frontend/
│   ├── index.html            # Interface principal
│   └── js/
│       └── app.js            # Lógica da aplicação
└── README.md
```

## API Endpoints

### Autenticação
- `POST /api/auth.php?action=login` - Login
- `POST /api/auth.php?action=register` - Registro
- `POST /api/auth.php?action=logout` - Logout
- `GET /api/auth.php?action=check` - Verificar autenticação

### Hábitos
- `GET /api/habits.php?user_id={id}` - Listar hábitos do usuário
- `POST /api/habits.php` - Criar novo hábito
- `PUT /api/habits.php` - Atualizar hábito
- `DELETE /api/habits.php?id={id}` - Excluir hábito

### Conclusões
- `POST /api/completions.php` - Marcar hábito como concluído

### Badges
- `GET /api/badges.php?user_id={id}` - Listar badges do usuário

### Ranking
- `GET /api/ranking.php` - Listar ranking de usuários

### Usuários
- `GET /api/users.php?id={id}` - Obter dados do usuário
- `PUT /api/users.php` - Atualizar perfil

## Tecnologias Utilizadas

- **Frontend**: HTML5, CSS3 (Tailwind CSS), JavaScript (Vanilla)
- **Backend**: PHP 7.4+
- **Banco de Dados**: MySQL
- **Ícones**: Lucide Icons

## Contribuição

1. Faça um fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## Licença

Este projeto está sob a licença MIT. Veja o arquivo `LICENSE` para mais detalhes.
