<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { set_flash('warning', 'Choose a valid student.'); redirect('manage_students.php'); }
$stmt = $pdo->prepare('SELECT * FROM students WHERE id=?'); $stmt->execute([$id]); $student = $stmt->fetch();
if (!$student) { set_flash('warning', 'Student not found.'); redirect('manage_students.php'); }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim(isset($_POST['name']) ? $_POST['name'] : ''); $email = trim(isset($_POST['email']) ? $_POST['email'] : ''); $phone = trim(isset($_POST['phone']) ? $_POST['phone'] : '');
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $phone === '') { $error = 'Enter a name, valid email address, and phone number.'; }
    else {
        try {
            $update = $pdo->prepare('UPDATE students SET name=?, email=?, phone=? WHERE id=?'); $update->execute([$name, $email, $phone, $id]);
            set_flash('success', 'Student details updated.'); redirect('manage_students.php');
        } catch (PDOException $exception) { $error = 'Could not update this student. The email may already be registered.'; }
    }
}
$pageTitle = 'Edit student'; require __DIR__ . '/../includes/header.php';
?>
<div class="page-heading"><div><h1>Edit student</h1><p>Update member contact details.</p></div><a href="manage_students.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>All students</a></div>
<div class="row"><div class="col-12 col-xl-8"><div class="panel-card"><?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="row g-3"><div class="col-12"><label class="form-label" for="name">Full name</label><input class="form-control" id="name" name="name" required maxlength="140" value="<?= e(isset($_POST['name']) ? $_POST['name'] : $student['name']) ?>"></div>
<div class="col-md-6"><label class="form-label" for="email">Email address</label><input class="form-control" id="email" name="email" type="email" required maxlength="190" value="<?= e(isset($_POST['email']) ? $_POST['email'] : $student['email']) ?>"></div><div class="col-md-6"><label class="form-label" for="phone">Phone number</label><input class="form-control" id="phone" name="phone" type="tel" required maxlength="30" value="<?= e(isset($_POST['phone']) ? $_POST['phone'] : $student['phone']) ?>"></div></div>
<div class="mt-4 d-flex gap-2"><button class="btn btn-primary" type="submit"><i class="bi bi-check2 me-1"></i>Save changes</button><a class="btn btn-light" href="manage_students.php">Cancel</a></div></form></div></div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
