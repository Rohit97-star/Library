<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $bookId = filter_var(isset($_POST['book_id']) ? $_POST['book_id'] : null, FILTER_VALIDATE_INT);
    $studentId = filter_var(isset($_POST['student_id']) ? $_POST['student_id'] : null, FILTER_VALIDATE_INT);
    $dueDate = isset($_POST['return_date']) ? $_POST['return_date'] : '';
    $validDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate) && strtotime($dueDate) !== false && date('Y-m-d', strtotime($dueDate)) === $dueDate;
    if (!$bookId || !$studentId || !$validDate || $dueDate < date('Y-m-d')) { $error = 'Select a book and student, and choose a valid due date that is today or later.'; }
    else {
        try {
            $pdo->beginTransaction();
            $bookStmt = $pdo->prepare('SELECT available_quantity FROM books WHERE id=? FOR UPDATE'); $bookStmt->execute([$bookId]); $book = $bookStmt->fetch();
            $studentStmt = $pdo->prepare('SELECT id FROM students WHERE id=?'); $studentStmt->execute([$studentId]);
            if (!$book || !$studentStmt->fetch()) { throw new RuntimeException('The selected book or student no longer exists.'); }
            if ((int) $book['available_quantity'] < 1) { throw new RuntimeException('No copies of that book are currently available.'); }
            $insert = $pdo->prepare("INSERT INTO issued_books (book_id,student_id,issue_date,return_date,status) VALUES (?, ?, CURRENT_DATE, ?, 'issued')"); $insert->execute([$bookId, $studentId, $dueDate]);
            $update = $pdo->prepare('UPDATE books SET available_quantity=available_quantity-1 WHERE id=? AND available_quantity>0'); $update->execute([$bookId]);
            if ($update->rowCount() !== 1) { throw new RuntimeException('No copies of that book are currently available.'); }
            $pdo->commit(); set_flash('success', 'Book issued successfully.'); redirect('issue_book.php');
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            $error = $exception instanceof RuntimeException ? $exception->getMessage() : 'Could not issue the book. Please try again.';
        }
    }
}
$books = $pdo->query('SELECT id,title,author,available_quantity FROM books WHERE available_quantity>0 ORDER BY title')->fetchAll();
$students = $pdo->query('SELECT id,name,email FROM students ORDER BY name')->fetchAll();
$recent = $pdo->query("SELECT ib.issue_date,ib.return_date,b.title,s.name AS student_name FROM issued_books ib JOIN books b ON b.id=ib.book_id JOIN students s ON s.id=ib.student_id WHERE ib.status='issued' ORDER BY ib.id DESC LIMIT 8")->fetchAll();
$pageTitle = 'Issue a book'; require __DIR__ . '/../includes/header.php';
?>
<div class="page-heading"><div><h1>Issue a book</h1><p>Record a loan and reserve one available copy.</p></div><a href="return_book.php" class="btn btn-outline-primary"><i class="bi bi-box-arrow-in-down-left me-1"></i>Process returns</a></div>
<div class="row g-4"><div class="col-12 col-xl-5"><div class="panel-card"><h2 class="panel-title">New loan</h2><?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<?php if (!$students): ?><div class="alert alert-warning">Add a student before issuing a book. <a href="add_student.php">Add student</a></div><?php elseif (!$books): ?><div class="alert alert-warning">There are no available books to issue.</div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="mb-3"><label class="form-label" for="student_id">Student</label><select class="form-select" id="student_id" name="student_id" required><option value="">Choose a student</option><?php foreach ($students as $student): ?><option value="<?= (int) $student['id'] ?>" <?= (isset($_POST['student_id']) && (int) $_POST['student_id'] === (int) $student['id']) ? 'selected' : '' ?>><?= e($student['name']) ?> · <?= e($student['email']) ?></option><?php endforeach; ?></select></div>
<div class="mb-3"><label class="form-label" for="book_id">Book</label><select class="form-select" id="book_id" name="book_id" required><option value="">Choose an available book</option><?php foreach ($books as $book): ?><option value="<?= (int) $book['id'] ?>" <?= (isset($_POST['book_id']) && (int) $_POST['book_id'] === (int) $book['id']) ? 'selected' : '' ?>><?= e($book['title']) ?> — <?= e($book['author']) ?> (<?= (int) $book['available_quantity'] ?> available)</option><?php endforeach; ?></select></div>
<div class="mb-4"><label class="form-label" for="return_date">Due date</label><input class="form-control" id="return_date" name="return_date" type="date" min="<?= e(date('Y-m-d')) ?>" required value="<?= e(isset($_POST['return_date']) ? $_POST['return_date'] : date('Y-m-d', strtotime('+14 days'))) ?>"></div><button class="btn btn-primary" type="submit" <?= (!$books || !$students) ? 'disabled' : '' ?>><i class="bi bi-box-arrow-up-right me-1"></i>Confirm issue</button></form></div></div>
<div class="col-12 col-xl-7"><div class="panel-card"><h2 class="panel-title">Current loans</h2><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Book</th><th>Student</th><th>Issued</th><th>Due</th></tr></thead><tbody><?php if (!$recent): ?><tr><td colspan="4" class="text-center text-secondary py-4">No active loans.</td></tr><?php endif; ?><?php foreach ($recent as $row): ?><tr><td class="fw-semibold"><?= e($row['title']) ?></td><td><?= e($row['student_name']) ?></td><td><?= e($row['issue_date']) ?></td><td><?= e($row['return_date']) ?></td></tr><?php endforeach; ?></tbody></table></div></div></div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
