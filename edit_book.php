<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { set_flash('warning', 'Choose a valid book.'); redirect('manage_books.php'); }
$stmt = $pdo->prepare('SELECT * FROM books WHERE id = ?'); $stmt->execute([$id]); $book = $stmt->fetch();
if (!$book) { set_flash('warning', 'Book not found.'); redirect('manage_books.php'); }
$activeStmt = $pdo->prepare("SELECT COUNT(*) FROM issued_books WHERE book_id = ? AND status = 'issued'"); $activeStmt->execute([$id]); $outstanding = (int) $activeStmt->fetchColumn();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $title = trim(isset($_POST['title']) ? $_POST['title'] : ''); $author = trim(isset($_POST['author']) ? $_POST['author'] : '');
    $category = trim(isset($_POST['category']) ? $_POST['category'] : ''); $isbn = trim(isset($_POST['isbn']) ? $_POST['isbn'] : '');
    $quantity = filter_var(isset($_POST['quantity']) ? $_POST['quantity'] : null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    if ($title === '' || $author === '' || $category === '' || $isbn === '' || $quantity === false) { $error = 'Complete all fields and enter a valid non-negative quantity.'; }
    elseif ($quantity < $outstanding) { $error = 'Total copies cannot be lower than the number currently issued (' . $outstanding . ').'; }
    else {
        try {
            $update = $pdo->prepare('UPDATE books SET title=?, author=?, category=?, isbn=?, quantity=?, available_quantity=? WHERE id=?');
            $update->execute([$title, $author, $category, $isbn, $quantity, $quantity - $outstanding, $id]);
            set_flash('success', 'Book details and quantity updated.'); redirect('manage_books.php');
        } catch (PDOException $exception) { $error = 'Could not update this book. The ISBN may already be in use.'; }
    }
}
$pageTitle = 'Edit book'; require __DIR__ . '/../includes/header.php';
?>
<div class="page-heading"><div><h1>Edit book</h1><p>Update book details and total inventory.</p></div><a href="manage_books.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>All books</a></div>
<div class="row"><div class="col-12 col-xl-8"><div class="panel-card"><?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="row g-3">
<div class="col-12"><label class="form-label" for="title">Book title</label><input class="form-control" id="title" name="title" required maxlength="200" value="<?= e(isset($_POST['title']) ? $_POST['title'] : $book['title']) ?>"></div>
<div class="col-md-6"><label class="form-label" for="author">Author</label><input class="form-control" id="author" name="author" required maxlength="160" value="<?= e(isset($_POST['author']) ? $_POST['author'] : $book['author']) ?>"></div>
<div class="col-md-6"><label class="form-label" for="category">Category</label><input class="form-control" id="category" name="category" required maxlength="100" value="<?= e(isset($_POST['category']) ? $_POST['category'] : $book['category']) ?>"></div>
<div class="col-md-6"><label class="form-label" for="isbn">ISBN</label><input class="form-control" id="isbn" name="isbn" required maxlength="30" value="<?= e(isset($_POST['isbn']) ? $_POST['isbn'] : $book['isbn']) ?>"></div>
<div class="col-md-6"><label class="form-label" for="quantity">Total copies</label><input class="form-control" id="quantity" name="quantity" type="number" min="<?= $outstanding ?>" required value="<?= e(isset($_POST['quantity']) ? $_POST['quantity'] : $book['quantity']) ?>"><div class="form-text"><?= $outstanding ?> currently issued; available count recalculates automatically.</div></div>
</div><div class="mt-4 d-flex gap-2"><button class="btn btn-primary" type="submit"><i class="bi bi-check2 me-1"></i>Save changes</button><a class="btn btn-light" href="manage_books.php">Cancel</a></div></form></div></div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
