// Main Application File

// Check authentication on load
async function checkAuth() {
    try {
        const response = await api.get('/auth/user');
        if (response.success && response.user) {
            store.setState({
                user: response.user,
                isAuthenticated: true
            });
            return true;
        }
    } catch (error) {
        console.log('Not authenticated');
    }
    return false;
}

// Register all routes
router.register('/', Dashboard);
router.register('/dashboard', Dashboard);
router.register('/login', Login);
router.register('/students', StudentsList);

// Initialize app
(async function initApp() {
    const isAuthenticated = await checkAuth();

    // Redirect to login if not authenticated
    if (!isAuthenticated && !window.location.hash.includes('login')) {
        router.navigate('/login');
    } else if (isAuthenticated && window.location.hash.includes('login')) {
        router.navigate('/dashboard');
    } else {
        router.handleRoute();
    }
})();

// Handle navigation clicks
document.addEventListener('click', (e) => {
    if (e.target.matches('a[href^="#"]')) {
        e.preventDefault();
        const path = e.target.getAttribute('href').substring(1);
        router.navigate(path);
    }
});
