// js/app.js

// Global notification system
function showNotification(message, type = 'info') {
    const area = document.getElementById('notification-area');
    if (!area) return;

    const notif = document.createElement('div');
    notif.className = `notification ${type}`;
    notif.innerHTML = `
        <span class="notification-text">${message}</span>
    `;
    
    area.appendChild(notif);

    // Auto remove after 4 seconds
    setTimeout(() => {
        notif.style.animation = 'slideIn 0.3s ease-in reverse forwards';
        setTimeout(() => {
            if (area.contains(notif)) area.removeChild(notif);
        }, 300);
    }, 4000);
}

// Ensure the user is logged in
async function checkAuth(requiredRole = null) {
    try {
        const response = await fetch('api/auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'check_session' })
        });
        const data = await response.json();
        
        if (data.status !== 'success') {
            window.location.href = 'index.php';
            return null;
        }

        const user = data.user;
        if (requiredRole && user.role !== requiredRole) {
            window.location.href = user.role === 'admin' ? 'admin_dashboard.php' : 'dashboard.php';
            return null;
        }
        
        // Setup navbar user info if available
        const navbarUser = document.getElementById('navbar-user');
        if (navbarUser) {
            navbarUser.textContent = `Hello, ${user.name}`;
        }
        
        return user;
    } catch(err) {
        console.error('Auth Check Error', err);
        window.location.href = 'index.php';
    }
}

// Handle Logout globally
document.addEventListener('DOMContentLoaded', () => {
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            await fetch('api/auth.php?action=logout');
            window.location.href = 'index.php';
        });
    }
});
