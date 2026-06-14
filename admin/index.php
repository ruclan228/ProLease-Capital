<?php
session_start();

$config = require __DIR__ . '/../config/database.php';

$isAuth = !empty($_SESSION['admin_logged']);
$dbAvailable = false;
$pdo = null;
$applications = [];
$users = [];
$products = [];
$tab = $_GET['tab'] ?? 'contracts';
$editItem = null;
$editType = null;

// ─── Login ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password']) && !$isAuth) {
    if ($_POST['password'] === 'admin123') {
        $_SESSION['admin_logged'] = true;
        $isAuth = true;
    }
}

// ─── Logout ───
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// ─── DB + CRUD ───
if ($isAuth) {
    try {
        $pdo = new PDO(
            "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
            $config['user'],
            $config['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
        $dbAvailable = true;

        $action = $_GET['action'] ?? '';
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        // ─── Applications CRUD ───
        if ($action === 'toggle_app' && $id) {
            $pdo->prepare("UPDATE applications SET status = IF(status='active','closed','active') WHERE id = ?")->execute([$id]);
            header('Location: index.php?tab=contracts');
            exit;
        }
        if ($action === 'delete_app' && $id) {
            $pdo->prepare("DELETE FROM applications WHERE id = ?")->execute([$id]);
            header('Location: index.php?tab=contracts');
            exit;
        }
        if ($action === 'seed') {
            $demoHash = password_hash('demo123', PASSWORD_DEFAULT);
            $pdo->exec("DELETE FROM applications");
            $pdo->exec("DELETE FROM users");
            $pdo->exec("DELETE FROM products");
            $pdo->exec("INSERT INTO users (name, email, phone, inn, password_hash) VALUES
                ('ООО «СтройМаш»','stroy@example.com','+7 (495) 111-22-33','7701234567','$demoHash'),
                ('ИП Петров И.А.','petrov@example.com','+7 (495) 222-33-44','7702345678','$demoHash'),
                ('ЗАО «ТехноПром»','techno@example.com','+7 (495) 333-44-55','7703456789','$demoHash'),
                ('ООО «АгроСервис»','agro@example.com','+7 (495) 444-55-66','7704567890','$demoHash'),
                ('ПАО «СтальИнвест»','steel@example.com','+7 (495) 555-66-77','7705678901','$demoHash')");
            $pdo->exec("INSERT INTO products (name, category, price, status) VALUES
                ('Экскаватор Komatsu PC200','Строительная техника',8500000,'available'),
                ('Фронтальный погрузчик SDLG','Строительная техника',3200000,'leased'),
                ('Навесное оборудование John Deere','Сельхозтехника',1800000,'available'),
                ('Компрессор Atlas Copco','Оборудование',950000,'leased'),
                ('Сварочный аппарат Lincoln','Оборудование',420000,'available'),
                ('Генератор SDMO 200 кВт','Энергетика',1350000,'unavailable')");
            $pdo->exec("INSERT INTO applications (user_id, asset_price, advance_payment, term_months, annual_rate, buyout_percent, monthly_payment, total_paid, status) VALUES
                (1,2500000,250000,12,18,10,85000,1020000,'active'),
                (2,1800000,180000,12,16,10,62000,744000,'active'),
                (3,3200000,320000,24,20,10,110000,660000,'active'),
                (4,950000,95000,12,15,10,34000,408000,'closed'),
                (5,4100000,410000,6,14,10,142000,852000,'active')");
            header('Location: index.php?tab=contracts');
            exit;
        }

        // ─── Users CRUD ───
        if ($action === 'delete_user' && $id) {
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
            header('Location: index.php?tab=users');
            exit;
        }

        // ─── Products CRUD ───
        if ($action === 'toggle_product' && $id) {
            $pdo->prepare("UPDATE products SET status = IF(status='available','leased','available') WHERE id = ?")->execute([$id]);
            header('Location: index.php?tab=products');
            exit;
        }
        if ($action === 'delete_product' && $id) {
            $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
            header('Location: index.php?tab=products');
            exit;
        }

        // ─── Save User (POST) ───
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_user'])) {
            $name  = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $inn   = trim($_POST['inn'] ?? '');
            $editId = (int)($_POST['edit_id'] ?? 0);
            if ($name !== '') {
                if ($editId) {
                    $pdo->prepare("UPDATE users SET name=?, email=?, phone=?, inn=? WHERE id=?")->execute([$name, $email, $phone, $inn, $editId]);
                } else {
                    $pdo->prepare("INSERT INTO users (name, email, phone, inn) VALUES (?,?,?,?)")->execute([$name, $email, $phone, $inn]);
                }
            }
            header('Location: index.php?tab=users');
            exit;
        }

        // ─── Save Product (POST) ───
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {
            $name     = trim($_POST['name'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $price    = (float)($_POST['price'] ?? 0);
            $status   = $_POST['status'] ?? 'available';
            $editId   = (int)($_POST['edit_id'] ?? 0);
            if ($name !== '' && $price > 0) {
                if ($editId) {
                    $pdo->prepare("UPDATE products SET name=?, category=?, price=?, status=? WHERE id=?")->execute([$name, $category, $price, $status, $editId]);
                } else {
                    $pdo->prepare("INSERT INTO products (name, category, price, status) VALUES (?,?,?,?)")->execute([$name, $category, $price, $status]);
                }
            }
            header('Location: index.php?tab=products');
            exit;
        }

        // ─── Fetch data ───
        $applications = $pdo->query("SELECT a.*, u.name AS user_name FROM applications a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.id DESC")->fetchAll();
        $users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
        $products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();

        // ─── Edit mode ───
        $editType = $_GET['edit'] ?? '';
        $editId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($editType === 'user' && $editId) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$editId]);
            $editItem = $stmt->fetch();
        }
        if ($editType === 'product' && $editId) {
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$editId]);
            $editItem = $stmt->fetch();
        }

    } catch (PDOException $e) {
        $dbAvailable = false;
        $dbError = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Админ-панель — ProLease Capital</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
  <header class="header">
    <a href="../index.php" class="logo">ProLease Capital</a>
    <nav class="nav">
      <a href="../index.php">Главная</a>
      <a href="../pages/calculator.php">Калькулятор</a>
      <a href="../pages/cabinet.php">Личный кабинет</a>
      <?php if ($isAuth): ?>
        <a href="?logout=1" style="color: var(--danger);">Выйти</a>
      <?php endif; ?>
    </nav>
  </header>

  <?php if (!$isAuth): ?>
  <main class="container" style="max-width: 420px; padding-top: 6rem;">
    <div class="card" style="text-align: center;">
      <h1 style="font-size: 1.5rem; margin-bottom: 1.5rem;">Вход в админ-панель</h1>
      <?php if (isset($_POST['password'])): ?>
        <p style="color: var(--danger); margin-bottom: 1rem;">Неверный пароль</p>
      <?php endif; ?>
      <form method="post">
        <div class="form-group">
          <label for="password">Пароль</label>
          <input type="password" id="password" name="password" placeholder="Введите пароль" required autofocus style="text-align:center;">
        </div>
        <button class="btn" type="submit">Войти</button>
      </form>
    </div>
  </main>
  <?php else: ?>
  <main class="container">
    <div style="text-align: center; margin-bottom: 1rem;">
      <span class="hero-badge">Панель управления</span>
    </div>
    <h1 style="font-size: 2rem; background: linear-gradient(135deg,#1c1917 0%,#065f46 50%,#059669 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; text-align: center;">Админ-панель</h1>
    <p class="subtitle" style="text-align: center;">Аналитика договоров, клиентов и товаров</p>

    <?php if (!$dbAvailable): ?>
      <div class="card" style="margin-bottom: 1rem; border-color: var(--danger); background: rgba(220,38,38,0.03);">
        <p style="color: var(--danger); font-weight: 600;">База данных недоступна</p>
        <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 0.5rem;">Запустите MAMP и откройте phpMyAdmin для импорта sql/init.sql</p>
        <?php if (!empty($dbError)): ?>
          <p style="color: var(--text-secondary); font-size: 0.8rem; margin-top: 0.5rem; font-family: monospace;"><?= htmlspecialchars($dbError) ?></p>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <!-- ─── Tabs ─── -->
    <div class="toolbar" style="justify-content: center; margin-bottom: 2rem;">
      <a href="?tab=contracts" class="btn-sm <?= $tab === 'contracts' ? 'btn-sm--active' : '' ?>">Договоры</a>
      <a href="?tab=users" class="btn-sm <?= $tab === 'users' ? 'btn-sm--active' : '' ?>">Клиенты</a>
      <a href="?tab=products" class="btn-sm <?= $tab === 'products' ? 'btn-sm--active' : '' ?>">Товары</a>
      <?php if ($dbAvailable): ?>
        <a href="?action=seed" class="btn-sm btn-sm--danger" style="font-size: 0.7rem;" onclick="return confirm('Сбросить и загрузить демо-данные?')">Seed</a>
      <?php endif; ?>
    </div>

    <?php if ($tab === 'contracts'): ?>
    <!-- ─── Contracts tab ─── -->
    <div class="stats-grid">
      <div class="stat-card"><div class="number" id="contracts-count">0</div><div class="label">Всего договоров</div></div>
      <div class="stat-card"><div class="number" id="active-count">0</div><div class="label">Активных</div></div>
      <div class="stat-card"><div class="number" id="revenue">0 ₽</div><div class="label">Выручка</div></div>
      <div class="stat-card"><div class="number" id="avg-payment">0 ₽</div><div class="label">Средний платёж</div></div>
      <div class="stat-card"><div class="number" id="expenses">0 ₽</div><div class="label">Расходы</div></div>
      <div class="stat-card"><div class="number" id="profit">0 ₽</div><div class="label">Прибыль</div></div>
    </div>

    <div class="grid-3" style="margin-bottom: 2rem;">
      <div class="card">
        <h2>Статусы договоров</h2>
        <div class="chart-vertical">
          <div class="chart-bar-group"><div class="chart-label">Активны</div><div class="chart-track"><div class="chart-fill chart-fill--green" id="chart-active" style="width:0%"></div></div><div class="chart-value" id="chart-active-label">0</div></div>
          <div class="chart-bar-group"><div class="chart-label">На рассмотрении</div><div class="chart-track"><div class="chart-fill chart-fill--gold" id="chart-pending" style="width:0%"></div></div><div class="chart-value" id="chart-pending-label">0</div></div>
          <div class="chart-bar-group"><div class="chart-label">Закрыты</div><div class="chart-track"><div class="chart-fill chart-fill--gray" id="chart-closed" style="width:0%"></div></div><div class="chart-value" id="chart-closed-label">0</div></div>
        </div>
      </div>
      <div class="card">
        <h2>Финансовые показатели</h2>
        <div class="chart-vertical">
          <div class="chart-bar-group"><div class="chart-label">Выручка</div><div class="chart-track"><div class="chart-fill chart-fill--green" id="chart-revenue-bar" style="width:0%"></div></div><div class="chart-value" id="chart-revenue-label">0 ₽</div></div>
          <div class="chart-bar-group"><div class="chart-label">Расходы</div><div class="chart-track"><div class="chart-fill chart-fill--gold" id="chart-expenses-bar" style="width:0%"></div></div><div class="chart-value" id="chart-expenses-label">0 ₽</div></div>
          <div class="chart-bar-group"><div class="chart-label">Прибыль</div><div class="chart-track"><div class="chart-fill chart-fill--green" id="chart-profit-bar" style="width:0%"></div></div><div class="chart-value" id="chart-profit-label">0 ₽</div></div>
        </div>
      </div>
      <div class="card">
        <h2>Портфель</h2>
        <div style="text-align:center;padding:0.5rem 0;">
          <div style="font-size:2.5rem;font-weight:800;background:var(--primary-gradient);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;" id="portfolio-total">0</div>
          <div style="color:var(--text-secondary);font-size:0.85rem;margin-top:0.25rem;">Всего активов</div>
        </div>
        <div style="display:flex;justify-content:center;gap:2rem;margin-top:1rem;">
          <div style="text-align:center;"><div style="font-size:1.25rem;font-weight:700;color:var(--success);" id="portfolio-active">0</div><div style="color:var(--text-secondary);font-size:0.75rem;">Активные</div></div>
          <div style="text-align:center;"><div style="font-size:1.25rem;font-weight:700;color:var(--gold);" id="portfolio-pending">0</div><div style="color:var(--text-secondary);font-size:0.75rem;">В ожидании</div></div>
          <div style="text-align:center;"><div style="font-size:1.25rem;font-weight:700;color:var(--text-secondary);" id="portfolio-closed">0</div><div style="color:var(--text-secondary);font-size:0.75rem;">Закрыты</div></div>
        </div>
      </div>
    </div>

    <section class="card">
      <h2>Реестр договоров</h2>
      <div class="toolbar">
        <input type="text" id="search-input" placeholder="Поиск по ID...">
        <select id="status-filter">
          <option value="all">Все статусы</option>
          <option value="active">Активен</option>
          <option value="pending">На рассмотрении</option>
          <option value="closed">Закрыт</option>
        </select>
      </div>
      <div style="overflow-x:auto;">
        <table id="contracts-table">
          <thead><tr><th>ID</th><th>Клиент</th><th>Стоимость</th><th>Платёж</th><th>Оплачено</th><th>Статус</th><th>Действие</th></tr></thead>
          <tbody></tbody>
        </table>
      </div>
    </section>

    <?php elseif ($tab === 'users'): ?>
    <!-- ─── Users tab ─── -->
    <div class="stats-grid">
      <div class="stat-card"><div class="number"><?= count($users) ?></div><div class="label">Всего клиентов</div></div>
      <div class="stat-card"><div class="number"><?= count($applications) ?></div><div class="label">Всего договоров</div></div>
      <div class="stat-card"><div class="number"><?= count(array_filter($applications, fn($a) => $a['status'] === 'active')) ?></div><div class="label">Активных</div></div>
    </div>

    <?php if ($editType === 'user'): ?>
    <section class="card" style="margin-bottom:1.5rem;">
      <h2><?= $editItem ? 'Редактирование клиента' : 'Новый клиент' ?></h2>
      <form method="post">
        <input type="hidden" name="save_user" value="1">
        <input type="hidden" name="edit_id" value="<?= $editItem['id'] ?? 0 ?>">
        <div class="form-group"><label>Название / ФИО</label><input type="text" name="name" value="<?= htmlspecialchars($editItem['name'] ?? '') ?>" required></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" value="<?= htmlspecialchars($editItem['email'] ?? '') ?>"></div>
        <div class="form-group"><label>Телефон</label><input type="text" name="phone" value="<?= htmlspecialchars($editItem['phone'] ?? '') ?>"></div>
        <div class="form-group"><label>ИНН</label><input type="text" name="inn" value="<?= htmlspecialchars($editItem['inn'] ?? '') ?>" maxlength="12"></div>
        <div style="display:flex;gap:0.5rem;">
          <button class="btn" type="submit">Сохранить</button>
          <a href="?tab=users" class="btn-sm" style="background:var(--text-secondary);">Отмена</a>
        </div>
      </form>
    </section>
    <?php endif; ?>

    <section class="card">
      <h2>Реестр клиентов</h2>
      <div class="toolbar">
        <a href="?tab=users&edit=user" class="btn-sm">+ Новый клиент</a>
      </div>
      <div style="overflow-x:auto;">
        <table>
          <thead><tr><th>ID</th><th>Название / ФИО</th><th>Email</th><th>Телефон</th><th>ИНН</th><th>Действие</th></tr></thead>
          <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
              <td>#<?= $u['id'] ?></td>
              <td><?= htmlspecialchars($u['name']) ?></td>
              <td><?= htmlspecialchars($u['email'] ?? '—') ?></td>
              <td><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
              <td><?= htmlspecialchars($u['inn'] ?? '—') ?></td>
              <td>
                <a href="?tab=users&edit=user&id=<?= $u['id'] ?>" class="btn-sm">Ред.</a>
                <a href="?action=delete_user&id=<?= $u['id'] ?>" class="btn-sm btn-sm--danger" onclick="return confirm('Удалить клиента «<?= htmlspecialchars($u['name']) ?>»?')">✕</a>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($users)): ?>
            <tr><td colspan="6" style="text-align:center;color:var(--text-secondary);padding:2rem;">Нет клиентов</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <?php elseif ($tab === 'products'): ?>
    <!-- ─── Products tab ─── -->
    <div class="stats-grid">
      <div class="stat-card"><div class="number"><?= count($products) ?></div><div class="label">Всего товаров</div></div>
      <div class="stat-card"><div class="number"><?= count(array_filter($products, fn($p) => $p['status'] === 'available')) ?></div><div class="label">Доступно</div></div>
      <div class="stat-card"><div class="number"><?= count(array_filter($products, fn($p) => $p['status'] === 'leased')) ?></div><div class="label">В лизинге</div></div>
      <div class="stat-card"><div class="number"><?= count(array_filter($products, fn($p) => $p['status'] === 'unavailable')) ?></div><div class="label">Недоступно</div></div>
    </div>

    <?php if ($editType === 'product'): ?>
    <section class="card" style="margin-bottom:1.5rem;">
      <h2><?= $editItem ? 'Редактирование товара' : 'Новый товар' ?></h2>
      <form method="post">
        <input type="hidden" name="save_product" value="1">
        <input type="hidden" name="edit_id" value="<?= $editItem['id'] ?? 0 ?>">
        <div class="form-group"><label>Название</label><input type="text" name="name" value="<?= htmlspecialchars($editItem['name'] ?? '') ?>" required></div>
        <div class="form-group"><label>Категория</label><input type="text" name="category" value="<?= htmlspecialchars($editItem['category'] ?? '') ?>"></div>
        <div class="form-group"><label>Стоимость (₽)</label><input type="number" name="price" value="<?= $editItem['price'] ?? '' ?>" required min="1" step="100"></div>
        <div class="form-group">
          <label>Статус</label>
          <select name="status">
            <option value="available" <?= ($editItem['status'] ?? '') === 'available' ? 'selected' : '' ?>>Доступен</option>
            <option value="leased" <?= ($editItem['status'] ?? '') === 'leased' ? 'selected' : '' ?>>В лизинге</option>
            <option value="unavailable" <?= ($editItem['status'] ?? '') === 'unavailable' ? 'selected' : '' ?>>Недоступен</option>
          </select>
        </div>
        <div style="display:flex;gap:0.5rem;">
          <button class="btn" type="submit">Сохранить</button>
          <a href="?tab=products" class="btn-sm" style="background:var(--text-secondary);">Отмена</a>
        </div>
      </form>
    </section>
    <?php endif; ?>

    <section class="card">
      <h2>Реестр товаров</h2>
      <div class="toolbar">
        <a href="?tab=products&edit=product" class="btn-sm">+ Новый товар</a>
      </div>
      <div style="overflow-x:auto;">
        <table>
          <thead><tr><th>ID</th><th>Название</th><th>Категория</th><th>Стоимость</th><th>Статус</th><th>Действие</th></tr></thead>
          <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
              <td>#<?= $p['id'] ?></td>
              <td><?= htmlspecialchars($p['name']) ?></td>
              <td><?= htmlspecialchars($p['category'] ?? '—') ?></td>
              <td><?= number_format($p['price'], 0, '', ' ') ?> ₽</td>
              <td>
                <?php if ($p['status'] === 'available'): ?>
                  <span class="badge badge--active">Доступен</span>
                <?php elseif ($p['status'] === 'leased'): ?>
                  <span class="badge badge--pending">В лизинге</span>
                <?php else: ?>
                  <span class="badge badge--closed">Недоступен</span>
                <?php endif; ?>
              </td>
              <td>
                <div style="display:flex;flex-wrap:wrap;gap:0.35rem;">
                  <a href="?tab=products&edit=product&id=<?= $p['id'] ?>" class="btn-sm">Ред.</a>
                  <a href="?action=toggle_product&id=<?= $p['id'] ?>" class="btn-sm" style="background:var(--gold);"><?= $p['status'] === 'available' ? '→ Лизинг' : '→ Свободен' ?></a>
                  <a href="?action=delete_product&id=<?= $p['id'] ?>" class="btn-sm btn-sm--danger" onclick="return confirm('Удалить товар?')">✕</a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($products)): ?>
            <tr><td colspan="6" style="text-align:center;color:var(--text-secondary);padding:2rem;">Нет товаров</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
    <?php endif; ?>
  </main>

  <footer class="footer">
    <div class="footer-content">
      <div class="footer-grid">
        <div class="footer-col">
          <div class="footer-logo">ProLease Capital</div>
          <p class="footer-desc">Лизинг оборудования для малого и среднего бизнеса</p>
        </div>
        <div class="footer-col">
          <div class="footer-heading">Навигация</div>
          <a href="../index.php" class="footer-link">Главная</a>
          <a href="../pages/calculator.php" class="footer-link">Калькулятор</a>
        </div>
        <div class="footer-col">
          <div class="footer-heading">Контакты</div>
          <div class="footer-text">Москва, ул. Образцова, 9/1</div>
          <div class="footer-text">+7 (495) 123-45-67</div>
          <div class="footer-text">info@prolease-capital.ru</div>
        </div>
      </div>
      <div class="footer-bottom">© 2026 ProLease Capital. Учебный проект ПМ.05</div>
    </div>
  </footer>

  <?php if ($dbAvailable && $tab === 'contracts'): ?>
  <script>var _dbApps = <?= json_encode($applications) ?>;</script>
  <?php endif; ?>
  <script src="../js/algorithms.js"></script>
  <?php if ($tab === 'contracts'): ?>
  <script src="../js/admin.js"></script>
  <?php endif; ?>
  <?php endif; ?>
</body>
</html>
