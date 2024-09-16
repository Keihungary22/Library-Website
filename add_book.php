<?php
session_start();

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: index.php');
    exit;
}

$errors = [];
$dataFile = 'data.json';

if (file_exists($dataFile)) {
    $books = json_decode(file_get_contents($dataFile), true);
} else {
    $books = [];
}

$title = $author = $description = $cover_image = $year = $genre = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $cover_image = trim($_POST['cover_image'] ?? '');
    $year = trim($_POST['year'] ?? '');
    $genre = trim($_POST['genre'] ?? '');

    if (empty($title)) {
        $errors[] = 'Enter the book title.';
    }
    if (empty($author)) {
        $errors[] = 'Enter the author name.';
    }
    if (empty($description)) {
        $errors[] = 'Enter a description.';
    }
    if (empty($cover_image)) {
        $errors[] = 'Enter the cover image URL.';
    }
    if (empty($year) || !is_numeric($year)) {
        $errors[] = 'Enter a valid year.';
    }
    if (empty($genre)) {
        $errors[] = 'Select the genre.';
    }

    if (empty($errors)) {
        $newBookId = 'book' . (count($books));
        $newBook = [
            'title' => $title,
            'author' => $author,
            'description' => $description,
            'cover_image' => $cover_image,
            'year' => (int)$year,
            'genre' => $genre,
            'average_rating' => 0,
            'ratings' => []
        ];
        $books[$newBookId] = $newBook;
        file_put_contents($dataFile, json_encode($books));

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
    <title>IK-Library | Add Book</title>
    <link rel="stylesheet" href="styles/main.css">
    <style>
        .image-placeholder {
            width: 336px;
            height: 510px;
            background-color: #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .image-placeholder img {
            max-width: 100%;
            max-height: 100%;
        }
    </style>
</head>

<body>
    <header>
        <h1><a href="index.php">IK-Library</a> > Add Book</h1>
        <a href="index.php">Go Back to Home</a>
    </header>
    <div id="content">
        <h2>Add New Book</h2>
        <?php if (!empty($errors)): ?>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <form action="add_book.php" method="POST" novalidate>
            <label for="title">Book Title:</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($title) ?>" required><br>

            <label for="author">Author:</label>
            <input type="text" id="author" name="author" value="<?= htmlspecialchars($author) ?>" required><br>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required><?= htmlspecialchars($description) ?></textarea><br>

            <label for="cover_image">Cover Image URL:</label>
            <input type="text" id="cover_image" name="cover_image" value="<?= htmlspecialchars($cover_image) ?>" required><br>

            <label for="year">Year:</label>
            <input type="number" id="year" name="year" value="<?= htmlspecialchars($year) ?>" required><br>

            <label for="genre">Genre:</label>
            <select id="genre" name="genre" required>
                <option value="" disabled <?= $genre === '' ? 'selected' : '' ?>>Select genre</option>
                <option value="fiction" <?= $genre == 'fiction' ? 'selected' : '' ?>>Fiction</option>
                <option value="novel" <?= $genre == 'novel' ? 'selected' : '' ?>>Novel</option>
                <option value="mystery" <?= $genre == 'mystery' ? 'selected' : '' ?>>Mystery</option>
                <option value="fantasy" <?= $genre == 'fantasy' ? 'selected' : '' ?>>Fantasy</option>
                <option value="science" <?= $genre == 'science' ? 'selected' : '' ?>>Science</option>
                <option value="history" <?= $genre == 'history' ? 'selected' : '' ?>>History</option>
                <option value="fairy tale" <?= $genre == 'fairy tale' ? 'selected' : '' ?>>Fairy Tale</option>
                <option value="other" <?= $genre == 'other' ? 'selected' : '' ?>>Other</option>
            </select><br>

            <button type="submit">Add Book</button>
        </form>
    </div>
    <footer>
        <p>IK-Library | ELTE IK Webprogramming</p>
    </footer>
</body>
</html>
