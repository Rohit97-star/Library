<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
$stats = [
    'books' => (int) $pdo->query('SELECT COUNT(*) FROM books')->fetchColumn(),
    'students' => (int) $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn(),
    'issued' => (int) $pdo->query("SELECT COUNT(*) FROM issued_books WHERE status = 'issued'")->fetchColumn(),
    'returned' => (int) $pdo->query("SELECT COUNT(*) FROM issued_books WHERE status = 'returned'")->fetchColumn(),
    'available' => (int) $pdo->query('SELECT COALESCE(SUM(available_quantity), 0) FROM books')->fetchColumn(),
];
$recent = $pdo->query('SELECT ib.issue_date, ib.return_date, ib.status, b.title, s.name AS student_name FROM issued_books ib JOIN books b ON b.id=ib.book_id JOIN students s ON s.id=ib.student_id ORDER BY ib.id DESC LIMIT 6')->fetchAll();
$pageTitle = 'Dashboard';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-heading"><div><h1>Good day, <?= e(isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Librarian') ?></h1><p>Here is what is happening in your library today.</p></div><a href="issue_book.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Issue a book</a></div>
<div class="row g-3 mb-4">
<?php $cards = [['Total books',$stats['books'],'book','bg-primary-subtle text-primary'],['Students',$stats['students'],'people','bg-success-subtle text-success'],['Currently issued',$stats['issued'],'box-arrow-up-right','bg-warning-subtle text-warning-emphasis'],['Returned loans',$stats['returned'],'check2-circle','bg-info-subtle text-info-emphasis'],['Available copies',$stats['available'],'bookshelf','bg-danger-subtle text-danger']]; foreach ($cards as $card): ?>
<div class="col-12 col-sm-6 col-xl"><div class="stat-card"><div class="d-flex justify-content-between align-items-start"><div><div class="stat-label"><?= e($card[0]) ?></div><div class="stat-value"><?= number_format($card[1]) ?></div></div><span class="stat-icon <?= e($card[3]) ?>"><i class="bi bi-<?= e($card[2]) ?>"></i></span></div></div></div><?php endforeach; ?>
</div><div class="panel-card"><div class="d-flex justify-content-between align-items-center gap-2 mb-3"><h2 class="panel-title mb-0">Recent activity</h2><a href="reports.php" class="small text-decoration-none">View reports <i class="bi bi-arrow-right"></i></a></div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Book</th><th>Student</th><th>Issue date</th><th>Due date</th><th>Status</th></tr></thead><tbody>
<?php if (!$recent): ?><tr><td colspan="5" class="text-center text-secondary py-4">No loans recorded yet.</td></tr><?php endif; ?><?php foreach ($recent as $row): ?><tr><td class="fw-semibold"><?= e($row['title']) ?></td><td><?= e($row['student_name']) ?></td><td><?= e($row['issue_date']) ?></td><td><?= e($row['return_date']) ?></td><td><span class="badge text-bg-<?= $row['status'] === 'issued' ? 'warning' : 'success' ?>"><?= e(ucfirst($row['status'])) ?></span></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
