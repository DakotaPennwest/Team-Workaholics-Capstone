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
                    <?php if (isset($_GET['error'])): //  ?> <!-- Added error handling specific to user and parent roles --> 
                        <p class="error-message">
                            <?php
                            if ($_GET['error'] == 'invalid_username') {
                                echo 'Invalid username!';
                            } elseif ($_GET['error'] == 'user_invalid_password') {
                                echo 'Invalid password for user!';
                            } elseif ($_GET['error'] == 'parent_invalid_password') {
                                echo 'Invalid password for parent!';
                            } elseif ($_GET['error'] == 'invalid_credentials') {
                                echo 'Invalid username or password!';
                            } elseif ($_GET['error'] == 'db_connection_failed') {
                                echo 'Failed to connect to the database!';
                            }
                            ?>
                        </p>
                    <?php endif; ?>
                    <div class="form-input-container">
                        <form action="login.php" method="post"> <!-- directs to login.php -->
                            <div class="input-wrapper">
                                <label for="usernameInput" class="input-label">Username</label>
                                <input type="text" id="usernameInput" name="username" class="text-input-box" placeholder="Ex: john123">
                                <p id="usernameError" class="error-message"></p>
                            </div>
                            <div class="input-wrapper">
                                <label for="passwordInput" class="input-label">Password</label>
                                <input type="password" id="passwordInput" name="password" class="text-input-box" placeholder="">
                                <p id="passwordError" class="error-message"></p>
                            </div>
                            <div class="form-button-container">
                                <button type="submit" class="form-button">Sign In</button>
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
</body>
</html>
