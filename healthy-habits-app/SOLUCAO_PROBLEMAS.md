# Solução de Problemas - Healthy Habits App

Este guia ajudará você a resolver os problemas de login e cadastro.

## Problema: Não consigo fazer login ou cadastrar usuários

### Passo 1: Verificar Configuração do Banco de Dados

Primeiro, execute o script de teste de conexão:

```bash
cd backend
php test_connection.php
```

**Possíveis resultados:**

#### ✅ Se funcionar:
- Banco de dados está configurado corretamente
- Pule para o Passo 3

#### ❌ Se der erro de conexão:
1. **Verifique se o MySQL está rodando:**
   ```bash
   # Windows
   net start mysql
   
   # Linux/Mac
   sudo service mysql start
   # ou
   sudo systemctl start mysql
   ```

2. **Verifique se o banco existe:**
   ```sql
   mysql -u root -p
   SHOW DATABASES;
   ```

3. **Se o banco não existir, crie-o:**
   ```sql
   CREATE DATABASE healthy_habits;
   exit;
   ```

4. **Verifique as credenciais no `backend/config.php`:**
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'healthy_habits');
   define('DB_USER', 'root');
   define('DB_PASS', 'password123'); // Sua senha real do MySQL
   ```

### Passo 2: Reset Completo do Banco de Dados

Se o banco existe mas ainda há problemas, execute o reset completo:

```bash
cd backend
php reset_database.php
```

Este script irá:
- Remover todas as tabelas existentes
- Criar novas tabelas com a estrutura correta
- Inserir badges padrão
- Criar usuário demo funcional

### Passo 3: Verificar Servidor PHP

1. **Inicie o servidor PHP:**
   ```bash
   cd backend
   php -S localhost:8000
   ```

2. **Teste se o servidor está funcionando:**
   - Abra: `http://localhost:8000/test_connection.php`
   - Deve mostrar informações sobre o banco de dados

### Passo 4: Testar API de Autenticação

1. **Teste o endpoint de login:**
   ```bash
   curl -X POST http://localhost:8000/api/auth.php?action=login \
     -H "Content-Type: application/json" \
     -d '{"username":"demo_user","password":"123456"}'
   ```

2. **Teste o endpoint de registro:**
   ```bash
   curl -X POST http://localhost:8000/api/auth.php?action=register \
     -H "Content-Type: application/json" \
     -d '{"username":"teste","email":"teste@teste.com","password":"123456"}'
   ```

### Passo 5: Verificar Frontend

1. **Abra o arquivo `frontend/index.html` no navegador**
2. **Abra o Console do navegador (F12)**
3. **Tente fazer login e observe os erros no console**

### Passo 6: Problemas Comuns e Soluções

#### Problema: "CORS error"
**Solução:**
- Verifique se o servidor está rodando na porta 8000
- Verifique se o frontend está acessando a URL correta

#### Problema: "Erro de conexão com banco de dados"
**Solução:**
- Execute `php test_connection.php` para diagnosticar
- Verifique credenciais no `config.php`
- Verifique se o MySQL está rodando

#### Problema: "Username ou password incorretos"
**Solução:**
- Execute `php reset_database.php` para recriar o usuário demo
- Use as credenciais: `demo_user` / `123456`

#### Problema: "Sessão não mantida"
**Solução:**
- Verifique se `credentials: 'include'` está configurado no JavaScript
- Verifique se o CORS está configurado corretamente

### Passo 7: Logs de Debug

Para ver logs detalhados:

1. **Verifique os logs do PHP:**
   ```bash
   # Windows
   tail -f C:\xampp\php\logs\php_error_log
   
   # Linux/Mac
   tail -f /var/log/apache2/error.log
   ```

2. **Logs estão habilitados no código:**
   - `backend/config.php` - logs de conexão
   - `backend/api/auth.php` - logs de autenticação

### Passo 8: Teste Completo

Após seguir todos os passos, teste:

1. **Login com usuário demo:**
   - Username: `demo_user`
   - Password: `123456`

2. **Criar nova conta:**
   - Preencha todos os campos
   - Password mínimo 6 caracteres

3. **Verificar se funciona:**
   - Login/Logout
   - Criação de hábitos
   - Sistema de pontuação

## Credenciais de Teste

### Usuário Demo (criado automaticamente)
- **Username:** `demo_user`
- **Password:** `123456`
- **Email:** `demo@example.com`

### Para criar nova conta
- **Username:** Qualquer nome único
- **Email:** Email válido
- **Password:** Mínimo 6 caracteres

## Comandos Úteis

```bash
# Testar conexão
php test_connection.php

# Reset completo do banco
php reset_database.php

# Iniciar servidor
php -S localhost:8000

# Verificar se MySQL está rodando
mysql -u root -p -e "SELECT 1;"
```

## Estrutura de Arquivos Importantes

```
backend/
├── config.php              # Configurações do banco
├── test_connection.php     # Teste de conexão
├── reset_database.php      # Reset completo
└── api/
    └── auth.php            # Sistema de autenticação

frontend/
├── index.html              # Interface principal
└── js/
    └── app.js              # Lógica JavaScript
```

## Se Nada Funcionar

1. **Verifique se o PHP tem as extensões necessárias:**
   ```bash
   php -m | grep -E "(pdo|mysql|json)"
   ```

2. **Verifique a versão do PHP:**
   ```bash
   php -v
   # Deve ser 7.4 ou superior
   ```

3. **Verifique a versão do MySQL:**
   ```sql
   SELECT VERSION();
   # Deve ser 5.7 ou superior
   ```

4. **Reinicie tudo:**
   ```bash
   # Parar servidor PHP (Ctrl+C)
   # Reiniciar MySQL
   # Executar reset_database.php
   # Iniciar servidor PHP novamente
   ```

## Contato para Suporte

Se ainda houver problemas, verifique:
1. Logs de erro do PHP
2. Console do navegador
3. Network tab do navegador
4. Mensagens de erro específicas
