<?php
session_start();
header('Content-Type: application/json');

$config = require __DIR__ . '/../config/database.php';

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    http_response_code(401);
    echo json_encode(['error' => 'Не авторизован']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$assetPrice     = (float)($input['assetPrice'] ?? 0);
$advancePayment = (float)($input['advancePayment'] ?? 0);
$termMonths     = (int)($input['termMonths'] ?? 0);
$annualRate     = (float)($input['annualRate'] ?? 0);
$buyoutPercent  = (float)($input['buyoutPercent'] ?? 10);
$monthlyPayment = (float)($input['monthlyPayment'] ?? 0);
$totalPaid      = (float)($input['totalPaid'] ?? 0);

if ($assetPrice <= 0 || $termMonths <= 0 || $monthlyPayment <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Некорректные данные']);
    exit;
}

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
        $config['user'],
        $config['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $stmt = $pdo->prepare("INSERT INTO applications (user_id, asset_price, advance_payment, term_months, annual_rate, buyout_percent, monthly_payment, total_paid, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([$userId, $assetPrice, $advancePayment, $termMonths, $annualRate, $buyoutPercent, $monthlyPayment, $totalPaid]);

    echo json_encode(['ok' => true, 'id' => $pdo->lastInsertId()]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка базы данных']);
}
