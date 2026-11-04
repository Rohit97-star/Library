<?php
$pageTitle = isset($pageTitle) ? $pageTitle : 'Admin';
$currentPage = basename($_SERVER['PHP_SELF']);
$flash = get_flash();
$navItems = [
    ['dashboard.php', 'grid-1x2', 'Dashboard'],
    ['manage_books.php', 'book', 'Books'],
    ['manage_students.php', 'people', 'Students'],
    ['issue_book.php', 'box-arrow-up-right', 'Issue a book'],
    ['return_book.php', 'box-arrow-in-down-left', 'Return books'],
    ['reports.php', 'bar-chart', 'Reports'],
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> · Shelfwise Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="admin-body">
<nav class="navbar topbar sticky-top">
    <div class="container-fluid px-3 px-lg-4">
        <button class="btn btn-light d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-label="Open navigation"><i class="bi bi-list fs-5"></i></button>
        <a class="navbar-brand fw-bold" href="dashboard.php"><span class="brand-mark"><i class="bi bi-book-half"></i></span>Shelfwise</a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <span class="small text-secondary d-none d-sm-inline"><i class="bi bi-person-circle me-1"></i><?= e(isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Administrator') ?></span>
            <form method="post" action="logout.php" class="m-0"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><button class="btn btn-sm btn-outline-secondary" type="submit"><i class="bi bi-box-arrow-right me-1"></i>Sign out</button></form>
        </div>
    </div>
</nav>
<div class="admin-shell">
    <aside class="offcanvas-lg offcanvas-start sidebar" tabindex="-1" id="adminSidebar" aria-label="Admin navigation">
        <div class="offcanvas-header d-lg-none"><h5 class="offcanvas-title">Library menu</h5><button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button></div>
        <div class="offcanvas-body p-3"><div class="sidebar-label">WORKSPACE</div><nav class="nav flex-column gap-1">
            <?php foreach ($navItems as $item): ?><a class="nav-link <?= $currentPage === $item[0] ? 'active' : '' ?>" href="<?= e($item[0]) ?>"><i class="bi bi-<?= e($item[1]) ?>"></i><span><?= e($item[2]) ?></span></a><?php endforeach; ?>
        </nav><div class="sidebar-note mt-auto"><i class="bi bi-lightbulb me-1"></i>Keep your collection up to date to make lending simple.</div></div>
    </aside>
    <main class="main-content"><div class="content-wrap">
        <?php if ($flash): ?><div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert"><?= e($flash['message']) ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div><?php endif; ?>
