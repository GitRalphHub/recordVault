<?php
session_start();

// Already logged in → go to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // BUG FIX: Use password_verify() instead of plain-text comparison
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: index.php");
                exit();
            } else {
                $error = 'Invalid username or password.';
            }
        } else {
            $error = 'Invalid username or password.';
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – RecordVault</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-card {
            background: #fff;
            padding: 40px 36px 32px;
            border-radius: 12px;
            width: 340px;
            box-shadow: 0 4px 24px rgba(0,0,0,.10);
        }
        .login-card .brand {
            font-size: 22px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 4px;
        }
        .login-card .subtitle {
            font-size: 13px;
            color: #888;
            margin-bottom: 24px;
        }
        .login-card label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #444;
            margin-bottom: 5px;
        }
        .login-card input[type="text"],
        .login-card input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 16px;
            border: 1px solid #ddd;
            border-radius: 7px;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color .2s;
        }
        .login-card input:focus {
            outline: none;
            border-color: #4f46e5;
        }
        .login-card button {
            width: 100%;
            padding: 11px;
            background: #4f46e5;
            color: #fff;
            border: none;
            border-radius: 7px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s;
        }
        .login-card button:hover { background: #3730a3; }
        .error-msg {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            padding: 9px 12px;
            border-radius: 7px;
            margin-bottom: 16px;
            font-size: 13px;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="brand"> RecordVault</div>
    <div class="subtitle">Sign in to your account</div>

    <?php if ($error): ?>
        <div class="error-msg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label for="username">Username</label>
        <input type="text" id="username" name="username"
               placeholder="Enter username"
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>

        <label for="password">Password</label>
        <input type="password" id="password" name="password"
               placeholder="Enter password" required>

        <button type="submit">Login</button>
    </form>
</div>

</body>
</html>
