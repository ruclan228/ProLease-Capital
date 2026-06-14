<?php
session_start();

$config = require __DIR__ . '/../config/database.php';

$userId = $_SESSION['user_id'] ?? null;
$user = null;
$applications = [];
$error = '';
$success = '';
$showRegister = false;

// ─── DB connection ───
$pdo = null;
try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
        $config['user'],
        $config['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    // DB unavailable
}

// ─── Logout ───
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: cabinet.php');
    exit;
}

// ─── Register ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register']) && $pdo) {
    $showRegister = true;
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $error = 'Заполните обязательные поля';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль не менее 6 символов';
    } elseif ($password !== $confirm) {
        $error = 'Пароли не совпадают';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Пользователь с таким email уже существует';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO users (name, email, phone, password_hash) VALUES (?, ?, ?, ?)")
                ->execute([$name, $email, $phone, $hash]);
            $userId = (int)$pdo->lastInsertId();
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $name;
            header('Location: cabinet.php');
            exit;
        }
    }
}

// ─── Login ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login']) && $pdo) {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Заполните email и пароль';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $found = $stmt->fetch();
        if ($found && password_verify($password, $found['password_hash'])) {
            $userId = (int)$found['id'];
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $found['name'];
            header('Location: cabinet.php');
            exit;
        } else {
            $error = 'Неверный email или пароль';
        }
    }
}

// ─── Fetch user data ───
if ($userId && $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user_name'] = $user['name'];
        $stmt = $pdo->prepare("SELECT * FROM applications WHERE user_id = ? ORDER BY id DESC");
        $stmt->execute([$userId]);
        $applications = $stmt->fetchAll();
    } else {
        session_destroy();
        $userId = null;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Личный кабинет — ProLease Capital</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
  <header class="header">
    <a href="../index.php" class="logo">ProLease Capital</a>
    <nav class="nav">
      <a href="../index.php">Главная</a>
      <a href="calculator.php">Калькулятор</a>
      <a href="cabinet.php" class="active">Личный кабинет</a>
      <?php if ($userId): ?>
        <a href="?logout=1" style="color: var(--danger);">Выйти</a>
      <?php endif; ?>
    </nav>
  </header>

  <main class="container">

    <?php if (!$userId): ?>
    <!-- ─── Auth forms ─── -->
    <div style="max-width: 520px; margin: 0 auto; padding-top: 3rem;">

      <?php if ($error): ?>
        <div class="card" style="margin-bottom: 1rem; border-color: var(--danger);">
          <p style="color: var(--danger);"><?= htmlspecialchars($error) ?></p>
        </div>
      <?php endif; ?>

      <?php if (!$pdo): ?>
        <div class="card" style="margin-bottom: 1rem; border-color: var(--gold);">
          <p style="color: var(--gold);">База данных недоступна. Регистрация и вход временно невозможны.</p>
          <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 0.5rem;">Данные калькулятора сохраняются в localStorage.</p>
        </div>
      <?php endif; ?>

      <!-- Login -->
      <section class="card" style="margin-bottom: 1rem;">
        <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem;">Вход</h2>
          <form method="post">
            <input type="hidden" name="login" value="1">
            <div class="form-group">
              <label>Email</label>
              <input type="email" name="email" placeholder="you@example.com" required>
            </div>
            <div class="form-group">
              <label>Пароль</label>
              <input type="password" name="password" placeholder="Введите пароль" required>
            </div>
            <button class="btn" type="submit" <?= $pdo ? '' : 'disabled' ?>>Войти</button>
          </form>
        </section>

      <p style="text-align:center;margin-bottom:1.5rem;">
        <span style="color:var(--text-secondary);font-size:0.9rem;">Нет аккаунта?</span>
        <a href="#" onclick="document.getElementById('register-section').style.display='block';this.style.display='none';return false;" style="color:var(--primary);font-weight:600;font-size:0.9rem;">Зарегистрироваться</a>
      </p>

      <!-- Register (hidden by default) -->
      <section id="register-section" class="card" style="<?= $showRegister ? '' : 'display:none; ' ?>margin-bottom:1.5rem;">
        <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem;">Регистрация</h2>
          <form method="post">
            <input type="hidden" name="register" value="1">
            <div class="form-group">
              <label>Название / ФИО *</label>
              <input type="text" name="name" placeholder="ООО «Компания»" required>
            </div>
            <div class="form-group">
              <label>Email *</label>
              <input type="email" name="email" placeholder="you@example.com" required>
            </div>
            <div class="form-group">
              <label>Телефон</label>
              <input type="text" name="phone" placeholder="+7 (999) 123-45-67">
            </div>
            <div class="form-group">
              <label>Пароль * (мин. 6 символов)</label>
              <input type="password" name="password" placeholder="Придумайте пароль" required minlength="6">
            </div>
            <div class="form-group">
              <label>Повторите пароль *</label>
              <input type="password" name="confirm_password" placeholder="Повторите пароль" required>
            </div>
            <button class="btn" type="submit" <?= $pdo ? '' : 'disabled' ?>>Зарегистрироваться</button>
          </form>
        </section>

    </div>

    <?php else: ?>
    <!-- ─── Authorized: dashboard ─── -->
    <div style="text-align: center; margin-bottom: 1rem;">
      <span class="hero-badge">Мои заявки</span>
    </div>
    <h1 style="font-size: 2rem; background: linear-gradient(135deg,#1c1917 0%,#065f46 50%,#059669 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; text-align: center;">
      <?= htmlspecialchars($user['name']) ?>
    </h1>
    <p class="subtitle" style="text-align: center;"><?= htmlspecialchars($user['email']) ?><?= $user['phone'] ? ' · ' . htmlspecialchars($user['phone']) : '' ?></p>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="number"><?= count($applications) ?></div>
        <div class="label">Всего заявок</div>
      </div>
      <div class="stat-card">
        <div class="number"><?= count(array_filter($applications, fn($a) => $a['status'] === 'pending')) ?></div>
        <div class="label">На рассмотрении</div>
      </div>
      <div class="stat-card">
        <div class="number"><?= count(array_filter($applications, fn($a) => $a['status'] === 'active')) ?></div>
        <div class="label">Активных</div>
      </div>
    </div>

    <section class="card">
      <h2>Реестр заявок</h2>
      <div style="overflow-x: auto;">
        <table>
          <thead>
            <tr>
              <th>№</th>
              <th>Стоимость имущества</th>
              <th>Ежемесячный платёж</th>
              <th>Срок</th>
              <th>Статус</th>
              <th>Дата подачи</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($applications as $a): ?>
            <tr>
              <td>#<?= $a['id'] ?></td>
              <td><?= number_format($a['asset_price'], 0, '', ' ') ?> ₽</td>
              <td><?= number_format($a['monthly_payment'], 0, '', ' ') ?> ₽</td>
              <td><?= $a['term_months'] ?> мес.</td>
              <td>
                <?php if ($a['status'] === 'active'): ?>
                  <span class="badge badge--active">Активен</span>
                <?php elseif ($a['status'] === 'pending'): ?>
                  <span class="badge badge--pending">На рассмотрении</span>
                <?php else: ?>
                  <span class="badge badge--closed">Закрыт</span>
                <?php endif; ?>
              </td>
              <td><?= date('d.m.Y', strtotime($a['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($applications)): ?>
            <tr><td colspan="6" style="text-align: center; color: var(--text-secondary); padding: 2rem;">У вас пока нет заявок. <a href="calculator.php">Перейти к калькулятору</a></td></tr>
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
          <a href="calculator.php" class="footer-link">Калькулятор</a>
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
</body>
</html>
