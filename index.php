<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In</title>
  <link rel="stylesheet" href="styles/layout.css">
  <link rel="stylesheet" href="styles/stylevariables.css">
  <link rel="stylesheet" href="styles/indexlogin.css">
  <link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico">
</head>
<body>
    <!-- Top bar -->
    <div class="top-bar">
        <h1 class="page-title">Login</h1>
    </div>

    <!-- Main screen container -->
    <div class="main-screen-container">
        <img src="./images/rainbow.svg" alt="Rainbow" class="rainbow">
        <img src="./images/Cloud1.svg" alt="Cloud 1" class="cloud cloud1">
        <img src="./images/Cloud2.svg" alt="Cloud 2" class="cloud cloud2">
        <img src="./images/Cloud3.svg" alt="Cloud 3" class="cloud cloud3">

        <div class="form-container">
            <div class="login-form">
                <div class="left-content-container">
                    <h2 class="sign-in-text">Sign in</h2>
                    <p class="subtitle-text">You can sign in with your username</p>
                    <div class="button-container">
                        <p class="create-account-text">Don’t have an account?</p>
                        <a href="accountcreationtype.html" class="create-account-button">Create Account</a>
                    </div>
                </div>

                <div class="right-content-container">
                    <div class="form-input-container">
                        <form id="loginForm" method="post">
                            <div class="input-wrapper">
                                <label for="usernameInput" class="input-label">Username</label>
                                <input type="text" id="usernameInput" name="username" class="text-input-box" placeholder="Ex: john123" required>
                                <p id="usernameError" class="error-message"></p>
                            </div>
                            <div class="input-wrapper">
                                <label for="passwordInput" class="input-label">Password</label>
                                <input type="password" id="passwordInput" name="password" class="text-input-box" placeholder="" required>
                                <p id="passwordError" class="error-message"></p>
                            </div>
                            <div class="form-button-container">
                                <button type="button" class="form-button" onclick="submitForm()">Sign In</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="waves-container">
        <img src="./images/waveBack.svg" alt="Back Wave" class="wave back-wave">
        <img src="./images/waveMiddle.svg" alt="Middle Wave" class="wave middle-wave">
        <img src="./images/waveFront.svg" alt="Front Wave" class="wave front-wave">
    </div>

    <script>
        function submitForm() {
            // Clear previous error messages
            document.getElementById('usernameError').innerText = '';
            document.getElementById('passwordError').innerText = '';

            // Get form values
            const username = document.getElementById('usernameInput').value;
            const password = document.getElementById('passwordInput').value;

            // Basic validation
            if (username.trim() === '') {
                document.getElementById('usernameError').innerText = 'Please enter your username.';
                return;
            }

            if (password.trim() === '') {
                document.getElementById('passwordError').innerText = 'Please enter your password.';
                return;
            }

            // Create FormData object
            const formData = new FormData();
            formData.append('username', username);
            formData.append('password', password);

            // Send form data using Fetch API
            fetch('login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    if (data.error === 'invalid_username') {
                        document.getElementById('usernameError').innerText = 'Invalid username!';
                    } else if (data.error === 'user_invalid_password') {
                        document.getElementById('passwordError').innerText = 'Invalid password for user!';
                    } else if (data.error === 'parent_invalid_password') {
                        document.getElementById('passwordError').innerText = 'Invalid password for parent!';
                    } else {
                        document.getElementById('passwordError').innerText = 'Invalid username or password!';
                    }
                }
            })
            .catch(error => {
                document.getElementById('passwordError').innerText = 'An error occurred. Please try again.';
            });
        }
    </script>
</body>
</html>
