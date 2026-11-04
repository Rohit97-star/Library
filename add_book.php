<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $title = trim(isset($_POST['title']) ? $_POST['title'] : '');
    $author = trim(isset($_POST['author']) ? $_POST['author'] : '');
    $category = trim(isset($_POST['category']) ? $_POST['category'] : '');
    $isbn = trim(isset($_POST['isbn']) ? $_POST['isbn'] : '');
    $quantity = filter_var(isset($_POST['quantity']) ? $_POST['quantity'] : null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    if ($title === '' || $author === '' || $category === '' || $isbn === '' || $quantity === false) {
        $error = 'Complete all fields and enter a valid non-negative quantity.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO books (title, author, category, isbn, quantity, available_quantity) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$title, $author, $category, $isbn, $quantity, $quantity]);
            set_flash('success', 'Book added to the collection.');
            redirect('manage_books.php');
        } catch (PDOException $exception) { $error = 'Could not save this book. The ISBN may already be in use.'; }
    }
}
$pageTitle = 'Add book';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-heading"><div><h1>Add a book</h1><p>Add a title to your library collection.</p></div><a href="manage_books.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>All books</a></div>
<div class="row"><div class="col-12 col-xl-8"><div class="panel-card"><?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="row g-3">
<div class="col-12"><label class="form-label" for="title">Book title</label><input class="form-control" id="title" name="title" required maxlength="200" value="<?= e(isset($_POST['title']) ? $_POST['title'] : '') ?>"></div>
<div class="col-md-6"><label class="form-label" for="author">Author</label><input class="form-control" id="author" name="author" required maxlength="160" value="<?= e(isset($_POST['author']) ? $_POST['author'] : '') ?>"></div>
<div class="col-md-6"><label class="form-label" for="category">Category</label><input class="form-control" id="category" name="category" required maxlength="100" placeholder="e.g. Fiction" value="<?= e(isset($_POST['category']) ? $_POST['category'] : '') ?>"></div>
<div class="col-md-6"><label class="form-label" for="isbn">ISBN</label><input class="form-control" id="isbn" name="isbn" required maxlength="30" value="<?= e(isset($_POST['isbn']) ? $_POST['isbn'] : '') ?>"></div>
<div class="col-md-6"><label class="form-label" for="quantity">Total copies</label><input class="form-control" id="quantity" name="quantity" type="number" min="0" required value="<?= e(isset($_POST['quantity']) ? $_POST['quantity'] : '1') ?>"></div>
</div><div class="mt-4 d-flex gap-2"><button class="btn btn-primary" type="submit"><i class="bi bi-check2 me-1"></i>Save book</button><a class="btn btn-light" href="manage_books.php">Cancel</a></div></form></div></div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
