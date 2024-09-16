<?php
session_start();
$errors = [];
$usersFile = 'users.json';
if(file_exists($usersFile)){
    $users = json_decode(file_get_contents($usersFile), true);
}else{
    $users = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username)) {
        $errors[] = 'Enter your username.';
    }
    if (empty($password)) {
        $errors[] = 'Enter your password.';
    }

    if (empty($errors)) {
        if (isset($users[$username]) && password_verify($password, $users[$username]['password'])) {
            $_SESSION['username'] = $username;
            $_SESSION['is_admin'] = $users[$username]['is_admin'];

            $users[$username]['last_login'] = date('Y-m-d H:i:s');
            file_put_contents($usersFile, json_encode($users));

            header('Location: index.php');
            exit;
        } elseif(isset($users[$username]) && $username === 'admin' && $users[$username]['password'] === $password){
            $_SESSION['username'] = $username;
            $_SESSION['is_admin'] = $users[$username]['is_admin'];

            $users[$username]['last_login'] = date('Y-m-d H:i:s');
            file_put_contents($usersFile, json_encode($users));

            header('Location: index.php');
            exit;
        } else {
            $errors[] = 'Invalid username or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IK-Library | Login</title>
    <link rel="stylesheet" href="styles/main.css">
</head>
<body>
    <header>
        <h1><a href="index.php">IK-Library</a> > Login</h1>
    </header>
    <div id="content">
        <h2>Login</h2>
        <?php if (!empty($errors)): ?>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <form action="login.php" method="POST" novalidate>
            <label for="username">User name:</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($username ?? '') ?>" required><br>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>

            <button type="submit">Login</button>
        </form>
    </div>
    <footer>
        <p>IK-Library | ELTE IK Webprogramming</p>
    </footer>
</body>
</html>
