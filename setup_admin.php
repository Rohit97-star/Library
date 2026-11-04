<?php
// First-run admin creation is available only while the admins table is empty.
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
if ((int) $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn() > 0) {
    set_flash('info', 'An administrator already exists. Sign in to continue.');
    redirect('login.php');
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        $error = 'Enter a name, valid email, and password with at least 8 characters.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO admins (name, email, password) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            set_flash('success', 'Administrator created. You can now sign in.');
            redirect('login.php');
        } catch (PDOException $exception) {
            $error = 'Could not create the administrator. Check the email and try again.';
        }
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Create administrator · Shelfwise</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"><link href="../assets/css/style.css" rel="stylesheet"></head><body class="login-page">
<main class="login-card"><div class="login-logo"><span class="brand-mark"><i class="bi bi-book-half"></i></span>Shelfwise</div><h1 class="h3 fw-bold mb-2">Set up your admin</h1><p class="text-secondary mb-4">Create the first account to open your admin workspace.</p><?php if ($error): ?><div class="alert alert-danger" role="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="mb-3"><label class="form-label" for="name">Full name</label><input class="form-control" id="name" name="name" required maxlength="120" value="<?= e(isset($_POST['name']) ? $_POST['name'] : '') ?>"></div><div class="mb-3"><label class="form-label" for="email">Email address</label><input class="form-control" id="email" name="email" type="email" required maxlength="190" value="<?= e(isset($_POST['email']) ? $_POST['email'] : '') ?>"></div><div class="mb-4"><label class="form-label" for="password">Password</label><input class="form-control" id="password" name="password" type="password" minlength="8" autocomplete="new-password" required><div class="form-text">Use at least 8 characters.</div></div><button class="btn btn-primary w-100 py-2" type="submit">Create administrator</button></form></main></body></html>
