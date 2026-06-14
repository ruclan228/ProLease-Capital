/**
 * Алгоритмы обработки числовых данных — ProLease Capital
 * Тема 2: расчёт лизинговых платежей, выкупа, аналитика
 */

/** Ежемесячный лизинговый платёж (аннуитетная формула) */
function calculateMonthlyPayment(assetPrice, advancePayment, termMonths, annualRate) {
  const principal = assetPrice - advancePayment;
  if (principal <= 0 || termMonths <= 0) return 0;

  const monthlyRate = annualRate / 12 / 100;
  if (monthlyRate === 0) return Math.round(principal / termMonths);

  const factor = Math.pow(1 + monthlyRate, termMonths);
  const payment = (principal * monthlyRate * factor) / (factor - 1);
  return Math.round(payment);
}

/** Общая сумма выплат за срок договора */
function calculateTotalPayments(monthlyPayment, termMonths, advancePayment) {
  return advancePayment + monthlyPayment * termMonths;
}

/** Выкупная стоимость имущества (% от первоначальной цены) */
function calculateBuyoutPrice(assetPrice, buyoutPercent) {
  buyoutPercent = buyoutPercent ?? 10;
  return Math.round(assetPrice * buyoutPercent / 100);
}

/** Стоимость продления договора */
function calculateExtensionCost(monthlyPayment, extensionMonths) {
  return monthlyPayment * extensionMonths;
}

/** Переплата по договору относительно стоимости имущества */
function calculateOverpayment(totalPayments, buyoutPrice, assetPrice) {
  return totalPayments + buyoutPrice - assetPrice;
}

/** Количество заявок/договоров */
function countContracts(contracts) {
  return contracts.length;
}

/** Суммарная выручка по договорам */
function calculateRevenue(contracts) {
  return contracts.reduce((sum, contract) => sum + contract.totalPaid, 0);
}

/** Прибыль = выручка − расходы */
function calculateProfit(revenue, expenses) {
  return revenue - expenses;
}

/** Средний ежемесячный платёж по портфелю */
function calculateAveragePayment(contracts) {
  if (contracts.length === 0) return 0;
  const total = contracts.reduce((sum, c) => sum + c.monthlyPayment, 0);
  return Math.round(total / contracts.length);
}

/** Количество активных договоров */
function countActiveContracts(contracts) {
  return contracts.filter((c) => c.status === 'active').length;
}

/** Форматирование суммы в рублях */
function formatRub(amount) {
  return amount.toLocaleString('ru-RU') + ' ₽';
}

if (typeof module !== 'undefined' && module.exports) {
  module.exports = {
    calculateMonthlyPayment,
    calculateTotalPayments,
    calculateBuyoutPrice,
    calculateExtensionCost,
    calculateOverpayment,
    countContracts,
    calculateRevenue,
    calculateProfit,
    calculateAveragePayment,
    countActiveContracts,
    formatRub,
  };
}
