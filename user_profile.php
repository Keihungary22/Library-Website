<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];
$usersJson = file_get_contents('users.json');
$users = json_decode($usersJson, true);

$booksJson = file_get_contents('data.json');
$books = json_decode($booksJson, true);
$user = $users[$username];

function sortBooksByReadCount($readBooks) {
    usort($readBooks, function($a, $b) {
        return $b['count'] - $a['count'];
    });
    return $readBooks;
}

$readBooks = [];
foreach ($user['read_books'] as $bookId => $count) {
    if (isset($books[$bookId])) {
        $readBooks[] = [
            'bookId' => $bookId,
            'count' => $count,
            'title' => $books[$bookId]['title'],
            'author' => $books[$bookId]['author']
        ];
    }
}

$readBooks = sortBooksByReadCount($readBooks);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IK-Library | User Profile</title>
    <link rel="stylesheet" href="styles/main.css">
    <style>
#profile a {
    color: black;
    text-decoration: none; 
}

#profile a:hover {
    color: black;
    text-decoration: underline;
}

#profile a:visited {
    color: black;
    text-decoration: none; 
}
    </style>
</head>

<body>
    <header>
        <h1><a href="index.php">IK-Library</a> > User Profile</h1>
        <a href="logout.php">Logout</a>
    </header>
    <div id="profile">
        <h2>User Profile</h2>
        <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Last Login:</strong> <?= htmlspecialchars($user['last_login']) ?></p>
        <h3>Read Books</h3>
        <ul>
            <?php foreach ($readBooks as $book): ?>
                <li>
                    <a href="details.php?id=<?= htmlspecialchars($book['bookId']) ?>">
                        <?= htmlspecialchars($book['title']) ?> by <?= htmlspecialchars($book['author']) ?> (Read <?= htmlspecialchars($book['count']) ?> times)
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <h3>Reviews</h3>
        <ul>
            <?php foreach ($user['reviews'] as $review): ?>
                <?php if (isset($books[$review['book_id']])): ?>
                    <li>
                        <strong><?= htmlspecialchars($books[$review['book_id']]['title']) ?>:</strong>
                        <?= htmlspecialchars($review['rating']) ?>/5 -
                        <?= htmlspecialchars($review['review']) ?>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>
    <footer>
        <p>IK-Library | ELTE IK Webprogramming</p>
    </footer>
</body>

</html>
