<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Registration</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div id="notification-area"></div>
    <div class="auth-container">
        <div class="auth-header">
            <h1>Welcome Back</h1>
            <p>Sign in to your account</p>
        </div>
        <form id="loginForm">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" class="form-control" required placeholder="student@university.edu">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" class="form-control" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-primary" id="loginBtn">Sign In</button>
        </form>
        <div class="auth-footer">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>

    <!-- Include Javascript Application Logic -->
    <script src="js/app.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const btn = document.getElementById('loginBtn');

            btn.disabled = true;
            btn.textContent = 'Signing in...';

            try {
                const response = await fetch('api/auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'login', email, password })
                });

                const result = await response.json();
                
                if (result.status === 'success') {
                    showNotification(result.message, 'success');
                    setTimeout(() => {
                        if (result.user.role === 'admin') {
                            window.location.href = 'admin_dashboard.php';
                        } else {
                            window.location.href = 'dashboard.php';
                        }
                    }, 1000);
                } else {
                    showNotification(result.message, 'error');
                }
            } catch(err) {
                showNotification('An error occurred during sign in', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Sign In';
            }
        });
    </script>
</body>
</html>
