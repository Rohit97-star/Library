<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    verify_csrf();
    try {
        $stmt = $pdo->prepare('DELETE FROM students WHERE id=?'); $stmt->execute([(int) $_POST['delete_id']]);
        set_flash($stmt->rowCount() ? 'success' : 'warning', $stmt->rowCount() ? 'Student deleted.' : 'Student was not found.');
    } catch (PDOException $exception) { set_flash('danger', 'This student has loan history and cannot be deleted.'); }
    redirect('manage_students.php');
}
$students = $pdo->query("SELECT s.*, (SELECT COUNT(*) FROM issued_books ib WHERE ib.student_id=s.id AND ib.status='issued') AS active_loans FROM students s ORDER BY s.name")->fetchAll();
$pageTitle = 'Manage students'; require __DIR__ . '/../includes/header.php';
?>
<div class="page-heading"><div><h1>Students</h1><p>Manage registered library members.</p></div><a href="add_student.php" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>Add student</a></div>
<div class="panel-card"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Student</th><th>Email</th><th>Phone</th><th>Active loans</th><th>Registered</th><th class="text-end">Actions</th></tr></thead><tbody>
<?php if (!$students): ?><tr><td colspan="6" class="text-center text-secondary py-4">No students registered yet.</td></tr><?php endif; ?>
<?php foreach ($students as $student): ?><tr><td class="fw-semibold"><?= e($student['name']) ?></td><td><?= e($student['email']) ?></td><td><?= e($student['phone']) ?></td><td><span class="badge <?= (int) $student['active_loans'] ? 'text-bg-primary' : 'text-bg-light text-dark' ?>"><?= (int) $student['active_loans'] ?></span></td><td><?= e(substr($student['created_at'], 0, 10)) ?></td><td class="text-end text-nowrap"><a class="btn btn-sm btn-outline-primary" href="edit_student.php?id=<?= (int) $student['id'] ?>" aria-label="Edit student"><i class="bi bi-pencil"></i></a> <form method="post" class="d-inline" onsubmit="return confirm('Delete this student?')"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="delete_id" value="<?= (int) $student['id'] ?>"><button class="btn btn-sm btn-outline-danger" type="submit" aria-label="Delete student"><i class="bi bi-trash"></i></button></form></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
