<?php
session_start();

if (!isset($_SESSION['username']) || !$_SESSION['is_admin']) {
    header('Location: index.php');
    exit;
}

$booksFile = 'data.json';
$booksJson = file_get_contents($booksFile);
$books = json_decode($booksJson, true);

$bookId = $_GET['id'] ?? null;
if ($bookId === null || !isset($books[$bookId])) {
    header('Location: index.php');
    exit;
}

$book = $books[$bookId];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $description = trim($_POST['description']);
    $cover_image = trim($_POST['cover_image']);
    $year = trim($_POST['year']);
    $genre = trim($_POST['genre']);
    $average_rating = floatval($_POST['average_rating']);

    if (empty($title)) {
        $errors[] = 'Title is required.';
    }
    if (empty($author)) {
        $errors[] = 'Author is required.';
    }
    if (empty($description)) {
        $errors[] = 'Description is required.';
    }
    if (empty($cover_image)) {
        $errors[] = 'Cover image is required.';
    }
    if (empty($year) || !is_numeric($year)) {
        $errors[] = 'Valid year is required.';
    }
    if (empty($genre)) {
        $errors[] = 'Genre is required.';
    }
    if ($average_rating < 0 || $average_rating > 5) {
        $errors[] = 'Average rating must be between 0 and 5.';
    }

    if (empty($errors)) {
        $books[$bookId] = [
            'title' => $title,
            'author' => $author,
            'description' => $description,
            'cover_image' => $cover_image,
            'year' => $year,
            'genre' => $genre,
            'average_rating' => $average_rating,
            'ratings' => $book['ratings']
        ];
        file_put_contents($booksFile, json_encode($books));

        header('Location: details.php?id=' . urlencode($bookId));
        exit;
    }
}

$genres = ['fiction', 'novel', 'mystery', 'fantasy', 'science', 'history', 'fairy tale', 'other'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book | IK-Library</title>
    <link rel="stylesheet" href="styles/main.css">
</head>
<body>
    <header>
        <h1><a href="index.php">IK-Library</a> > Edit Book</h1>
        <?php if(isset($_SESSION['username'])): ?>
            <a href="user_profile.php">User Profile</a>
        <?php else: ?>
            <a href="login.php">Login</a> | <a href="register.php">Register</a>
        <?php endif; ?>
    </header>
    <div id="content">
        <h2>Edit Book</h2>
        <?php if (!empty($errors)): ?>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <form action="edit.php?id=<?= htmlspecialchars($bookId) ?>" method="POST">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($book['title']) ?>" required><br>

            <label for="author">Author:</label>
            <input type="text" id="author" name="author" value="<?= htmlspecialchars($book['author']) ?>" required><br>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required><?= htmlspecialchars($book['description']) ?></textarea><br>

            <label for="cover_image">Cover Image URL:</label>
            <input type="text" id="cover_image" name="cover_image" value="<?= htmlspecialchars($book['cover_image']) ?>" required><br>

            <label for="year">Year:</label>
            <input type="text" id="year" name="year" value="<?= htmlspecialchars($book['year']) ?>" required><br>

            <label for="genre">Genre:</label>
            <select id="genre" name="genre" required>
                <?php foreach ($genres as $g): ?>
                    <option value="<?= htmlspecialchars($g) ?>" <?= $book['genre'] === $g ? 'selected' : '' ?>><?= htmlspecialchars($g) ?></option>
                <?php endforeach; ?>
            </select><br>

            <label for="average_rating">Average Rating:</label>
            <input type="number" id="average_rating" name="average_rating" value="<?= htmlspecialchars($book['average_rating']) ?>" min="0" max="5" step="0.1" required><br>

            <button type="submit">Save Changes</button>
        </form>
    </div>
    <footer>
        <p>IK-Library | ELTE IK Webprogramming</p>
    </footer>
</body>
</html>

