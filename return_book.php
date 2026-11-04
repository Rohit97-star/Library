<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['loan_id'])) {
    verify_csrf();
    $loanId = filter_var($_POST['loan_id'], FILTER_VALIDATE_INT);
    if (!$loanId) { set_flash('danger', 'Choose a valid loan.'); redirect('return_book.php'); }
    try {
        $pdo->beginTransaction();
        $loanStmt = $pdo->prepare("SELECT id,book_id FROM issued_books WHERE id=? AND status='issued' FOR UPDATE"); $loanStmt->execute([$loanId]); $loan = $loanStmt->fetch();
        if (!$loan) { throw new RuntimeException('This loan was already returned or does not exist.'); }
        $updateLoan = $pdo->prepare("UPDATE issued_books SET status='returned',returned_at=NOW() WHERE id=? AND status='issued'"); $updateLoan->execute([$loanId]);
        if ($updateLoan->rowCount() !== 1) { throw new RuntimeException('This loan was already returned.'); }
        $updateBook = $pdo->prepare('UPDATE books SET available_quantity=available_quantity+1 WHERE id=? AND available_quantity<quantity'); $updateBook->execute([(int) $loan['book_id']]);
        if ($updateBook->rowCount() !== 1) { throw new RuntimeException('Inventory could not be updated. Check the book stock.'); }
        $pdo->commit(); set_flash('success', 'Book return recorded and availability updated.');
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        set_flash('danger', $exception instanceof RuntimeException ? $exception->getMessage() : 'Could not process this return.');
    }
    redirect('return_book.php');
}
$loans = $pdo->query("SELECT ib.id,ib.issue_date,ib.return_date,b.title,b.isbn,s.name AS student_name,CASE WHEN ib.return_date<CURRENT_DATE THEN 1 ELSE 0 END AS overdue FROM issued_books ib JOIN books b ON b.id=ib.book_id JOIN students s ON s.id=ib.student_id WHERE ib.status='issued' ORDER BY ib.return_date,ib.id")->fetchAll();
$pageTitle = 'Return books'; require __DIR__ . '/../includes/header.php';
?>
<div class="page-heading"><div><h1>Return books</h1><p>Check in books that are currently on loan.</p></div><a href="reports.php" class="btn btn-outline-secondary"><i class="bi bi-bar-chart me-1"></i>Loan reports</a></div>
<div class="panel-card"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Book</th><th>Student</th><th>Issue date</th><th>Due date</th><th>Status</th><th class="text-end">Action</th></tr></thead><tbody>
<?php if (!$loans): ?><tr><td colspan="6" class="text-center text-secondary py-4">All books are checked in. No active loans.</td></tr><?php endif; ?><?php foreach ($loans as $loan): ?><tr><td><div class="fw-semibold"><?= e($loan['title']) ?></div><div class="small text-secondary"><?= e($loan['isbn']) ?></div></td><td><?= e($loan['student_name']) ?></td><td><?= e($loan['issue_date']) ?></td><td><?= e($loan['return_date']) ?></td><td><span class="badge text-bg-<?= $loan['overdue'] ? 'danger' : 'warning' ?>"><?= $loan['overdue'] ? 'Overdue' : 'Issued' ?></span></td><td class="text-end"><form method="post" onsubmit="return confirm('Mark this book as returned?')"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="loan_id" value="<?= (int) $loan['id'] ?>"><button class="btn btn-sm btn-success" type="submit"><i class="bi bi-check2 me-1"></i>Mark returned</button></form></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
