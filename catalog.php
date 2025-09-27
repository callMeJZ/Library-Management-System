<?php
// Include database connection (assumes $mysqli is defined here)
require_once 'db_connect.php';
session_start(); // Start the session to access the user's role

// --- ADDED LOGIC FOR BACK BUTTON ---
// Determine the correct dashboard link based on the session role
$dashboard_url = 'user_dashboard.php'; // Default if role is not set or is 'User'
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Librarian') {
    $dashboard_url = 'librarian_dashboard.php';
}
// --- END ADDED LOGIC ---

$books = [];
$error_message = null;
$records_per_page = 10; 

// Get and sanitize search term and current page number
$search_term = trim($_GET['search'] ?? '');
$current_page = max(1, (int)($_GET['page'] ?? 1)); 

$where_clause = '';
if ($search_term) {
    // Build WHERE clause for Title, Author, ISBN, or Category
    $safe_term = "%" . $mysqli->real_escape_string($search_term) . "%";
    $where_clause = " 
        WHERE 
            title LIKE '{$safe_term}' OR 
            author LIKE '{$safe_term}' OR 
            isbn LIKE '{$safe_term}' OR 
            category LIKE '{$safe_term}'
    ";
}


// 1. Get total count of books (for pagination)
$count_sql = "SELECT COUNT(*) AS total FROM books {$where_clause}";
$count_result = $mysqli->query($count_sql);

$total_books = 0;
$total_pages = 1;

if ($count_result === FALSE) {
    $error_message = "Database Query Error: " . $mysqli->error;
} elseif ($count_row = $count_result->fetch_assoc()) {
    $total_books = (int)$count_row['total'];
    $total_pages = ceil($total_books / $records_per_page);
    
    // Calculate offset for current page
    $current_page = min($current_page, $total_pages);
    $current_page = max(1, $current_page); 
    $offset = ($current_page - 1) * $records_per_page;
}

// 2. Fetch paginated book data
if (!$error_message) { 
    $sql = "
        SELECT 
            book_id, title, author, isbn, publication_year, category, status
        FROM 
            books
        {$where_clause}
        ORDER BY 
            title ASC
        LIMIT {$records_per_page} OFFSET {$offset};
    ";

    $result = $mysqli->query($sql);

    if ($result === FALSE) {
        $error_message = "Database Query Error: " . $mysqli->error;
    } elseif ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $books[] = $row;
        }
    }
}

// Close resources
if (isset($count_result) && $count_result instanceof mysqli_result) { $count_result->free(); }
if (isset($result) && $result instanceof mysqli_result) { $result->free(); }
if (isset($mysqli)) { $mysqli->close(); }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Book Catalog</title>
    <style>
        /* Basic styles for structure and typography */
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        .back-link { /* Style for the new back button */
            display: inline-block;
            padding: 8px 15px;
            margin-bottom: 20px;
            background-color: #6c757d; 
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .back-link:hover {
            background-color: #5a6268;
        }
        .search-form { margin-bottom: 20px; display: flex; gap: 10px; }
        .search-form input[type="text"] { padding: 8px; width: 300px; border: 1px solid #ccc; border-radius: 4px; }
        .search-form button { padding: 8px 15px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .search-form button:hover { background-color: #0056b3; }
        .book-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .book-table th, .book-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .book-table th { background-color: #f2f2f2; font-weight: bold; }
        /* Status colors */
        .status-Available { color: green; font-weight: bold; }
        .status-Borrowed { color: red; font-weight: bold; }
        .error { color: red; font-weight: bold; padding: 10px; }
        
        /* Pagination layout */
        .pagination { 
            margin-top: 20px; 
            text-align: center; 
            padding: 10px;
            border-top: 1px solid #ccc;
        }
        .pagination a {
            color: #333;
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #ddd;
            margin: 0 4px;
            border-radius: 4px;
        }
        .pagination a.active {
            background-color: #007bff; 
            color: white;
            border: 1px solid #007bff;
        }
        .pagination a:hover:not(.active) {background-color: #f2f2f2;}
    </style>
</head>
<body>

    <!-- ADDED BACK BUTTON -->
    <a href="<?= htmlspecialchars($dashboard_url) ?>" class="back-link">
        &larr; Back to Dashboard
    </a>

    <h1>Library Book Catalog</h1>

    <!-- Search Form -->
    <form method="GET" class="search-form">
        <input type="text" name="search" placeholder="Search by Title, Author, ISBN, or Category" 
               value="<?= htmlspecialchars($search_term) ?>">
        <button type="submit">Search</button>
        <?php if (!empty($search_term)): ?>
            <!-- Clear Search link -->
            <a href="catalog.php?page=<?= $current_page ?>" style="padding: 8px 15px; border: 1px solid #ccc; border-radius: 4px; text-decoration: none; color: #333; align-self: center;">Clear Search</a>
        <?php endif; ?>
    </form>

    <?php if ($error_message): ?>
        <p class="error">Error: <?= $error_message ?></p>
    <?php elseif (empty($books) && !empty($search_term)): ?>
        <p>No books found matching "<?= htmlspecialchars($search_term) ?>".</p>
    <?php elseif (empty($books) && $total_books == 0): ?>
        <p>No books found in the catalog.</p>
    <?php else: ?>

        <!-- Showing results summary -->
        <p>
            <?php if (!empty($search_term)): ?>
                Search results: 
            <?php endif; ?>
            Showing 
            <?= $total_books > 0 ? (($current_page - 1) * $records_per_page) + 1 : 0 ?> 
            to 
            <?= (($current_page - 1) * $records_per_page) + count($books) ?> 
            of 
            <?= $total_books ?> books total.
        </p>

        <!-- Book data table -->
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

        <!-- PAGINATION CONTROLS -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php 
                // Build base URL for pagination links, keeping search term
                $base_query = http_build_query(array_filter(['search' => $search_term]));
                $base_url = "catalog.php?" . $base_query . (empty($base_query) ? '' : '&');
                ?>

                <?php if ($current_page > 1): ?>
                    <a href="<?= $base_url ?>page=<?= $current_page - 1 ?>">Previous</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="<?= $base_url ?>page=<?= $i ?>" class="<?= ($i == $current_page) ? 'active' : ''; ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="<?= $base_url ?>page=<?= $current_page + 1 ?>">Next</a>
                <?php endif; ?>
                
            </div>
        <?php endif; ?>

    <?php endif; ?>

</body>
</html>
