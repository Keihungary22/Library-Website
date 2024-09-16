<?php
session_start();

$booksJson = file_get_contents('data.json');
$books = json_decode($booksJson, true);
$usersJson = file_get_contents('users.json');
$users = json_decode($usersJson, true);

function isLoggedIn(){
    return isset($_SESSION['username']);
}

function getUsername(){
    return $_SESSION['username'] ?? '';
}

function isAdmin(){
    return $_SESSION['is_admin'] ?? false;
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

$filterGenre = $_GET['genre'] ?? '';
if (!empty($filterGenre)) {
    $books = array_filter($books, function($book) use ($filterGenre) {
        return $book['genre'] === $filterGenre;
    });
}

function getGenres($books) {
    $genres = array_unique(array_column($books, 'genre'));
    sort($genres);
    return $genres;
}

$genres = getGenres($books);

$averageRatings = array_column($books, 'average_rating');
array_multisort($averageRatings, SORT_DESC, $books);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IK-Library | Home</title>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/cards.css">
</head>

<body>
    <header>
        <h1><a href="index.php">IK-Library</a> > Home</h1>
        <?php if(isLoggedIn()): ?>
            <span>Welcome, <a href="user_profile.php"><?= htmlspecialchars(getUsername()) ?></a> !</span>
            <span>  |  </span>
            <a href="user_profile.php">User Profile</a>
            <?php if(isAdmin()): ?>
                <span>  |  </span>
                <a href="add_book.php">Add Book Here!</a>
            <?php endif; ?>
        <?php else: ?>
            <a href="login.php">Login</a> | <a href="register.php">Register</a>
        <?php endif; ?>
    </header>
    <div id="content">
        <form method="GET" action="index.php">
            <label for="genre">Filter by genre:</label>
            <select id="genre" name="genre" onchange="this.form.submit()">
                <option value="">All genres</option>
                <?php foreach($genres as $genre): ?>
                    <option value="<?= htmlspecialchars($genre) ?>" <?= $filterGenre == $genre ? 'selected' : '' ?>><?= htmlspecialchars($genre) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
        <div id="card-list">
            <?php foreach($books as $id => $book): ?>
                <div class="book-card">
                    <div class="image">
                        <img src="<?= htmlspecialchars($book['cover_image']) ?>" alt="<?= htmlspecialchars($book['title']) ?>" onerror="this.onerror=null;this.src='assets/default.png';">
                    </div>
                    <div class="details">
                        <h2><a href="details.php?id=<?= htmlspecialchars($id) ?>"><?= htmlspecialchars($book['title']) ?> - <?= htmlspecialchars($book['author']) ?></a></h2>
                        <p>Average Rating: <?= htmlspecialchars(number_format($book['average_rating'], 2)) ?></p>
                    </div>
                    <?php if(isAdmin()): ?>
                        <div class="edit">
                            <a href="edit.php?id=<?= htmlspecialchars($id) ?>">Edit</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <footer>
        <p>IK-Library | ELTE IK Webprogramming</p>
    </footer>
</body>
</html>
