<?php
session_start();

$booksJson = file_get_contents('data.json');
$books = json_decode($booksJson, true);

$usersJson = file_get_contents('users.json');
$users = json_decode($usersJson, true);

$bookId = $_GET['id'] ?? null;
if ($bookId === null || !isset($books[$bookId])) {
    header('Location: index.php');
    exit;
}
$book = $books[$bookId];

function isLoggedIn() {
    return isset($_SESSION['username']);
}

function getUsername() {
    return $_SESSION['username'] ?? '';
}

function getUserReadCount($bookId) {
    global $users;
    if (!isLoggedIn()) {
        return 0;
    }
    $username = getUsername();
    $readBooks = $users[$username]['read_books'] ?? [];
    return $readBooks[$bookId] ?? 0;
}

function incrementReadCount($bookId) {
    global $users;
    if (!isLoggedIn()) {
        return;
    }
    $username = getUsername();
    if (!isset($users[$username]['read_books'])) {
        $users[$username]['read_books'] = [];
    }
    if (!isset($users[$username]['read_books'][$bookId])) {
        $users[$username]['read_books'][$bookId] = 0;
    }
    $users[$username]['read_books'][$bookId]++;
    file_put_contents('users.json', json_encode($users, JSON_PRETTY_PRINT));
}

if (isset($_POST['increment_read_count'])) {
    incrementReadCount($bookId);
    header("Location: details.php?id=" . urlencode($bookId));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IK-Library | Book Details</title>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/details.css">
</head>

<body>
    <header>
        <h1><a href="index.php">IK-Library</a> > Book Details</h1>
        <?php if (isLoggedIn()): ?>
            <a href="user_profile.php">User Profile</a>
        <?php else: ?>
            <a href="login.php">Login</a> | <a href="register.php">Register</a>
        <?php endif; ?>
    </header>
    <div id="details">
        <div class="image">
            <img src="<?= htmlspecialchars($book['cover_image']) ?>" alt="<?= htmlspecialchars($book['title']) ?>" onerror="this.onerror=null;this.src='assets/default.png';">
        </div>
        <div class="info">
            <h2><?= htmlspecialchars($book['title']) ?></h2>
            <p><strong>Author:</strong> <?= htmlspecialchars($book['author']) ?></p>
            <p><strong>Year:</strong> <?= htmlspecialchars($book['year']) ?></p>
            <p><strong>Genre:</strong> <?= htmlspecialchars($book['genre']) ?></p>
            <p><strong>Description:</strong> <?= htmlspecialchars($book['description']) ?></p>
            <p><strong>Average Rating:</strong> <?= htmlspecialchars($book['average_rating']) ?></p>
            <?php if (isLoggedIn()): ?>
                <p><strong>How many times you read:</strong> <?= htmlspecialchars(getUserReadCount($bookId)) ?></p>
            <?php else: ?>
                <p><strong>How many times you read:</strong> You haven't logged in or signed in.</p>
            <?php endif; ?>
            <h3>Reviews</h3>
            <ul>
                <?php foreach ($book['ratings'] as $rating): ?>
                    <li>
                        <strong><?= htmlspecialchars($rating['user']) ?>:</strong>
                        <?= htmlspecialchars($rating['rating']) ?>/5 -
                        <?= htmlspecialchars($rating['review']) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php if (isLoggedIn()): ?>
                <button onclick="location.href='rate_book.php?id=<?= htmlspecialchars($bookId) ?>'">Rate this book</button>
                <form method="POST" action="details.php?id=<?= htmlspecialchars($bookId) ?>" style="display:inline;">
                    <input type="hidden" name="increment_read_count" value="1">
                    <button type="submit">Read Count</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <footer>
        <p>IK-Library | ELTE IK Webprogramming</p>
    </footer>
</body>
</html>
