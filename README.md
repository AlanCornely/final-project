# Healthy Habits App - Modo Demo

## 🎯 Sobre o Projeto

Healthy Habits é uma aplicação web para gerenciamento de hábitos saudáveis, desenvolvida com **PHP** (backend) e **JavaScript** (frontend). A aplicação permite aos usuários criar, gerenciar e acompanhar seus hábitos saudáveis, ganhar pontos por completações e conquistar badges.

## 🚀 **NOVO: Modo Demo Ativado**

A aplicação agora funciona em **modo demo** sem necessidade de:
- ✅ Login/Registro
- ✅ Configuração de banco de dados
- ✅ Servidor PHP rodando

### Como Usar (Modo Demo)

1. **Abra o arquivo `frontend/index.html`** em qualquer navegador
2. **Use a aplicação diretamente** - todos os dados são simulados
3. **Teste todas as funcionalidades** sem configuração adicional

## 🛠️ Funcionalidades Disponíveis

### 📊 Dashboard
- Visão geral dos hábitos ativos
- Estatísticas de completações
- Badges conquistadas
- Atividade recente

### 🎯 Gerenciamento de Hábitos
- **Criar** novos hábitos
- **Editar** hábitos existentes
- **Excluir** hábitos
- **Marcar** como completado
- **Definir** recompensas e pontos

### 🏆 Sistema de Pontos e Badges
- Ganhe pontos por completar hábitos
- Conquiste badges por atingir metas
- Visualize progresso e conquistas

### 📈 Ranking
- Compare-se com outros usuários
- Filtros por pontos, badges e completações
- Busca por nome de usuário

### 👤 Perfil
- Visualize estatísticas pessoais
- Edite informações do perfil
- Veja badges conquistadas

## 🏗️ Estrutura do Projeto

```
healthy-habits-app/
├── frontend/                 # Interface do usuário
│   ├── index.html           # Página principal
│   ├── js/
│   │   └── app.js          # Lógica da aplicação (modo demo)
│   └── package.json
├── backend/                  # API e banco de dados (opcional)
│   ├── api/                 # Endpoints da API
│   ├── config.php           # Configurações
│   ├── database.sql         # Estrutura do banco
│   └── setup_standalone.php # Script de configuração
└── README.md
```

## 🎮 Como Testar (Modo Demo)

### 1. Abrir a Aplicação
```bash
# Navegue até a pasta frontend
cd healthy-habits-app/frontend

# Abra o index.html no navegador
# Ou use um servidor local simples:
python -m http.server 3000
# Depois acesse: http://localhost:3000
```

### 2. Funcionalidades para Testar
- ✅ **Criar hábitos**: Clique em "Criar Hábito" e preencha os dados
- ✅ **Completar hábitos**: Clique em "Marcar como Concluído"
- ✅ **Ver ranking**: Navegue para a seção "Ranking"
- ✅ **Editar perfil**: Acesse "Perfil" e modifique os dados
- ✅ **Excluir hábitos**: Use o botão de lixeira nos cards

### 3. Dados de Demonstração
A aplicação vem com dados simulados incluindo:
- 4 hábitos pré-criados
- 3 badges disponíveis
- Ranking com usuários fictícios
- Sistema de pontos funcionando

## 🔧 Configuração Completa (Opcional)

Se quiser usar a versão completa com banco de dados:

### Pré-requisitos
- PHP 7.4+
- MySQL 5.7+
- Servidor web (Apache/Nginx) ou servidor PHP embutido

### Instalação
1. **Configure o banco de dados**:
   ```bash
   cd backend
   php setup_standalone.php
   ```

2. **Inicie o servidor PHP**:
   ```bash
   php -S localhost:8000
   ```

3. **Acesse a aplicação**:
   ```
   http://localhost:8000/frontend/
   ```

## 🐛 Problemas Conhecidos (Resolvidos)

### ❌ Problemas de Login (ANTES)
- Erro de conexão com banco de dados
- Problemas de autenticação PHP
- Sessões não funcionando
- CORS issues
- Credenciais incorretas

### ✅ Solução Implementada
- **Modo demo ativado** - funciona sem banco de dados
- **Dados simulados** - experiência completa sem configuração
- **Sem dependências** - roda em qualquer navegador
- **Funcionalidades completas** - todas as features disponíveis

## 📝 Notas Técnicas

### Modo Demo
- Todos os dados são armazenados em memória
- Mudanças são perdidas ao recarregar a página
- Funciona completamente offline
- Ideal para demonstração e testes

### Tecnologias Utilizadas
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 7.4+, MySQL (opcional)
- **UI Framework**: Tailwind CSS
- **Ícones**: Lucide Icons

## 🤝 Contribuição

Para contribuir com o projeto:
1. Faça um fork do repositório
2. Crie uma branch para sua feature
3. Commit suas mudanças
4. Abra um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo LICENSE para mais detalhes.

---

**🎉 Agora você pode usar a aplicação Healthy Habits imediatamente, sem configuração!**

