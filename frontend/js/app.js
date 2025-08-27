// Configuration
const API_BASE_URL = 'http://localhost:8000/api';
let currentUser = {
    id: 1,
    username: 'Usuário Demo',
    email: 'demo@healthyhabits.com',
    total_points: 0
};

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
        // Skip authentication and go directly to main app
        showMainApp();
        await loadUserData();
        await loadHabits();
        await loadBadges();
        showSection('dashboard');
        updateDashboard();
    } catch (error) {
        console.error('Error initializing app:', error);
        showMainApp(); // Still show main app even if there's an error
    }
}

// Authentication functions - Simplified for demo mode
async function checkAuthentication() {
    // Return authenticated by default for demo mode
    return { 
        authenticated: true, 
        user: currentUser 
    };
}

async function login(event) {
    event.preventDefault();
    
    // For demo mode, just show success and continue
    showNotification('Modo demo ativado!', 'success');
    showMainApp();
    await loadUserData();
    await loadHabits();
    await loadBadges();
    showSection('dashboard');
    updateDashboard();
}

async function register(event) {
    event.preventDefault();
    
    // For demo mode, just show success and continue
    showNotification('Modo demo ativado!', 'success');
    showMainApp();
    await loadUserData();
    await loadHabits();
    await loadBadges();
    showSection('dashboard');
    updateDashboard();
}

async function logout() {
    // For demo mode, just show notification
    showNotification('Modo demo - logout não disponível', 'info');
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

// API Functions - Demo mode with mock data
async function apiRequest(endpoint, options = {}) {
    // For demo mode, return mock data instead of making API calls
    console.log('Demo mode: Mocking API request to', endpoint);
    
    // Simulate API delay
    await new Promise(resolve => setTimeout(resolve, 300));
    
    // Return mock data based on endpoint
    if (endpoint.includes('/users.php')) {
        return {
            id: currentUser.id,
            username: currentUser.username,
            email: currentUser.email,
            total_points: currentUser.total_points
        };
    } else if (endpoint.includes('/habits.php')) {
        return getMockHabits();
    } else if (endpoint.includes('/badges.php')) {
        return getMockBadges();
    } else if (endpoint.includes('/ranking.php')) {
        return {
            ranking: getMockRanking()
        };
    } else if (endpoint.includes('/completions.php') && options.method === 'POST') {
        // Simulate completion
        const points = Math.floor(Math.random() * 20) + 10;
        currentUser.total_points += points;
        return {
            points_earned: points,
            message: 'Hábito completado com sucesso!'
        };
    }
    
    return { success: true };
}

// Mock data functions
function getMockHabits() {
    return [
        {
            id: 1,
            user_id: currentUser.id,
            name: 'Beber 2L de água',
            description: 'Manter-se hidratado bebendo pelo menos 2 litros de água por dia',
            points_per_completion: 50,
            reward_description: 'Pele mais saudável e energia renovada',
            total_completions: 2,
            duration: 5,
            current_completions: 2,
            is_completed: false
        },
        {
            id: 2,
            user_id: currentUser.id,
            name: 'Exercitar-se 30min',
            description: 'Fazer pelo menos 30 minutos de exercício físico',
            points_per_completion: 50,
            reward_description: 'Endorfina e bem-estar',
            total_completions: 1,
            duration: 3,
            current_completions: 1,
            is_completed: false
        },
        {
            id: 3,
            user_id: currentUser.id,
            name: 'Meditar 10min',
            description: 'Praticar meditação por 10 minutos',
            points_per_completion: 50,
            reward_description: 'Paz mental e clareza',
            total_completions: 0,
            duration: 'indefinido',
            current_completions: 0,
            is_completed: false
        },
        {
            id: 4,
            user_id: currentUser.id,
            name: 'Caminhar 10.000 passos',
            description: 'Atingir a meta de 10.000 passos por dia',
            points_per_completion: 50,
            reward_description: 'Saúde cardiovascular',
            total_completions: 0,
            duration: 7,
            current_completions: 0,
            is_completed: false
        }
    ];
}

function getMockBadges() {
    return [
        {
            id: 1,
            name: 'Iniciante',
            description: 'Primeiros passos na jornada saudável',
            points_threshold: 0
        },
        {
            id: 2,
            name: 'Dedicado',
            description: 'Alcançou 50 pontos',
            points_threshold: 50
        },
        {
            id: 3,
            name: 'Persistente',
            description: 'Alcançou 100 pontos',
            points_threshold: 100
        }
    ];
}

function getMockRanking() {
    return [
        {
            position: 1,
            username: 'João Silva',
            points: 850,
            badges_count: 5,
            completions_count: 45
        },
        {
            position: 2,
            username: 'Maria Santos',
            points: 720,
            badges_count: 4,
            completions_count: 38
        },
        {
            position: 3,
            username: 'Pedro Costa',
            points: 650,
            badges_count: 3,
            completions_count: 32
        },
        {
            position: 4,
            username: currentUser.username,
            points: currentUser.total_points,
            badges_count: 2,
            completions_count: 15
        }
    ];
}

// Data loading functions - Demo mode
async function loadUserData() {
    try {
        const userData = await apiRequest(`/users.php?id=${currentUser.id}`);
        appState.userStats = userData;
        document.getElementById('user-points').textContent = userData.total_points || 0;
    } catch (error) {
        console.error('Error loading user data:', error);
        // Fallback to current user data
        document.getElementById('user-points').textContent = currentUser.total_points || 0;
    }
}

async function loadHabits() {
    try {
        const habits = await apiRequest(`/habits.php?user_id=${currentUser.id}`);
        appState.habits = habits;
        renderHabits();
    } catch (error) {
        console.error('Error loading habits:', error);
        // Use mock data as fallback
        appState.habits = getMockHabits();
        renderHabits();
    }
}

async function loadBadges() {
    try {
        const badges = await apiRequest(`/badges.php?user_id=${currentUser.id}`);
        appState.badges = badges;
        renderBadges();
    } catch (error) {
        console.error('Error loading badges:', error);
        // Use mock data as fallback
        appState.badges = getMockBadges();
        renderBadges();
    }
}

async function loadRanking() {
    try {
        const searchTerm = document.getElementById('search-input').value;
        const sortBy = document.getElementById('sort-select').value;
        
        // Get mock ranking data
        const rankingData = await apiRequest(`/ranking.php`);
        appState.ranking = rankingData.ranking;
        
        // Apply search filter if provided
        if (searchTerm) {
            appState.ranking = appState.ranking.filter(user => 
                user.username.toLowerCase().includes(searchTerm.toLowerCase())
            );
        }
        
        // Apply sorting
        appState.ranking.sort((a, b) => {
            if (sortBy === 'points') {
                return b.points - a.points;
            } else if (sortBy === 'badges') {
                return b.badges_count - a.badges_count;
            } else {
                return b.completions_count - a.completions_count;
            }
        });
        
        renderRanking();
    } catch (error) {
        console.error('Error loading ranking:', error);
        // Use mock data as fallback
        appState.ranking = getMockRanking();
        renderRanking();
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
    
    habitsGrid.innerHTML = appState.habits.map(habit => {
        const isCompleted = habit.is_completed;
        const durationText = habit.duration === 'indefinido' ? 'Indefinido' : `${habit.duration} vezes`;
        const progressText = habit.duration === 'indefinido' ? 
            `${habit.current_completions} vezes completadas` : 
            `${habit.current_completions}/${habit.duration} vezes`;
        
        return `
            <div class="bg-white rounded-xl shadow-md p-6 card-hover ${isCompleted ? 'opacity-75' : ''}">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">${habit.name}</h3>
                    <div class="flex space-x-2">
                        ${!isCompleted ? `
                            <button onclick="editHabit(${habit.id})" class="text-gray-400 hover:text-gray-600">
                                <i data-lucide="edit-2" class="h-4 w-4"></i>
                            </button>
                        ` : ''}
                        <button onclick="deleteHabit(${habit.id})" class="text-gray-400 hover:text-red-600">
                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                        </button>
                    </div>
                </div>
                
                <p class="text-gray-600 text-sm mb-4">${habit.description || 'Sem descrição'}</p>
                
                <!-- Duração e Progresso -->
                <div class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i data-lucide="clock" class="h-4 w-4 text-blue-600 mr-2"></i>
                            <span class="text-sm text-blue-800 font-medium">Duração:</span>
                            <span class="text-sm text-blue-700 ml-1">${durationText}</span>
                        </div>
                        <div class="flex items-center">
                            <i data-lucide="bar-chart-3" class="h-4 w-4 text-blue-600 mr-2"></i>
                            <span class="text-sm text-blue-800 font-medium">Progresso:</span>
                            <span class="text-sm text-blue-700 ml-1">${progressText}</span>
                        </div>
                    </div>
                    ${habit.duration !== 'indefinido' ? `
                        <div class="mt-2">
                            <div class="w-full bg-blue-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: ${(habit.current_completions / habit.duration) * 100}%"></div>
                            </div>
                        </div>
                    ` : ''}
                </div>
                
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
                
                ${isCompleted ? `
                    <div class="w-full bg-green-100 text-green-800 py-2 px-4 rounded-lg flex items-center justify-center">
                        <i data-lucide="check-circle" class="h-4 w-4 mr-2"></i>
                        Tarefa Concluída!
                    </div>
                ` : `
                    <button onclick="completeHabit(${habit.id})" class="w-full bg-success-600 hover:bg-success-700 text-white py-2 px-4 rounded-lg transition-colors flex items-center justify-center">
                        <i data-lucide="check" class="h-4 w-4 mr-2"></i>
                        Marcar como Concluído
                    </button>
                `}
            </div>
        `;
    }).join('');
    
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
    document.getElementById('habit-points').value = '50';
    document.getElementById('habit-duration').value = '';
    document.getElementById('habit-reward').value = '';
}

// CRUD operations - Demo mode
async function addHabit(event) {
    event.preventDefault();
    
    const habitData = {
        user_id: currentUser.id,
        name: document.getElementById('habit-name').value,
        description: document.getElementById('habit-description').value,
        points_per_completion: 50, // Fixed at 50 points
        reward_description: document.getElementById('habit-reward').value,
        duration: document.getElementById('habit-duration').value
    };
    
    // Add to mock data
    const newHabit = {
        id: Date.now(), // Use timestamp as ID
        ...habitData,
        total_completions: 0,
        current_completions: 0,
        is_completed: false
    };
    
    appState.habits.push(newHabit);
    
    showNotification('Hábito criado com sucesso! (Modo demo)', 'success');
    hideAddHabitModal();
    renderHabits();
    updateDashboard();
}

async function completeHabit(habitId) {
    // Find the habit and update its completion count
    const habit = appState.habits.find(h => h.id === habitId);
    if (habit && !habit.is_completed) {
        habit.current_completions++;
        habit.total_completions++;
        currentUser.total_points += habit.points_per_completion;
        
        // Check if habit is completed based on duration
        if (habit.duration !== 'indefinido' && habit.current_completions >= habit.duration) {
            habit.is_completed = true;
            showNotification(`🎉 Tarefa "${habit.name}" concluída! +${habit.points_per_completion} pontos! (Modo demo)`, 'success');
            // Schedule auto-cleanup after 24 hours
            scheduleHabitCleanup(habit.id);
        } else {
            showNotification(`Parabéns! +${habit.points_per_completion} pontos! (Modo demo)`, 'success');
        }
        
        renderHabits();
        updateDashboard();
    }
}

async function deleteHabit(habitId) {
    const habit = appState.habits.find(h => h.id === habitId);
    const message = habit && habit.is_completed ? 
        'Tem certeza que deseja excluir esta tarefa concluída?' : 
        'Tem certeza que deseja excluir este hábito?';
    
    if (!confirm(message)) {
        return;
    }
    
    // Remove from mock data
    appState.habits = appState.habits.filter(h => h.id !== habitId);
    
    showNotification('Hábito excluído com sucesso! (Modo demo)', 'success');
    renderHabits();
    updateDashboard();
}

function editHabit(habitId) {
    const habit = appState.habits.find(h => h.id === habitId);
    if (habit) {
        // Populate the edit form
        document.getElementById('edit-habit-id').value = habit.id;
        document.getElementById('edit-habit-name').value = habit.name;
        document.getElementById('edit-habit-description').value = habit.description || '';
        document.getElementById('edit-habit-reward').value = habit.reward_description || '';
        document.getElementById('edit-habit-duration').value = habit.duration;
        
        // Show the edit modal
        document.getElementById('edit-habit-modal').classList.remove('hidden');
        document.getElementById('edit-habit-modal').classList.add('flex');
    }
}

async function updateHabit(event) {
    event.preventDefault();
    
    const habitId = parseInt(document.getElementById('edit-habit-id').value);
    const habit = appState.habits.find(h => h.id === habitId);
    
    if (habit) {
        // Update habit data
        habit.name = document.getElementById('edit-habit-name').value;
        habit.description = document.getElementById('edit-habit-description').value;
        habit.reward_description = document.getElementById('edit-habit-reward').value;
        habit.duration = document.getElementById('edit-habit-duration').value;
        
        showNotification('Hábito atualizado com sucesso! (Modo demo)', 'success');
        hideEditHabitModal();
        renderHabits();
        updateDashboard();
    }
}

function hideEditHabitModal() {
    document.getElementById('edit-habit-modal').classList.add('hidden');
    document.getElementById('edit-habit-modal').classList.remove('flex');
    
    // Reset form
    document.getElementById('edit-habit-id').value = '';
    document.getElementById('edit-habit-name').value = '';
    document.getElementById('edit-habit-description').value = '';
    document.getElementById('edit-habit-reward').value = '';
    document.getElementById('edit-habit-duration').value = '';
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
    
    // Update current user data in demo mode
    currentUser.username = profileData.username;
    currentUser.email = profileData.email;
    updateUserDisplay();
    
    showNotification('Perfil atualizado com sucesso! (Modo demo)', 'success');
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

// Auto-cleanup completed habits after 24 hours
function scheduleHabitCleanup(habitId) {
    setTimeout(() => {
        const habit = appState.habits.find(h => h.id === habitId);
        if (habit && habit.is_completed) {
            appState.habits = appState.habits.filter(h => h.id !== habitId);
            renderHabits();
            updateDashboard();
            showNotification(`Tarefa "${habit.name}" removida automaticamente após 24 horas`, 'info');
        }
    }, 24 * 60 * 60 * 1000); // 24 hours in milliseconds
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

