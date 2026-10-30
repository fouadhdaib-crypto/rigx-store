document.addEventListener('DOMContentLoaded', () => {
  const tableBody = document.getElementById('orders-table-body');
  const searchInput = document.getElementById('orders-search');
  const filterButtons = document.querySelectorAll('[data-status-filter]');
  const exportButton = document.getElementById('orders-export');
  const count = document.getElementById('orders-result-count');
  const emptyRow = document.getElementById('orders-empty');

  if (!tableBody || !searchInput || !exportButton || !count || !emptyRow) return;

  const rows = Array.from(tableBody.querySelectorAll('[data-order-row]'));
  let activeStatus = 'all';

  const updateRows = () => {
    const query = searchInput.value.trim().toLocaleLowerCase();
    let visibleCount = 0;

    rows.forEach((row) => {
      const matchesStatus = activeStatus === 'all' || row.dataset.status === activeStatus;
      const matchesSearch = row.textContent.toLocaleLowerCase().includes(query);
      const visible = matchesStatus && matchesSearch;
      row.hidden = !visible;
      if (visible) visibleCount += 1;
    });

    emptyRow.hidden = visibleCount > 0;
    count.textContent = `Showing ${visibleCount} of ${rows.length} sample orders`;
  };

  filterButtons.forEach((button) => {
    button.addEventListener('click', () => {
      activeStatus = button.dataset.statusFilter || 'all';
      filterButtons.forEach((filterButton) => {
        const isActive = filterButton === button;
        filterButton.classList.toggle('active', isActive);
        filterButton.setAttribute('aria-pressed', String(isActive));
      });
      updateRows();
    });
  });

  searchInput.addEventListener('input', updateRows);

  exportButton.addEventListener('click', () => {
    const visibleRows = rows.filter((row) => !row.hidden);
    const csvRows = [
      ['Order', 'Customer', 'Email', 'Date', 'Items', 'Total', 'Payment', 'Payment method', 'Status'],
      ...visibleRows.map((row) => {
        const cells = row.querySelectorAll('td');
        return [
          cells[0].innerText.trim(),
          cells[1].querySelector('strong').innerText.trim(),
          cells[1].querySelector('small').innerText.trim(),
          cells[2].innerText.trim(),
          cells[3].innerText.trim(),
          cells[4].innerText.trim(),
          cells[5].querySelector('.orders-payment').innerText.trim(),
          cells[5].querySelector('.orders-method').innerText.trim(),
          cells[6].innerText.trim(),
        ];
      }),
    ];
    const csv = csvRows
      .map((values) => values.map((value) => `"${value.replaceAll('"', '""')}"`).join(','))
      .join('\r\n');
    const file = new Blob([`\uFEFF${csv}`], { type: 'text/csv;charset=utf-8' });
    const url = URL.createObjectURL(file);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'regix-orders.csv';
    document.body.append(link);
    link.click();
    link.remove();
    setTimeout(() => URL.revokeObjectURL(url), 1000);
  });
});
