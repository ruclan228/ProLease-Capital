const FIXED_EXPENSES = 1_100_000;

function getContracts() {
  if (typeof _dbApps !== 'undefined' && _dbApps !== null) {
    const normalized = _dbApps.map((r) => ({
      id: Number(r.id),
      user_name: r.user_name ?? '—',
      assetPrice: Number(r.asset_price) || 0,
      advancePayment: Number(r.advance_payment) || 0,
      termMonths: Number(r.term_months) || 0,
      annualRate: Number(r.annual_rate) || 0,
      buyoutPercent: Number(r.buyout_percent) || 10,
      monthlyPayment: Number(r.monthly_payment) || 0,
      totalPaid: Number(r.total_paid) || 0,
      status: r.status,
      created_at: r.created_at,
    }));
    return normalized.length ? normalized : null;
  }
  const apps = JSON.parse(localStorage.getItem('leaseApplications') || '[]');
  return apps.length ? apps : null;
}

function getDemoData() {
  return [
    { id: 1, assetPrice: 2_500_000, monthlyPayment: 85_000, totalPaid: 1_020_000, status: 'active' },
    { id: 2, assetPrice: 1_800_000, monthlyPayment: 62_000, totalPaid: 744_000, status: 'active' },
    { id: 3, assetPrice: 3_200_000, monthlyPayment: 110_000, totalPaid: 660_000, status: 'active' },
    { id: 4, assetPrice: 950_000, monthlyPayment: 34_000, totalPaid: 408_000, status: 'closed' },
    { id: 5, assetPrice: 4_100_000, monthlyPayment: 142_000, totalPaid: 852_000, status: 'active' },
  ];
}

function statusLabel(status) {
  const labels = { active: 'Активен', pending: 'На рассмотрении', closed: 'Закрыт' };
  return labels[status] || status;
}

function renderTable(contracts) {
  const tbody = document.querySelector('#contracts-table tbody');
  if (!tbody) return;

  const useDb = typeof _dbApps !== 'undefined' && _dbApps !== null;

  tbody.innerHTML = contracts
    .map(
      (c) => `
    <tr>
      <td>#${c.id}</td>
      <td>${c.user_name ?? '—'}</td>
      <td>${formatRub(c.assetPrice)}</td>
      <td>${formatRub(c.monthlyPayment)}</td>
      <td>${formatRub(c.totalPaid)}</td>
      <td><span class="badge badge--${c.status}">${statusLabel(c.status)}</span></td>
      <td><div style="display:flex;flex-wrap:nowrap;gap:0.35rem;">${useDb
        ? `<a href="?action=toggle_app&id=${c.id}" class="btn-sm">${c.status === 'active' ? 'Закрыть' : 'Активировать'}</a><a href="?action=delete_app&id=${c.id}" class="btn-sm btn-sm--danger" onclick="return confirm('Удалить договор №${c.id}?')">✕</a>`
        : `<button class="btn-sm" data-action="toggle-status" data-id="${c.id}">${c.status === 'active' ? 'Закрыть' : 'Активировать'}</button><button class="btn-sm btn-sm--danger" data-action="delete" data-id="${c.id}">✕</button>`}</div></td>
    </tr>`
    )
    .join('');
}

function refreshStats() {
  const contracts = getContracts() || getDemoData();
  const search = (document.getElementById('search-input')?.value || '').toLowerCase();
  const status = document.getElementById('status-filter')?.value || 'all';

  const filtered = contracts.filter((c) => {
    const matchSearch = String(c.id).includes(search);
    const matchStatus = status === 'all' || c.status === status;
    return matchSearch && matchStatus;
  });

  const contractsCount = countContracts(filtered);
  const activeCount = countActiveContracts(filtered);
  const revenue = calculateRevenue(filtered);
  const avgPayment = calculateAveragePayment(filtered);
  const variableCosts = activeCount * 12_000;
  const profit = calculateProfit(revenue, FIXED_EXPENSES + variableCosts);

  document.getElementById('contracts-count').textContent = contractsCount;
  document.getElementById('active-count').textContent = activeCount;
  document.getElementById('revenue').textContent = formatRub(revenue);
  document.getElementById('avg-payment').textContent = formatRub(avgPayment);
  document.getElementById('expenses').textContent = formatRub(FIXED_EXPENSES + variableCosts);
  document.getElementById('profit').textContent = formatRub(profit);

  renderTable(filtered);

  // Charts
  const allContracts = getContracts() || getDemoData();
  const activeAll = allContracts.filter((c) => c.status === 'active').length;
  const pendingAll = allContracts.filter((c) => c.status === 'pending').length;
  const closedAll = allContracts.filter((c) => c.status === 'closed').length;
  const totalAll = allContracts.length;

  const maxCount = Math.max(activeAll, pendingAll, closedAll, 1);

  document.getElementById('chart-active').style.width = (activeAll / maxCount) * 100 + '%';
  document.getElementById('chart-active-label').textContent = activeAll;
  document.getElementById('chart-pending').style.width = (pendingAll / maxCount) * 100 + '%';
  document.getElementById('chart-pending-label').textContent = pendingAll;
  document.getElementById('chart-closed').style.width = (closedAll / maxCount) * 100 + '%';
  document.getElementById('chart-closed-label').textContent = closedAll;

  // Finance charts
  const allRevenue = calculateRevenue(allContracts);
  const allActive = countActiveContracts(allContracts);
  const allVarCosts = allActive * 12_000;
  const allExpenses = FIXED_EXPENSES + allVarCosts;
  const allProfit = calculateProfit(allRevenue, allExpenses);
  const maxFinance = Math.max(allRevenue, allExpenses, allProfit, 1);

  document.getElementById('chart-revenue-bar').style.width = (allRevenue / maxFinance) * 100 + '%';
  document.getElementById('chart-revenue-label').textContent = formatRub(allRevenue);
  document.getElementById('chart-expenses-bar').style.width = (allExpenses / maxFinance) * 100 + '%';
  document.getElementById('chart-expenses-label').textContent = formatRub(allExpenses);
  document.getElementById('chart-profit-bar').style.width = (allProfit / maxFinance) * 100 + '%';
  document.getElementById('chart-profit-label').textContent = formatRub(allProfit);

  // Portfolio summary
  document.getElementById('portfolio-total').textContent = totalAll;
  document.getElementById('portfolio-active').textContent = activeAll;
  document.getElementById('portfolio-pending').textContent = pendingAll;
  document.getElementById('portfolio-closed').textContent = closedAll;
}

function handleTableClick(e) {
  if (typeof _dbApps !== 'undefined' && _dbApps !== null) return;

  const btn = e.target.closest('button');
  if (!btn) return;

  const id = Number(btn.dataset.id);
  const action = btn.dataset.action;
  const appData = localStorage.getItem('leaseApplications');
  const isDemo = !appData;

  if (action === 'toggle-status') {
    let contracts;
    if (isDemo) {
      contracts = getDemoData();
    } else {
      contracts = JSON.parse(appData);
    }
    const idx = contracts.findIndex((c) => c.id === id);
    if (idx !== -1) {
      contracts[idx].status = contracts[idx].status === 'active' ? 'closed' : 'active';
      localStorage.setItem('leaseApplications', JSON.stringify(contracts));
    }
    refreshStats();
  }

  if (action === 'delete') {
    if (isDemo) return;
    let contracts = JSON.parse(appData);
    contracts = contracts.filter((c) => c.id !== id);
    localStorage.setItem('leaseApplications', JSON.stringify(contracts));
    refreshStats();
  }
}

document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('search-input')?.addEventListener('input', refreshStats);
  document.getElementById('status-filter')?.addEventListener('change', refreshStats);
  document.querySelector('#contracts-table tbody')?.addEventListener('click', handleTableClick);

  refreshStats();
});
