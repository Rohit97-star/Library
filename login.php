<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
if (!empty($_SESSION['admin_id'])) { redirect('dashboard.php'); }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $stmt = $pdo->prepare('SELECT id, name, password FROM admins WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        set_flash('success', 'Welcome back, ' . $admin['name'] . '.');
        redirect('dashboard.php');
    }
    $error = 'The email or password was not recognized.';
}
$flash = get_flash();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Admin sign in · Shelfwise</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"><link href="../assets/css/style.css" rel="stylesheet"></head><body class="login-page">
<main class="login-card"><div class="login-logo"><span class="brand-mark"><i class="bi bi-book-half"></i></span>Shelfwise</div><h1 class="h3 fw-bold mb-2">Welcome back</h1><p class="text-secondary mb-4">Sign in to manage your library.</p>
<?php if ($flash): ?><div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?><?php if ($error): ?><div class="alert alert-danger" role="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="mb-3"><label class="form-label" for="email">Email address</label><input class="form-control" id="email" name="email" type="email" autocomplete="username" required autofocus value="<?= e(isset($_POST['email']) ? $_POST['email'] : '') ?>"></div><div class="mb-4"><label class="form-label" for="password">Password</label><input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required></div><button class="btn btn-primary w-100 py-2" type="submit">Sign in <i class="bi bi-arrow-right ms-1"></i></button></form>
<p class="text-center text-secondary small mt-4 mb-0">First time here? <a href="setup_admin.php">Create the first admin</a></p></main></body></html>
