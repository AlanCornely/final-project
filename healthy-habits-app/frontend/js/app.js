// Configuration
const API_BASE_URL = 'http://localhost:8000/api';
let currentUser = null;

// State management
let appState = {
    habits: [],
    completions: [],
    badges: [],
    ranking: [],
    userStats: {}
};

// Initialize the application
async function initializeApp() {
    try {
        // Check if user is authenticated
        const authCheck = await checkAuthentication();
        if (authCheck.authenticated) {
            currentUser = authCheck.user;
            showMainApp();
            await loadUserData();
            await loadHabits();
            await loadBadges();
            showSection('dashboard');
            updateDashboard();
        } else {
            showAuthSection();
        }
    } catch (error) {
        console.error('Error initializing app:', error);
        showAuthSection();
    }
}

// Authentication functions
async function checkAuthentication() {
    try {
        const response = await fetch(`${API_BASE_URL}/auth.php?action=check`, {
            credentials: 'include'
        });
        return await response.json();
    } catch (error) {
        console.error('Auth check error:', error);
        return { authenticated: false };
    }
}

async function login(event) {
    event.preventDefault();
    
    const username = document.getElementById('login-username').value;
    const password = document.getElementById('login-password').value;
    
    try {
        const response = await fetch(`${API_BASE_URL}/auth.php?action=login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({ username, password })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            currentUser = data.user;
            showNotification('Login realizado com sucesso!', 'success');
            showMainApp();
            await loadUserData();
            await loadHabits();
            await loadBadges();
            showSection('dashboard');
            updateDashboard();
        } else {
            showNotification(data.error || 'Erro no login', 'error');
        }
    } catch (error) {
        console.error('Login error:', error);
        showNotification('Erro ao fazer login', 'error');
    }
}

async function register(event) {
    event.preventDefault();
    
    const username = document.getElementById('register-username').value;
    const email = document.getElementById('register-email').value;
    const password = document.getElementById('register-password').value;
    
    try {
        const response = await fetch(`${API_BASE_URL}/auth.php?action=register`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({ username, email, password })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            currentUser = data.user;
            showNotification('Conta criada com sucesso!', 'success');
            showMainApp();
            await loadUserData();
            await loadHabits();
            await loadBadges();
            showSection('dashboard');
            updateDashboard();
        } else {
            showNotification(data.error || 'Erro ao criar conta', 'error');
        }
    } catch (error) {
        console.error('Register error:', error);
        showNotification('Erro ao criar conta', 'error');
    }
}

async function logout() {
    try {
        await fetch(`${API_BASE_URL}/auth.php?action=logout`, {
            method: 'POST',
            credentials: 'include'
        });
        
        currentUser = null;
        showNotification('Logout realizado com sucesso!', 'success');
        showAuthSection();
    } catch (error) {
        console.error('Logout error:', error);
        showNotification('Erro ao fazer logout', 'error');
    }
}

// UI Functions
function showAuthSection() {
    document.getElementById('auth-section').classList.remove('hidden');
    document.getElementById('main-app').classList.add('hidden');
}

function showMainApp() {
    document.getElementById('auth-section').classList.add('hidden');
    document.getElementById('main-app').classList.remove('hidden');
    updateUserDisplay();
}

function showLoginForm() {
    document.getElementById('login-form').classList.remove('hidden');
    document.getElementById('register-form').classList.add('hidden');
}

function showRegisterForm() {
    document.getElementById('login-form').classList.add('hidden');
    document.getElementById('register-form').classList.remove('hidden');
}

function showSection(sectionName) {
    // Hide all sections
    document.querySelectorAll('.section').forEach(section => {
        section.classList.add('hidden');
    });
    
    // Show selected section
    document.getElementById(`${sectionName}-section`).classList.remove('hidden');
    
    // Load section-specific data
    if (sectionName === 'ranking') {
        loadRanking();
    } else if (sectionName === 'profile') {
        loadProfile();
    }
}

function updateUserDisplay() {
    if (currentUser) {
        document.getElementById('user-display').textContent = currentUser.username;
        document.getElementById('welcome-username').textContent = currentUser.username;
    }
}

function updateDashboard() {
    // Update stats
    document.getElementById('active-habits').textContent = appState.habits.length;
    document.getElementById('user-badges').textContent = appState.badges.length;
    
    // Calculate today's completions
    const today = new Date().toISOString().split('T')[0];
    const todayCompletions = appState.completions.filter(completion => 
        completion.completion_date.startsWith(today)
    ).length;
    document.getElementById('today-completions').textContent = todayCompletions;
    
    // Render recent activity
    renderRecentActivity();
}

// API Functions
async function apiRequest(endpoint, options = {}) {
    const url = `${API_BASE_URL}${endpoint}`;
    const config = {
        headers: {
            'Content-Type': 'application/json',
            ...options.headers
        },
        credentials: 'include',
        ...options
    };

    try {
        const response = await fetch(url, config);
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.error || 'Erro na requisição');
        }
        
        return data;
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}

// Data loading functions
async function loadUserData() {
    try {
        const userData = await apiRequest(`/users.php?id=${currentUser.id}`);
        appState.userStats = userData;
        document.getElementById('user-points').textContent = userData.total_points || 0;
    } catch (error) {
        console.error('Error loading user data:', error);
    }
}

async function loadHabits() {
    try {
        const habits = await apiRequest(`/habits.php?user_id=${currentUser.id}`);
        appState.habits = habits;
        renderHabits();
    } catch (error) {
        console.error('Error loading habits:', error);
    }
}

async function loadBadges() {
    try {
        const badges = await apiRequest(`/badges.php?user_id=${currentUser.id}`);
        appState.badges = badges;
        renderBadges();
    } catch (error) {
        console.error('Error loading badges:', error);
    }
}

async function loadRanking() {
    try {
        const searchTerm = document.getElementById('search-input').value;
        const sortBy = document.getElementById('sort-select').value;
        
        const params = new URLSearchParams({
            order_by: sortBy,
            order_dir: 'DESC',
            limit: 20
        });
        
        if (searchTerm) {
            params.append('search', searchTerm);
        }
        
        const rankingData = await apiRequest(`/ranking.php?${params}`);
        appState.ranking = rankingData.ranking;
        renderRanking();
    } catch (error) {
        console.error('Error loading ranking:', error);
    }
}

// Rendering functions
function renderHabits() {
    const habitsGrid = document.getElementById('habits-grid');
    
    if (appState.habits.length === 0) {
        habitsGrid.innerHTML = `
            <div class="col-span-full text-center py-12">
                <i data-lucide="target" class="h-12 w-12 text-gray-400 mx-auto mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum hábito encontrado</h3>
                <p class="text-gray-500 mb-4">Comece criando seu primeiro hábito saudável!</p>
                <button onclick="showAddHabitModal()" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg transition-colors">
                    Criar Primeiro Hábito
                </button>
            </div>
        `;
        return;
    }
    
    habitsGrid.innerHTML = appState.habits.map(habit => `
        <div class="bg-white rounded-xl shadow-md p-6 card-hover">
            <div class="flex justify-between items-start mb-4">
                <h3 class="text-lg font-semibold text-gray-900">${habit.name}</h3>
                <div class="flex space-x-2">
                    <button onclick="editHabit(${habit.id})" class="text-gray-400 hover:text-gray-600">
                        <i data-lucide="edit-2" class="h-4 w-4"></i>
                    </button>
                    <button onclick="deleteHabit(${habit.id})" class="text-gray-400 hover:text-red-600">
                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                    </button>
                </div>
            </div>
            
            <p class="text-gray-600 text-sm mb-4">${habit.description || 'Sem descrição'}</p>
            
            ${habit.reward_description ? `
                <div class="mb-4 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                    <div class="flex items-center">
                        <i data-lucide="gift" class="h-4 w-4 text-yellow-600 mr-2"></i>
                        <span class="text-sm text-yellow-800 font-medium">Recompensa:</span>
                    </div>
                    <p class="text-sm text-yellow-700 mt-1">${habit.reward_description}</p>
                </div>
            ` : ''}
            
            <div class="flex justify-between items-center mb-4">
                <span class="text-sm text-gray-500">
                    <i data-lucide="star" class="h-4 w-4 inline mr-1"></i>
                    ${habit.points_per_completion} pontos
                </span>
                <span class="text-sm text-gray-500">
                    <i data-lucide="check-circle" class="h-4 w-4 inline mr-1"></i>
                    ${habit.total_completions || 0} vezes
                </span>
            </div>
            
            <button onclick="completeHabit(${habit.id})" class="w-full bg-success-600 hover:bg-success-700 text-white py-2 px-4 rounded-lg transition-colors flex items-center justify-center">
                <i data-lucide="check" class="h-4 w-4 mr-2"></i>
                Marcar como Concluído
            </button>
        </div>
    `).join('');
    
    // Re-initialize Lucide icons
    lucide.createIcons();
}

function renderBadges() {
    const badgesContainer = document.getElementById('profile-badges');
    
    if (appState.badges.length === 0) {
        badgesContainer.innerHTML = `
            <div class="text-center py-8">
                <i data-lucide="award" class="h-12 w-12 text-gray-400 mx-auto mb-4"></i>
                <p class="text-gray-500">Nenhuma conquista ainda</p>
                <p class="text-sm text-gray-400">Complete hábitos para ganhar badges!</p>
            </div>
        `;
        return;
    }
    
    badgesContainer.innerHTML = appState.badges.map(badge => `
        <div class="flex items-center p-3 bg-gradient-to-r from-yellow-50 to-yellow-100 rounded-lg badge-glow">
            <div class="flex-shrink-0">
                <i data-lucide="award" class="h-8 w-8 text-yellow-600"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-gray-900">${badge.name}</p>
                <p class="text-xs text-gray-500">${badge.description}</p>
            </div>
        </div>
    `).join('');
    
    lucide.createIcons();
}

function renderRanking() {
    const rankingList = document.getElementById('ranking-list');
    
    if (appState.ranking.length === 0) {
        rankingList.innerHTML = `
            <div class="text-center py-12">
                <i data-lucide="trophy" class="h-12 w-12 text-gray-400 mx-auto mb-4"></i>
                <p class="text-gray-500">Nenhum usuário encontrado</p>
            </div>
        `;
        return;
    }
    
    rankingList.innerHTML = appState.ranking.map((user, index) => `
        <div class="flex items-center justify-between p-6 hover:bg-gray-50 transition-colors">
            <div class="flex items-center">
                <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full ${
                    index === 0 ? 'bg-yellow-100 text-yellow-800' :
                    index === 1 ? 'bg-gray-100 text-gray-800' :
                    index === 2 ? 'bg-orange-100 text-orange-800' :
                    'bg-gray-50 text-gray-600'
                }">
                    ${index < 3 ? '🏆' : user.position}
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-900">${user.username}</p>
                    <p class="text-sm text-gray-500">${user.badges_count} badges • ${user.completions_count} completions</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-lg font-semibold text-gray-900">${user.points}</p>
                <p class="text-sm text-gray-500">pontos</p>
            </div>
        </div>
    `).join('');
}

function renderRecentActivity() {
    const activityContainer = document.getElementById('recent-activity');
    
    // Simulate recent activity
    const recentActivity = [
        { type: 'completion', text: 'Você completou "Beber 2L de água"', time: '2 horas atrás', points: 10 },
        { type: 'badge', text: 'Nova conquista desbloqueada: "Dedicado"', time: '1 dia atrás', points: 0 },
        { type: 'habit', text: 'Novo hábito criado: "Meditar 10min"', time: '2 dias atrás', points: 0 }
    ];
    
    activityContainer.innerHTML = recentActivity.map(activity => `
        <div class="flex items-center p-4 bg-gray-50 rounded-lg">
            <div class="flex-shrink-0">
                <i data-lucide="${
                    activity.type === 'completion' ? 'check-circle' :
                    activity.type === 'badge' ? 'award' : 'plus-circle'
                }" class="h-5 w-5 text-${
                    activity.type === 'completion' ? 'success' :
                    activity.type === 'badge' ? 'warning' : 'primary'
                }-600"></i>
            </div>
            <div class="ml-3 flex-1">
                <p class="text-sm text-gray-900">${activity.text}</p>
                <p class="text-xs text-gray-500">${activity.time}</p>
            </div>
            ${activity.points > 0 ? `
                <div class="text-sm font-medium text-success-600">
                    +${activity.points} pts
                </div>
            ` : ''}
        </div>
    `).join('');
    
    lucide.createIcons();
}

// Modal functions
function showAddHabitModal() {
    document.getElementById('add-habit-modal').classList.remove('hidden');
    document.getElementById('add-habit-modal').classList.add('flex');
}

function hideAddHabitModal() {
    document.getElementById('add-habit-modal').classList.add('hidden');
    document.getElementById('add-habit-modal').classList.remove('flex');
    
    // Reset form
    document.getElementById('habit-name').value = '';
    document.getElementById('habit-description').value = '';
    document.getElementById('habit-points').value = '10';
    document.getElementById('habit-reward').value = '';
}

// CRUD operations
async function addHabit(event) {
    event.preventDefault();
    
    const habitData = {
        user_id: currentUser.id,
        name: document.getElementById('habit-name').value,
        description: document.getElementById('habit-description').value,
        points_per_completion: parseInt(document.getElementById('habit-points').value),
        reward_description: document.getElementById('habit-reward').value
    };
    
    try {
        await apiRequest('/habits.php', {
            method: 'POST',
            body: JSON.stringify(habitData)
        });
        
        showNotification('Hábito criado com sucesso!', 'success');
        hideAddHabitModal();
        await loadHabits();
        updateDashboard();
    } catch (error) {
        showNotification('Erro ao criar hábito: ' + error.message, 'error');
    }
}

async function completeHabit(habitId) {
    try {
        const response = await apiRequest('/completions.php', {
            method: 'POST',
            body: JSON.stringify({
                habit_id: habitId,
                user_id: currentUser.id
            })
        });
        
        showNotification(`Parabéns! +${response.points_earned} pontos!`, 'success');
        await loadUserData();
        await loadHabits();
        await loadBadges();
        updateDashboard();
    } catch (error) {
        showNotification('Erro ao completar hábito: ' + error.message, 'error');
    }
}

async function deleteHabit(habitId) {
    if (!confirm('Tem certeza que deseja excluir este hábito?')) {
        return;
    }
    
    try {
        await apiRequest(`/habits.php?id=${habitId}`, {
            method: 'DELETE'
        });
        
        showNotification('Hábito excluído com sucesso!', 'success');
        await loadHabits();
        updateDashboard();
    } catch (error) {
        showNotification('Erro ao excluir hábito: ' + error.message, 'error');
    }
}

function editHabit(habitId) {
    // TODO: Implement edit functionality
    showNotification('Funcionalidade de edição em desenvolvimento', 'info');
}

function loadProfile() {
    document.getElementById('profile-username').value = currentUser.username;
    document.getElementById('profile-email').value = currentUser.email;
}

async function updateProfile() {
    const profileData = {
        id: currentUser.id,
        username: document.getElementById('profile-username').value,
        email: document.getElementById('profile-email').value
    };
    
    try {
        await apiRequest('/users.php', {
            method: 'PUT',
            body: JSON.stringify(profileData)
        });
        
        currentUser.username = profileData.username;
        currentUser.email = profileData.email;
        updateUserDisplay();
        
        showNotification('Perfil atualizado com sucesso!', 'success');
    } catch (error) {
        showNotification('Erro ao atualizar perfil: ' + error.message, 'error');
    }
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners for search and sort
    const searchInput = document.getElementById('search-input');
    const sortSelect = document.getElementById('sort-select');
    
    if (searchInput) {
        searchInput.addEventListener('input', debounce(loadRanking, 500));
    }
    
    if (sortSelect) {
        sortSelect.addEventListener('change', loadRanking);
    }
});

// Utility functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full ${
        type === 'success' ? 'bg-success-600 text-white' :
        type === 'error' ? 'bg-red-600 text-white' :
        type === 'warning' ? 'bg-warning-600 text-white' :
        'bg-primary-600 text-white'
    }`;
    
    notification.innerHTML = `
        <div class="flex items-center">
            <i data-lucide="${
                type === 'success' ? 'check-circle' :
                type === 'error' ? 'x-circle' :
                type === 'warning' ? 'alert-triangle' :
                'info'
            }" class="h-5 w-5 mr-2"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    lucide.createIcons();
    
    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

