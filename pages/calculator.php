<?php
session_start();
$userId = $_SESSION['user_id'] ?? null;
$userName = $_SESSION['user_name'] ?? null;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Калькулятор лизинга — ProLease Capital</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
  <header class="header">
    <a href="../index.php" class="logo">ProLease Capital</a>
    <nav class="nav">
      <a href="../index.php">Главная</a>
      <a href="calculator.php" class="active">Калькулятор</a>
      <a href="cabinet.php">Личный кабинет</a>
      <?php if ($userId): ?>
        <a href="cabinet.php?logout=1" style="color: var(--danger);">Выйти</a>
      <?php endif; ?>
    </nav>
  </header>

  <main class="container">
    <div style="text-align: center; margin-bottom: 1rem;">
      <span class="hero-badge">Онлайн-расчёт</span>
    </div>
    <h1 style="font-size: 2rem; background: linear-gradient(135deg,#1c1917 0%,#065f46 50%,#059669 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; text-align: center;">Калькулятор лизинга</h1>
    <p class="subtitle" style="text-align: center;">Рассчитайте ежемесячный платёж, выкупную стоимость и общую сумму договора</p>

    <div class="grid">
      <section class="card">
        <h2>Параметры договора</h2>
        <form id="lease-form">
          <div class="form-group">
            <label for="asset-price">Стоимость имущества (₽)</label>
            <input type="number" id="asset-price" value="2500000" min="100000" step="10000">
          </div>
          <div class="form-group">
            <label for="advance">Авансовый платёж (₽)</label>
            <input type="number" id="advance" value="500000" min="0" step="10000">
          </div>
          <div class="form-group">
            <label for="term">Срок лизинга (мес.)</label>
            <input type="number" id="term" value="36" min="6" max="60">
          </div>
          <div class="form-group">
            <label for="rate">Годовая ставка (%)</label>
            <input type="number" id="rate" value="12" min="0" max="30" step="0.5">
          </div>
          <div class="form-group">
            <label for="buyout-percent">Выкуп (% от стоимости)</label>
            <input type="number" id="buyout-percent" value="10" min="1" max="30">
          </div>
          <div class="form-group">
            <label for="extension">Продление (мес.)</label>
            <input type="number" id="extension" value="12" min="0" max="36">
          </div>
          <button type="submit" class="btn">Отправить заявку</button>
        </form>
      </section>

      <section class="card" id="calc-result">
        <h2>Результаты расчёта</h2>
        <div class="result-item">
          <span class="label">Ежемесячный платёж</span>
          <span class="value" id="monthly-payment">—</span>
        </div>
        <div class="result-item">
          <span class="label">Общая сумма выплат</span>
          <span class="value" id="total-payments">—</span>
        </div>
        <div class="result-item">
          <span class="label">Выкупная стоимость</span>
          <span class="value" id="buyout-price">—</span>
        </div>
        <div class="result-item">
          <span class="label">Стоимость продления</span>
          <span class="value" id="extension-cost">—</span>
        </div>
        <div class="result-item" style="border-bottom: none;">
          <span class="label">Переплата</span>
          <span class="value" id="overpayment">—</span>
        </div>
      </section>
    </div>
    <!-- Info -->
    <section class="card" style="margin-top: 1.5rem; background: rgba(6, 95, 70, 0.03);">
      <h2>Как рассчитывается платёж</h2>
      <p style="color: var(--text-secondary); font-size: 0.9rem; line-height: 1.7; margin-bottom: 1rem;">
        Расчёт ежемесячного платежа производится по <strong>аннуитетной формуле</strong>:
        сумма финансирования (стоимость имущества минус аванс) умножается на коэффициент аннуитета,
        который зависит от процентной ставки и срока договора.
      </p>
      <p style="color: var(--text-secondary); font-size: 0.9rem; line-height: 1.7; margin-bottom: 0.5rem;">
        <strong>Формула:</strong> Платеж = Сумма финансирования × (мес.ставка × (1 + мес.ставка)^срок) / ((1 + мес.ставка)^срок − 1)
      </p>
      <ul style="color: var(--text-secondary); font-size: 0.88rem; line-height: 1.8; padding-left: 1.25rem; margin-top: 1rem;">
        <li>Аванс — от 10% до 50% от стоимости имущества</li>
        <li>Срок договора — от 6 до 60 месяцев</li>
        <li>Годовая ставка — от 10% до 30%</li>
        <li>Выкупная стоимость — от 1% до 30% от цены имущества</li>
      </ul>
    </section>
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

  <script>var _userId = <?= json_encode($userId) ?>;</script>
  <script src="../js/algorithms.js"></script>
  <script src="../js/calculator.js"></script>
</body>
</html>
