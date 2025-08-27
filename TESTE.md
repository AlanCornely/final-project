# Guia de Teste - Healthy Habits App

Este guia ajudará você a testar todas as correções implementadas no sistema.

## Pré-requisitos

1. Banco de dados MySQL configurado
2. PHP 7.4+ instalado
3. Servidor web rodando

## Passos para Teste

### 1. Configuração Inicial

```bash
# 1. Criar banco de dados
mysql -u root -p
CREATE DATABASE healthy_habits;
exit;

# 2. Configurar credenciais no backend/config.php
# 3. Executar script de configuração
cd backend
php setup_database.php
```

### 2. Iniciar Servidor

```bash
cd backend
php -S localhost:8000
```

### 3. Testar Funcionalidades

#### A. Sistema de Login ✅

**Teste 1: Registro de Nova Conta**
1. Abra `frontend/index.html` no navegador
2. Clique em "Não tem uma conta? Registre-se"
3. Preencha os campos:
   - Username: `teste_user`
   - Email: `teste@example.com`
   - Password: `123456`
4. Clique em "Criar Conta"
5. **Resultado esperado**: Conta criada com sucesso, redirecionamento para dashboard

**Teste 2: Login com Usuário Existente**
1. Use as credenciais do usuário demo:
   - Username: `demo_user`
   - Password: `123456`
2. Clique em "Entrar"
3. **Resultado esperado**: Login realizado com sucesso

**Teste 3: Logout**
1. Após fazer login, clique no ícone de logout no canto superior direito
2. **Resultado esperado**: Logout realizado, retorno à tela de login

#### B. Criação de Hábitos ✅

**Teste 4: Criar Novo Hábito**
1. Faça login na aplicação
2. Clique em "Adicionar Novo Hábito" no dashboard
3. Preencha os campos:
   - Nome: `Beber 2L de água por dia`
   - Descrição: `Manter-se hidratado é essencial`
   - Pontos: `15`
   - Recompensa: `Assistir um episódio da série favorita`
4. Clique em "Criar Hábito"
5. **Resultado esperado**: Hábito criado com sucesso, aparece na lista

**Teste 5: Verificar Campo de Recompensas**
1. Na lista de hábitos, verifique se a recompensa aparece em um card amarelo
2. **Resultado esperado**: Recompensa visível com ícone de presente

#### C. Sistema de Pontuação ✅

**Teste 6: Completar Hábito**
1. Na lista de hábitos, clique em "Marcar como Concluído"
2. **Resultado esperado**: 
   - Notificação de pontos ganhos
   - Contador de pontos atualizado no dashboard
   - Contador de completions hoje atualizado

**Teste 7: Verificar Pontuação Fixa**
1. Crie hábitos com diferentes pontuações (5, 10, 20 pontos)
2. Complete cada um
3. **Resultado esperado**: Pontos ganhos correspondem exatamente aos definidos

#### D. Interface e Navegação ✅

**Teste 8: Verificar Informações do Usuário**
1. Após login, verifique se o nome do usuário aparece:
   - No cabeçalho da página (canto superior direito)
   - Na mensagem de boas-vindas do dashboard
2. **Resultado esperado**: Nome do usuário visível em ambos os locais

**Teste 9: Navegação entre Seções**
1. Teste todos os botões de navegação:
   - Dashboard
   - Meus Hábitos
   - Ranking
   - Perfil
2. **Resultado esperado**: Navegação suave entre todas as seções

#### E. Ranking e Badges ✅

**Teste 10: Verificar Ranking**
1. Vá para a seção "Ranking"
2. **Resultado esperado**: Lista de usuários ordenados por pontos

**Teste 11: Verificar Badges**
1. Complete alguns hábitos para ganhar pontos
2. Vá para "Perfil" e verifique a seção "Minhas Conquistas"
3. **Resultado esperado**: Badges aparecem conforme pontos ganhos

## Problemas Corrigidos

### ❌ Antes das Correções:
1. **Sistema de Login**: Não existia, apenas usuário simulado
2. **Criação de Hábitos**: Não funcionava corretamente
3. **Sistema de Pontuação**: Sem campo de recompensas
4. **Interface**: Sem botão de logout, sem indicação do usuário logado

### ✅ Após as Correções:
1. **Sistema de Login**: ✅ Sistema completo de autenticação
2. **Criação de Hábitos**: ✅ Funciona perfeitamente com campo de recompensas
3. **Sistema de Pontuação**: ✅ Pontuação fixa + recompensas opcionais
4. **Interface**: ✅ Botão de logout + indicação clara do usuário

## Dados de Teste

### Usuário Demo
- **Username**: `demo_user`
- **Password**: `123456`

### Hábitos de Exemplo para Teste
1. **Beber 2L de água** (10 pontos) - Recompensa: "Assistir série"
2. **Meditar 10 minutos** (15 pontos) - Recompensa: "Chocolate"
3. **Exercício 30 min** (20 pontos) - Recompensa: "Banho relaxante"

## Troubleshooting

### Problema: "Erro de conexão com banco de dados"
**Solução**: Verificar credenciais no `backend/config.php`

### Problema: "CORS error"
**Solução**: Verificar se o servidor está rodando na porta 8000

### Problema: "Sessão não mantida"
**Solução**: Verificar se `credentials: 'include'` está configurado

## Conclusão

Após seguir este guia de teste, todas as funcionalidades devem estar funcionando corretamente. O sistema agora possui:

- ✅ Autenticação completa
- ✅ Criação de hábitos funcional
- ✅ Sistema de pontuação com recompensas
- ✅ Interface melhorada com logout e informações do usuário
- ✅ Todas as funcionalidades originais mantidas

