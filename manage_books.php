<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    verify_csrf();
    try {
        $stmt = $pdo->prepare('DELETE FROM books WHERE id = ?');
        $stmt->execute([(int) $_POST['delete_id']]);
        set_flash($stmt->rowCount() ? 'success' : 'warning', $stmt->rowCount() ? 'Book deleted.' : 'Book was not found.');
    } catch (PDOException $exception) { set_flash('danger', 'This book has loan history and cannot be deleted.'); }
    redirect('manage_books.php');
}
$search = trim(isset($_GET['q']) ? $_GET['q'] : '');
$sql = 'SELECT * FROM books'; $params = [];
if ($search !== '') { $sql .= ' WHERE title LIKE ? OR author LIKE ? OR category LIKE ? OR isbn LIKE ?'; $like = '%' . $search . '%'; $params = [$like, $like, $like, $like]; }
$sql .= ' ORDER BY added_date DESC, id DESC';
$stmt = $pdo->prepare($sql); $stmt->execute($params); $books = $stmt->fetchAll();
$pageTitle = 'Manage books';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-heading"><div><h1>Books</h1><p>Search and manage your library collection.</p></div><a href="add_book.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add book</a></div>
<div class="panel-card"><form class="row g-2 mb-3" method="get"><div class="col"><input class="form-control" type="search" name="q" placeholder="Search title, author, category or ISBN" value="<?= e($search) ?>" aria-label="Search books"></div><div class="col-auto"><button class="btn btn-outline-primary h-100" type="submit"><i class="bi bi-search me-1"></i>Search</button></div><?php if ($search !== ''): ?><div class="col-auto"><a href="manage_books.php" class="btn btn-light h-100">Clear</a></div><?php endif; ?></form>
<div class="table-responsive"><table class="table mb-0"><thead><tr><th>Title / Author</th><th>Category</th><th>ISBN</th><th>Copies</th><th>Added</th><th class="text-end">Actions</th></tr></thead><tbody>
<?php if (!$books): ?><tr><td colspan="6" class="text-center text-secondary py-4">No books found<?= $search ? ' for this search' : '' ?>.</td></tr><?php endif; ?>
<?php foreach ($books as $book): ?><tr><td><div class="fw-semibold"><?= e($book['title']) ?></div><div class="small text-secondary"><?= e($book['author']) ?></div></td><td><?= e($book['category']) ?></td><td><?= e($book['isbn']) ?></td><td><span class="fw-semibold"><?= (int) $book['available_quantity'] ?></span><span class="text-secondary"> / <?= (int) $book['quantity'] ?></span></td><td><?= e($book['added_date']) ?></td><td class="text-end text-nowrap"><a class="btn btn-sm btn-outline-primary" href="edit_book.php?id=<?= (int) $book['id'] ?>" aria-label="Edit book"><i class="bi bi-pencil"></i></a> <form method="post" class="d-inline" onsubmit="return confirm('Delete this book?')"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="delete_id" value="<?= (int) $book['id'] ?>"><button class="btn btn-sm btn-outline-danger" type="submit" aria-label="Delete book"><i class="bi bi-trash"></i></button></form></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
