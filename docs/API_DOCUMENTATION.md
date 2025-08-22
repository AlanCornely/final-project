# API Documentation - Healthy Habits

## Visão Geral

A API do Healthy Habits é uma API RESTful desenvolvida em PHP que fornece endpoints para gerenciar usuários, hábitos, completions, badges e ranking em um sistema gamificado de hábitos saudáveis.

**Base URL**: `http://localhost:8000/api`

## Configuração

### Headers Obrigatórios

```
Content-Type: application/json
```

### CORS

A API está configurada para aceitar requisições de qualquer origem:

```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization
```

## Endpoints

### 1. Users API (`/users.php`)

Gerenciamento de usuários do sistema.

#### GET - Listar Usuários

**Endpoint**: `GET /api/users.php`

**Descrição**: Retorna lista de todos os usuários ordenados por pontos (decrescente).

**Resposta**:
```json
[
    {
        "id": 1,
        "username": "joao_silva",
        "email": "joao@email.com",
        "total_points": 150
    },
    {
        "id": 2,
        "username": "maria_santos",
        "email": "maria@email.com",
        "total_points": 75
    }
]
```

#### GET - Buscar Usuário por ID

**Endpoint**: `GET /api/users.php?id={user_id}`

**Parâmetros**:
- `id` (integer): ID do usuário

**Resposta**:
```json
{
    "id": 1,
    "username": "joao_silva",
    "email": "joao@email.com",
    "total_points": 150,
    "badges": [
        {
            "name": "Iniciante",
            "description": "Primeiros passos na jornada saudável",
            "awarded_date": "2025-08-20 10:30:00"
        }
    ]
}
```

#### POST - Criar Usuário

**Endpoint**: `POST /api/users.php`

**Body**:
```json
{
    "username": "novo_usuario",
    "email": "novo@email.com",
    "password": "senha123"
}
```

**Resposta** (201):
```json
{
    "id": 3,
    "message": "Usuário criado com sucesso"
}
```

#### PUT - Atualizar Usuário

**Endpoint**: `PUT /api/users.php`

**Body**:
```json
{
    "id": 1,
    "username": "joao_silva_updated",
    "email": "joao_novo@email.com",
    "total_points": 200
}
```

**Resposta**:
```json
{
    "message": "Usuário atualizado com sucesso"
}
```

#### DELETE - Deletar Usuário

**Endpoint**: `DELETE /api/users.php?id={user_id}`

**Parâmetros**:
- `id` (integer): ID do usuário

**Resposta**:
```json
{
    "message": "Usuário deletado com sucesso"
}
```

### 2. Habits API (`/habits.php`)

Gerenciamento de hábitos dos usuários.

#### GET - Listar Todos os Hábitos

**Endpoint**: `GET /api/habits.php`

**Resposta**:
```json
[
    {
        "id": 1,
        "user_id": 2,
        "name": "Beber 2L de água",
        "description": "Manter-se hidratado bebendo pelo menos 2 litros de água por dia",
        "points_per_completion": 10,
        "username": "joao_silva"
    }
]
```

#### GET - Listar Hábitos por Usuário

**Endpoint**: `GET /api/habits.php?user_id={user_id}`

**Parâmetros**:
- `user_id` (integer): ID do usuário

**Resposta**:
```json
[
    {
        "id": 1,
        "user_id": 2,
        "name": "Beber 2L de água",
        "description": "Manter-se hidratado bebendo pelo menos 2 litros de água por dia",
        "points_per_completion": 10,
        "total_completions": 5,
        "last_completion": "2025-08-22 14:30:00"
    }
]
```

#### GET - Buscar Hábito por ID

**Endpoint**: `GET /api/habits.php?id={habit_id}`

**Parâmetros**:
- `id` (integer): ID do hábito

**Resposta**:
```json
{
    "id": 1,
    "user_id": 2,
    "name": "Beber 2L de água",
    "description": "Manter-se hidratado bebendo pelo menos 2 litros de água por dia",
    "points_per_completion": 10,
    "username": "joao_silva",
    "completions": [
        {
            "id": 1,
            "habit_id": 1,
            "user_id": 2,
            "completion_date": "2025-08-22 14:30:00",
            "points_earned": 10
        }
    ]
}
```

#### POST - Criar Hábito

**Endpoint**: `POST /api/habits.php`

**Body**:
```json
{
    "user_id": 2,
    "name": "Exercitar-se 30min",
    "description": "Fazer pelo menos 30 minutos de exercício físico",
    "points_per_completion": 15
}
```

**Resposta** (201):
```json
{
    "id": 4,
    "message": "Hábito criado com sucesso"
}
```

#### PUT - Atualizar Hábito

**Endpoint**: `PUT /api/habits.php`

**Body**:
```json
{
    "id": 1,
    "name": "Beber 3L de água",
    "description": "Aumentar hidratação para 3 litros",
    "points_per_completion": 15
}
```

**Resposta**:
```json
{
    "message": "Hábito atualizado com sucesso"
}
```

#### DELETE - Deletar Hábito

**Endpoint**: `DELETE /api/habits.php?id={habit_id}`

**Parâmetros**:
- `id` (integer): ID do hábito

**Resposta**:
```json
{
    "message": "Hábito deletado com sucesso"
}
```

### 3. Completions API (`/completions.php`)

Gerenciamento de completions de hábitos.

#### GET - Listar Todas as Completions

**Endpoint**: `GET /api/completions.php`

**Resposta**:
```json
[
    {
        "id": 1,
        "habit_id": 1,
        "user_id": 2,
        "completion_date": "2025-08-22 14:30:00",
        "points_earned": 10,
        "habit_name": "Beber 2L de água",
        "username": "joao_silva"
    }
]
```

#### GET - Listar Completions por Usuário

**Endpoint**: `GET /api/completions.php?user_id={user_id}`

**Parâmetros**:
- `user_id` (integer): ID do usuário

**Resposta**:
```json
[
    {
        "id": 1,
        "habit_id": 1,
        "user_id": 2,
        "completion_date": "2025-08-22 14:30:00",
        "points_earned": 10,
        "habit_name": "Beber 2L de água"
    }
]
```

#### GET - Listar Completions por Hábito

**Endpoint**: `GET /api/completions.php?habit_id={habit_id}`

**Parâmetros**:
- `habit_id` (integer): ID do hábito

**Resposta**:
```json
[
    {
        "id": 1,
        "habit_id": 1,
        "user_id": 2,
        "completion_date": "2025-08-22 14:30:00",
        "points_earned": 10,
        "username": "joao_silva"
    }
]
```

#### POST - Criar Completion

**Endpoint**: `POST /api/completions.php`

**Body**:
```json
{
    "habit_id": 1,
    "user_id": 2
}
```

**Resposta** (201):
```json
{
    "id": 5,
    "points_earned": 10,
    "message": "Hábito completado com sucesso!"
}
```

**Funcionalidades Automáticas**:
- Calcula pontos baseado no hábito
- Atualiza pontos totais do usuário
- Verifica e atribui badges automaticamente

#### DELETE - Deletar Completion

**Endpoint**: `DELETE /api/completions.php?id={completion_id}`

**Parâmetros**:
- `id` (integer): ID da completion

**Resposta**:
```json
{
    "message": "Completion deletada com sucesso"
}
```

**Funcionalidades Automáticas**:
- Remove pontos do usuário
- Mantém consistência dos dados

### 4. Badges API (`/badges.php`)

Gerenciamento de badges/conquistas.

#### GET - Listar Todos os Badges

**Endpoint**: `GET /api/badges.php`

**Resposta**:
```json
[
    {
        "id": 1,
        "name": "Iniciante",
        "description": "Primeiros passos na jornada saudável",
        "points_threshold": 0
    },
    {
        "id": 2,
        "name": "Dedicado",
        "description": "Alcançou 50 pontos",
        "points_threshold": 50
    }
]
```

#### GET - Listar Badges por Usuário

**Endpoint**: `GET /api/badges.php?user_id={user_id}`

**Parâmetros**:
- `user_id` (integer): ID do usuário

**Resposta**:
```json
[
    {
        "id": 1,
        "name": "Iniciante",
        "description": "Primeiros passos na jornada saudável",
        "points_threshold": 0,
        "awarded_date": "2025-08-20 10:30:00"
    }
]
```

#### POST - Criar Badge

**Endpoint**: `POST /api/badges.php`

**Body**:
```json
{
    "name": "Maratonista",
    "description": "Completou 100 exercícios",
    "points_threshold": 1500
}
```

**Resposta** (201):
```json
{
    "id": 7,
    "message": "Badge criado com sucesso"
}
```

#### PUT - Atualizar Badge

**Endpoint**: `PUT /api/badges.php`

**Body**:
```json
{
    "id": 1,
    "name": "Iniciante Atualizado",
    "description": "Nova descrição",
    "points_threshold": 5
}
```

**Resposta**:
```json
{
    "message": "Badge atualizado com sucesso"
}
```

#### DELETE - Deletar Badge

**Endpoint**: `DELETE /api/badges.php?id={badge_id}`

**Parâmetros**:
- `id` (integer): ID do badge

**Resposta**:
```json
{
    "message": "Badge deletado com sucesso"
}
```

### 5. Ranking API (`/ranking.php`)

Sistema de ranking dos usuários.

#### GET - Obter Ranking

**Endpoint**: `GET /api/ranking.php`

**Parâmetros Opcionais**:
- `search` (string): Buscar por username
- `order_by` (string): Campo de ordenação (`points`, `username`, `badges_count`)
- `order_dir` (string): Direção da ordenação (`ASC`, `DESC`)
- `limit` (integer): Número máximo de resultados (1-100)

**Exemplo**: `GET /api/ranking.php?order_by=points&order_dir=DESC&limit=10&search=joao`

**Resposta**:
```json
{
    "ranking": [
        {
            "id": 2,
            "username": "joao_silva",
            "email": "joao@email.com",
            "points": 150,
            "badges_count": 3,
            "completions_count": 15,
            "position": 1,
            "badges": [
                {
                    "name": "Iniciante",
                    "description": "Primeiros passos na jornada saudável"
                },
                {
                    "name": "Dedicado",
                    "description": "Alcançou 50 pontos"
                },
                {
                    "name": "Persistente",
                    "description": "Alcançou 100 pontos"
                }
            ]
        }
    ],
    "filters": {
        "search": "joao",
        "order_by": "points",
        "order_dir": "DESC",
        "limit": 10
    }
}
```

## Códigos de Status HTTP

### Sucesso
- `200 OK`: Operação realizada com sucesso
- `201 Created`: Recurso criado com sucesso

### Erro do Cliente
- `400 Bad Request`: Dados obrigatórios não fornecidos ou inválidos
- `404 Not Found`: Recurso não encontrado
- `405 Method Not Allowed`: Método HTTP não permitido
- `409 Conflict`: Conflito (ex: username/email já existem)

### Erro do Servidor
- `500 Internal Server Error`: Erro interno do servidor

## Formato de Resposta de Erro

```json
{
    "error": "Descrição do erro"
}
```

## Exemplos de Uso

### Fluxo Completo: Criar Usuário e Hábito

```bash
# 1. Criar usuário
curl -X POST http://localhost:8000/api/users.php \
  -H "Content-Type: application/json" \
  -d '{
    "username": "teste_usuario",
    "email": "teste@email.com",
    "password": "senha123"
  }'

# 2. Criar hábito para o usuário
curl -X POST http://localhost:8000/api/habits.php \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 3,
    "name": "Ler 30 minutos",
    "description": "Leitura diária para desenvolvimento pessoal",
    "points_per_completion": 12
  }'

# 3. Completar o hábito
curl -X POST http://localhost:8000/api/completions.php \
  -H "Content-Type: application/json" \
  -d '{
    "habit_id": 4,
    "user_id": 3
  }'

# 4. Verificar badges do usuário
curl http://localhost:8000/api/badges.php?user_id=3

# 5. Ver posição no ranking
curl http://localhost:8000/api/ranking.php?search=teste_usuario
```

### JavaScript/Fetch Examples

```javascript
// Função auxiliar para requisições
async function apiRequest(endpoint, options = {}) {
    const url = `http://localhost:8000/api${endpoint}`;
    const config = {
        headers: {
            'Content-Type': 'application/json',
            ...options.headers
        },
        ...options
    };

    const response = await fetch(url, config);
    const data = await response.json();
    
    if (!response.ok) {
        throw new Error(data.error || 'Erro na requisição');
    }
    
    return data;
}

// Criar hábito
async function createHabit(habitData) {
    return await apiRequest('/habits.php', {
        method: 'POST',
        body: JSON.stringify(habitData)
    });
}

// Completar hábito
async function completeHabit(habitId, userId) {
    return await apiRequest('/completions.php', {
        method: 'POST',
        body: JSON.stringify({
            habit_id: habitId,
            user_id: userId
        })
    });
}

// Buscar ranking
async function getRanking(filters = {}) {
    const params = new URLSearchParams(filters);
    return await apiRequest(`/ranking.php?${params}`);
}
```

## Lógica de Negócio

### Sistema de Pontos

1. **Atribuição**: Pontos são atribuídos automaticamente ao completar um hábito
2. **Cálculo**: Baseado no campo `points_per_completion` do hábito
3. **Atualização**: Pontos totais do usuário são atualizados em tempo real
4. **Transações**: Operações são atômicas para garantir consistência

### Sistema de Badges

1. **Verificação Automática**: Executada a cada completion
2. **Critério**: Baseado em `points_threshold` dos badges
3. **Atribuição**: Apenas badges não possuídos são atribuídos
4. **Persistência**: Relacionamento many-to-many entre users e badges

### Ranking

1. **Ordenação Padrão**: Por pontos (decrescente)
2. **Filtros**: Busca por username, ordenação customizada
3. **Posição**: Calculada dinamicamente baseada na ordenação
4. **Dados Agregados**: Inclui contagem de badges e completions

## Considerações de Performance

### Otimizações Implementadas

1. **Índices**: Chaves primárias e estrangeiras otimizadas
2. **JOINs Eficientes**: Queries otimizadas para reduzir N+1
3. **LIMIT**: Paginação para evitar sobrecarga
4. **Prepared Statements**: Prevenção de SQL injection e cache de queries

### Limitações

1. **Rate Limiting**: Não implementado (recomendado para produção)
2. **Cache**: Não implementado (considerar Redis para produção)
3. **Paginação**: Básica via LIMIT (considerar offset para grandes datasets)

## Segurança

### Medidas Implementadas

1. **SQL Injection**: Prevenido via prepared statements
2. **XSS**: Headers de resposta seguros
3. **CORS**: Configurado apropriadamente
4. **Validação**: Dados validados no backend

### Recomendações para Produção

1. **Autenticação**: Implementar JWT ou sessões
2. **Autorização**: Verificar permissões por recurso
3. **HTTPS**: Obrigatório para dados sensíveis
4. **Rate Limiting**: Prevenir abuso da API
5. **Logs**: Auditoria de operações críticas

## Versionamento

**Versão Atual**: 1.0.0

Para futuras versões, considerar:
- Versionamento na URL (`/api/v1/`)
- Headers de versão
- Backward compatibility
- Deprecation notices

---

*Documentação da API gerada em: Agosto de 2025*

