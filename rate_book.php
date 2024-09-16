<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$bookId = $_GET['id'] ?? null;
if ($bookId === null) {
    header('Location: index.php');
    exit;
}

// Load books data
$booksJson = file_get_contents('data.json');
$books = json_decode($booksJson, true);

if (!isset($books[$bookId])) {
    header('Location: index.php');
    exit;
}

// Load users data
$usersJson = file_get_contents('users.json');
$users = json_decode($usersJson, true);

$username = $_SESSION['username'];
if (!isset($users[$username])) {
    header('Location: logout.php');
    exit;
}

$book = $books[$bookId];
$user = $users[$username];

$errors = [];
$successMessage = '';

// Check if the user has already rated the book
$hasRated = false;
foreach ($book['ratings'] as $rating) {
    if ($rating['user'] === $username) {
        $hasRated = true;
        break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$hasRated) {
    $rating = $_POST['rating'] ?? null;
    $review = $_POST['review'] ?? '';

    if ($rating === null || $rating < 1 || $rating > 5) {
        $errors[] = 'Please provide a valid rating between 1 and 5.';
    }

    if (empty($review)) {
        $errors[] = 'Please provide a review.';
    }

    if (empty($errors)) {
        $newRating = [
            'user' => $username,
            'rating' => (int)$rating,
            'review' => $review
        ];

        // Update book data
        $books[$bookId]['ratings'][] = $newRating;

        // Calculate new average rating
        $totalRating = 0;
        $ratingsCount = count($books[$bookId]['ratings']);
        foreach ($books[$bookId]['ratings'] as $r) {
            $totalRating += $r['rating'];
        }
        $books[$bookId]['average_rating'] = $totalRating / $ratingsCount;

        // Update user's data
        $user['reviews'][] = [
            'book_id' => $bookId,
            'rating' => (int)$rating,
            'review' => $review
        ];

        if (!in_array($bookId, array_column($user['read_books'], 'book_id'))) {
            $user['read_books'][] = [
                'book_id' => $bookId,
                // 'time' => date('Y-m-d H:i:s')
            ];
        }

        // Save updated data to JSON files
        $users[$username] = $user;
        file_put_contents('data.json', json_encode($books, JSON_PRETTY_PRINT));
        file_put_contents('users.json', json_encode($users, JSON_PRETTY_PRINT));

        // Redirect to the book details page
        header('Location: details.php?id=' . $bookId);
        exit;
    }
}

function isLoggedIn() {
    return isset($_SESSION['username']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IK-Library | Rate Book</title>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/rate.css">
</head>
<body>
    <header>
        <h1><a href="index.php">IK-Library</a> > Rate Book</h1>
        <?php if (isLoggedIn()): ?>
            <a href="user_profile.php">User Profile</a>
        <?php else: ?>
            <a href="login.php">Login</a> | <a href="register.php">Register</a>
        <?php endif; ?>
    </header>
    <div id="content">
        <h2>Rate <?= htmlspecialchars($book['title']) ?></h2>
        <?php if ($hasRated): ?>
            <div class="info">
                <p>You already rated this book.</p>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($successMessage): ?>
            <div class="success">
                <p><?= htmlspecialchars($successMessage) ?></p>
            </div>
        <?php endif; ?>

        <?php if (!$hasRated): ?>
            <form method="POST" action="rate_book.php?id=<?= htmlspecialchars($bookId) ?>" novalidate>
                <label for="rating">Rating (1-5):</label>
                <input type="number" name="rating" id="rating" min="1" max="5" required>

                <label for="review">Review:</label>
                <textarea name="review" id="review" required></textarea>

                <button type="submit">Submit</button>
            </form>
        <?php endif; ?>
    </div>
    <footer>
        <p>IK-Library | ELTE IK Webprogramming</p>
    </footer>
</body>
</html>

