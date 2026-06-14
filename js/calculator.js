document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('lease-form');
  const resultBlock = document.getElementById('calc-result');

  if (!form) return;

  // Pre-fill from URL parameter ?price=...
  const params = new URLSearchParams(window.location.search);
  const priceParam = params.get('price');
  if (priceParam) {
    const priceField = document.getElementById('asset-price');
    const advanceField = document.getElementById('advance');
    if (priceField) {
      priceField.value = priceParam;
      if (advanceField && !advanceField.value) {
        advanceField.value = Math.round(priceParam * 0.1);
      }
      priceField.dispatchEvent(new Event('input'));
    }
  }

  form.addEventListener('input', updateCalculation);
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    saveApplication();
  });

  function getValues() {
    return {
      assetPrice: Number(document.getElementById('asset-price').value) || 0,
      advancePayment: Number(document.getElementById('advance').value) || 0,
      termMonths: Number(document.getElementById('term').value) || 0,
      annualRate: Number(document.getElementById('rate').value) || 0,
      buyoutPercent: Number(document.getElementById('buyout-percent').value) || 10,
      extensionMonths: Number(document.getElementById('extension').value) || 0,
    };
  }

  function updateCalculation() {
    const { assetPrice, advancePayment, termMonths, annualRate, buyoutPercent, extensionMonths } = getValues();

    const monthly = calculateMonthlyPayment(assetPrice, advancePayment, termMonths, annualRate);
    const total = calculateTotalPayments(monthly, termMonths, advancePayment);
    const buyout = calculateBuyoutPrice(assetPrice, buyoutPercent);
    const extension = calculateExtensionCost(monthly, extensionMonths);
    const overpay = calculateOverpayment(total, buyout, assetPrice);

    document.getElementById('monthly-payment').textContent = formatRub(monthly);
    document.getElementById('total-payments').textContent = formatRub(total);
    document.getElementById('buyout-price').textContent = formatRub(buyout);
    document.getElementById('extension-cost').textContent = formatRub(extension);
    document.getElementById('overpayment').textContent = formatRub(overpay);

    resultBlock.hidden = assetPrice <= 0 || termMonths <= 0;
  }

  function saveApplication() {
    const { assetPrice, advancePayment, termMonths, annualRate, buyoutPercent } = getValues();
    const monthly = calculateMonthlyPayment(assetPrice, advancePayment, termMonths, annualRate);
    const total = calculateTotalPayments(monthly, termMonths, advancePayment);

    const applications = JSON.parse(localStorage.getItem('leaseApplications') || '[]');
    applications.push({
      id: Date.now(),
      assetPrice,
      advancePayment,
      termMonths,
      annualRate,
      buyoutPercent,
      monthlyPayment: monthly,
      totalPaid: total,
      status: 'pending',
      createdAt: new Date().toISOString(),
    });
    localStorage.setItem('leaseApplications', JSON.stringify(applications));

    // Save to DB if logged in
    if (typeof _userId !== 'undefined' && _userId !== null) {
      fetch('../api/save_application.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ assetPrice, advancePayment, termMonths, annualRate, buyoutPercent, monthlyPayment: monthly, totalPaid: total }),
      })
        .then((r) => r.json())
        .then((data) => {
          if (data.ok) {
            showToast('Заявка сохранена в базе данных!');
          } else {
            showToast('Заявка сохранена локально.');
          }
        })
        .catch(() => {
          showToast('Заявка сохранена локально.');
        });
    } else {
      showToast('Заявка сохранена! Войдите в кабинет для сохранения в БД.');
    }

    form.reset();
    resultBlock.hidden = true;
    updateCartCount();
  }

  function showToast(message) {
    const existing = document.querySelector('.toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => toast.classList.add('toast--visible'), 10);
    setTimeout(() => {
      toast.classList.remove('toast--visible');
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  updateCalculation();
});
