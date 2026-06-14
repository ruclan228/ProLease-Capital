<?php
session_start();
$userId = $_SESSION['user_id'] ?? null;
$products = [];
try {
    $config = require __DIR__ . '/config/database.php';
    $pdo = new PDO("mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4", $config['user'], $config['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    $products = $pdo->query("SELECT * FROM products WHERE status = 'available' ORDER BY price DESC LIMIT 6")->fetchAll();
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ProLease Capital — Лизинговая компания</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <header class="header">
    <a href="index.php" class="logo">ProLease Capital</a>
    <nav class="nav">
      <a href="index.php" class="active">Главная</a>
      <a href="pages/calculator.php">Калькулятор</a>
      <a href="pages/cabinet.php">Личный кабинет</a>
      <?php if ($userId): ?>
        <a href="pages/cabinet.php?logout=1" style="color: var(--danger);">Выйти</a>
      <?php endif; ?>
    </nav>
  </header>

  <main>
    <!-- ─── Hero ─── -->
    <section class="hero">
      <div class="container">
        <div class="hero-badge">Лизинг оборудования для бизнеса</div>
        <h1>ProLease Capital</h1>
        <p>Предоставляем имущество во временное пользование <br>с возможностью выкупа, продления или возврата</p>
        <a href="pages/calculator.php" class="btn">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Рассчитать стоимость лизинга
        </a>
        <div class="hero-meta">⏱ Одобрение за 1–3 дня · 📄 Без справок о доходах</div>
      </div>
    </section>

    <!-- ─── Stats ─── -->
    <div class="container">
      <div class="stats-grid">
        <div class="stat-card">
          <div class="number">48+</div>
          <div class="label">Активных договоров</div>
        </div>
        <div class="stat-card">
          <div class="number">1–3 дня</div>
          <div class="label">Срок одобрения</div>
        </div>
        <div class="stat-card">
          <div class="number">от 10%</div>
          <div class="label">Аванс</div>
        </div>
        <div class="stat-card">
          <div class="number">99%</div>
          <div class="label">Удовлетворённых клиентов</div>
        </div>
      </div>
    </div>

    <!-- ─── Why us ─── -->
    <section class="section-alt">
      <div class="container">
        <div class="section-header">
          <h2>Почему выбирают ProLease Capital</h2>
          <p>Мы делаем лизинг простым, быстрым и выгодным для вашего бизнеса</p>
        </div>
        <div class="features-grid">
          <div class="feature-card">
            <div class="feature-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <h3>Быстрое решение</h3>
            <p>Одобрение заявки в течение 1–3 рабочих дней. Минимум документов — максимум скорости.</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>
            </div>
            <h3>Гибкие условия</h3>
            <p>Выкуп имущества, продление договора или возврат — вы выбираете удобный вариант завершения.</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <h3>Доступные ставки</h3>
            <p>Конкурентоспособные процентные ставки от 10% годовых. Индивидуальный подход к каждому клиенту.</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            </div>
            <h3>Любое оборудование</h3>
            <p>Спецтехника, транспорт, производственное и медицинское оборудование — широкий спектр имущества.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ─── How it works ─── -->
    <section class="container">
      <div class="section-header">
        <h2>Как это работает</h2>
        <p>Всего 4 шага от заявки до получения оборудования</p>
      </div>
      <div class="steps-grid">
        <div class="step-card">
          <div class="step-number">1</div>
          <h3>Заявка</h3>
          <p>Заполните онлайн-заявку на калькуляторе — это займёт не больше 5 минут</p>
        </div>
        <div class="step-arrow">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--primary-light)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
        <div class="step-card">
          <div class="step-number">2</div>
          <h3>Одобрение</h3>
          <p>Менеджер проводит скоринг и принимает решение в течение 1–3 рабочих дней</p>
        </div>
        <div class="step-arrow">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--primary-light)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
        <div class="step-card">
          <div class="step-number">3</div>
          <h3>Договор</h3>
          <p>Подписываем договор лизинга и поставляем оборудование</p>
        </div>
        <div class="step-arrow">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--primary-light)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
        <div class="step-card">
          <div class="step-number">4</div>
          <h3>Выбор</h3>
          <p>По окончании срока: выкуп, продление или возврат оборудования</p>
        </div>
      </div>
    </section>

    <!-- ─── Services ─── -->
    <section class="section-alt">
      <div class="container">
        <div class="section-header">
          <h2>Наши услуги</h2>
          <p>Полный цикл лизингового обслуживания для вашего бизнеса</p>
        </div>
        <div class="grid-3">
          <div class="card service-card">
            <div class="service-icon">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 18V6"/></svg>
            </div>
            <h3>Выкуп имущества</h3>
            <p>После завершения договора вы можете выкупить оборудование по остаточной стоимости и стать его полноправным владельцем.</p>
          </div>
          <div class="card service-card">
            <div class="service-icon">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
            </div>
            <h3>Продление договора</h3>
            <p>Если вам нужно больше времени — продлите договор на выгодных условиях без дополнительных комиссий.</p>
          </div>
          <div class="card service-card">
            <div class="service-icon">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </div>
            <h3>Возврат оборудования</h3>
            <p>Если оборудование больше не нужно — просто верните его после окончания срока договора без штрафов.</p>
          </div>
        </div>
      </div>
    </section>

    <?php if (!empty($products)): ?>
    <!-- ─── Products ─── -->
    <section class="container">
      <div class="section-header">
        <h2>Доступное оборудование</h2>
        <p>Оборудование, доступное для лизинга прямо сейчас</p>
      </div>
      <div class="grid-3">
        <?php foreach ($products as $p): ?>
        <div class="card product-card">
          <div class="product-status">
            <?php if ($p['status'] === 'available'): ?>
              <span class="badge badge--active">Доступен</span>
            <?php elseif ($p['status'] === 'leased'): ?>
              <span class="badge badge--pending">В лизинге</span>
            <?php else: ?>
              <span class="badge badge--closed">Недоступен</span>
            <?php endif; ?>
          </div>
          <div class="product-icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
          </div>
          <h3><?= htmlspecialchars($p['name']) ?></h3>
          <p class="product-category"><?= htmlspecialchars($p['category'] ?? '') ?></p>
          <div class="product-price"><?= number_format($p['price'], 0, '', ' ') ?> ₽</div>
          <a href="pages/calculator.php?price=<?= $p['price'] ?>" class="btn">Рассчитать лизинг</a>
        </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <!-- ─── CTA ─── -->
    <section class="container">
      <div class="cta-block">
        <div class="cta-content">
          <h2>Рассчитайте стоимость лизинга прямо сейчас</h2>
          <p>Узнайте ежемесячный платёж, общую сумму и выкупную стоимость за 1 минуту</p>
          <a href="pages/calculator.php" class="btn" style="width: auto;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            Перейти к расчёту
          </a>
        </div>
      </div>
    </section>

    <!-- ─── FAQ ─── -->
    <section class="container" style="padding-top: 0;">
      <div class="section-header">
        <h2>Часто задаваемые вопросы</h2>
      </div>
      <div class="faq-list">
        <details class="faq-item">
          <summary class="faq-question">Какие документы нужны для оформления лизинга?</summary>
          <p class="faq-answer">Для юридических лиц: ИНН, ОГРН, выписка из ЕГРЮЛ. Для ИП: паспорт, ИНН, ОГРНИП. В большинстве случаев справки о доходах не требуются.</p>
        </details>
        <details class="faq-item">
          <summary class="faq-question">Как быстро происходит одобрение заявки?</summary>
          <p class="faq-answer">Мы обрабатываем заявки в течение 1–3 рабочих дней. В некоторых случаях решение может быть принято в день обращения.</p>
        </details>
        <details class="faq-item">
          <summary class="faq-question">Что будет после окончания срока лизинга?</summary>
          <p class="faq-answer">После завершения договора вы можете выбрать один из трёх вариантов: выкупить имущество по остаточной стоимости, продлить договор на новый срок или вернуть оборудование без дополнительных комиссий.</p>
        </details>
        <details class="faq-item">
          <summary class="faq-question">Можно ли досрочно выкупить имущество?</summary>
          <p class="faq-answer">Да, вы можете досрочно выкупить оборудование. Для этого необходимо обратиться к вашему менеджеру для расчёта остаточной стоимости.</p>
        </details>
      </div>
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
          <a href="index.php" class="footer-link">Главная</a>
          <a href="pages/calculator.php" class="footer-link">Калькулятор</a>
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
