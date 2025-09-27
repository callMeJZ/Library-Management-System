<?php
// Include database connection. Assumes $mysqli object is defined here.
require_once 'db_connect.php';

$books = [];
$error_message = null;

// --- SEARCH HANDLING (Simple) ---
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$where_clause = '';

if (!empty($search_term)) {
    // 1. Prepare search term
    // Use prepared statements in production code for maximum security, but 
    // for this simple demonstration, we use real_escape_string to keep it simple.
    $safe_search_term = "%" . $mysqli->real_escape_string($search_term) . "%";

    // 2. Build WHERE clause (Search by Title, Author, ISBN, or Category)
    $where_clause = " 
        WHERE 
            title LIKE '{$safe_search_term}' OR 
            author LIKE '{$safe_search_term}' OR 
            isbn LIKE '{$safe_search_term}' OR 
            category LIKE '{$safe_search_term}'
    ";
}

// Check if the database connection succeeded
if ($mysqli && !$mysqli->connect_error) {
    
    // SQL Query to fetch filtered books (or all books if no search term)
    $sql = "
        SELECT 
            book_id, 
            title, 
            author, 
            isbn, 
            publication_year, 
            category,
            status
        FROM 
            books
        {$where_clause}
        ORDER BY 
            title ASC;
    ";

    $result = $mysqli->query($sql);

    if ($result === FALSE) {
        $error_message = "Database Query Error: " . $mysqli->error;
    } elseif ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $books[] = $row;
        }
    }

    $mysqli->close();
} else {
    $error_message = "Database connection failed.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Book Catalog</title>
    <style>
        /* Basic CSS for a clean, simple look */
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        .search-form { margin-bottom: 20px; display: flex; gap: 10px; }
        .search-form input[type="text"] { padding: 8px; width: 300px; border: 1px solid #ccc; border-radius: 4px; }
        .search-form button { padding: 8px 15px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .search-form button:hover { background-color: #0056b3; }
        .book-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .book-table th, .book-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .book-table th { background-color: #f2f2f2; font-weight: bold; }
        /* Status styling */
        .status-Available { color: green; font-weight: bold; }
        .status-Borrowed { color: red; font-weight: bold; }
        .error { color: red; font-weight: bold; padding: 10px; }
    </style>
</head>
<body>

    <h1>Library Book Catalog</h1>

    <!-- Search Form (Frontend Requirement Met) -->
    <form method="GET" class="search-form">
        <input type="text" name="search" placeholder="Search by Title, Author, ISBN, or Category" 
               value="<?= htmlspecialchars($search_term) ?>">
        <button type="submit">Search</button>
        <?php if (!empty($search_term)): ?>
            <!-- Clear search button -->
            <a href="catalog.php" style="padding: 8px 15px; border: 1px solid #ccc; border-radius: 4px; text-decoration: none; color: #333; align-self: center;">Clear Search</a>
        <?php endif; ?>
    </form>

    <?php if ($error_message): ?>
        <p class="error">Error: <?= $error_message ?></p>
    <?php elseif (empty($books) && !empty($search_term)): ?>
        <p>No books found matching "<?= htmlspecialchars($search_term) ?>".</p>
    <?php elseif (empty($books)): ?>
        <p>No books found in the catalog.</p>
    <?php else: ?>
        <p>Showing <?= count($books) ?> books.</p>
        <table class="book-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>ISBN</th>
                    <th>Category</th>
                    <th>Year</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $book): ?>
                    <tr>
                        <td><?= htmlspecialchars($book['title']) ?></td>
                        <td><?= htmlspecialchars($book['author']) ?></td>
                        <td><?= htmlspecialchars($book['isbn']) ?></td>
                        <td><?= htmlspecialchars($book['category'] ?? 'N/A') ?></td> 
                        <td><?= htmlspecialchars($book['publication_year']) ?></td>
                        <td class="status-<?= htmlspecialchars($book['status']) ?>">
                            <?= htmlspecialchars($book['status']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</body>
</html>
