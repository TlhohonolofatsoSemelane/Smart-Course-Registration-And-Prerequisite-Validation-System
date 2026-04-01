<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Smart Registration</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div id="notification-area"></div>
    <div class="auth-container">
        <div class="auth-header">
            <h1>Create Account</h1>
            <p>Join the student portal</p>
        </div>
        <form id="registerForm">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" class="form-control" required placeholder="John Doe">
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" class="form-control" required placeholder="student@university.edu">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" class="form-control" required minlength="6" placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-primary" id="registerBtn">Register</button>
        </form>
        <div class="auth-footer">
            Already have an account? <a href="index.php">Sign in here</a>
        </div>
    </div>

    <!-- Include Javascript Application Logic -->
    <script src="js/app.js"></script>
    <script>
        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const btn = document.getElementById('registerBtn');

            btn.disabled = true;
            btn.textContent = 'Creating account...';

            try {
                const response = await fetch('api/auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'register', name, email, password })
                });

                const result = await response.json();
                
                if (result.status === 'success') {
                    showNotification(result.message, 'success');
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 1000);
                } else {
                    showNotification(result.message, 'error');
                }
            } catch(err) {
                showNotification('An error occurred during registration', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Register';
            }
        });
    </script>
</body>
</html>
