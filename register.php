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
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($username)) {
        $errors[] = 'Enter your name.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email.';
    }
    if (empty($password)) {
        $errors[] = 'Enter a password.';
    } elseif (strlen($password) < 5) {
        $errors[] = 'Password must be at least 5 characters long.';
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain both uppercase and lowercase letters.';
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords don't match.";
    }

    if (isset($users[$username])) {
        $errors[] = 'This username is already used.';
    }

    if (empty($errors)) {
        $newUser = [
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'last_login' => null,
            'is_admin' => false,
            'read_books' => [],
            'reviews' => []
        ];
        $users[$username] = $newUser;
        file_put_contents($usersFile, json_encode($users));

        $_SESSION['username'] = $username;
        $_SESSION['is_admin'] = false;

        header('Location: index.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IK-Library | Register</title>
    <link rel="stylesheet" href="styles/main.css">
</head>
<body>
    <header>
        <h1><a href="index.php">IK-Library</a> > Register</h1>
    </header>
    <div id="content">
        <h2>Registration</h2>
        <?php if (!empty($errors)): ?>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <form action="register.php" method="POST" novalidate>
            <label for="username">User name:</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($username ?? '') ?>" required><br>

            <label for="email">Email Address:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required><br>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>

            <label for="confirm_password">Password Confirm:</label>
            <input type="password" id="confirm_password" name="confirm_password" required><br>

            <button type="submit">Register</button>
        </form>
    </div>
    <footer>
        <p>IK-Library | ELTE IK Webprogramming</p>
    </footer>
</body>
</html>

