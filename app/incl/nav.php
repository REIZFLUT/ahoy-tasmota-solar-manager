<nav class="py-2 bg-body-tertiary border-bottom">
        <div class="container d-flex flex-wrap">
            <ul class="nav me-auto">
                <li class="nav-item"><a href="http://<?= $_SERVER['HTTP_HOST'] ?>/index.php?date=<?= $_GET['date'] ?? date('Y-m-d'); ?>" class="nav-link link-body-emphasis px-2 <?= ($page == 'home') ? 'active':''; ?>" aria-current="page">Home</a></li>
                <li class="nav-item"><a href="http://<?= $_SERVER['HTTP_HOST'] ?>/monitor.php?date=<?= $_GET['date'] ?? date('Y-m-d'); ?>" class="nav-link link-body-emphasis px-2 <?= ($page == 'monitor') ? 'active':''; ?>">Monitor</a></li>
                <li class="nav-item"><a href="http://<?= $_SERVER['HTTP_HOST'] ?>/settings.php" class="nav-link link-body-emphasis px-2 <?= ($page == 'settings') ? 'active':''; ?>">Einstellungen</a></li>
            </ul>
            <div>
                <form method="GET" id="date_form">
                    <input type="date" name="date" id="view_date" class="form-control" value="<?= $_GET['date'] ?? date('Y-m-d') ?>" required>
                    <input type="hidden" name="mode" value="<?= $_GET['mode'] ?? 'day'; ?>">
                    <script>
                        const view_date = document.getElementById('view_date');
                        const date_form = document.getElementById('date_form');
                        view_date.addEventListener('change', (e) => {
                            date_form.submit();
                        })
                    </script>
                </form>
            </div>
        </div>
    </nav>