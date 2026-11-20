'use strict';
document.querySelectorAll('form[data-confirm]').forEach(form => {
  form.addEventListener('submit', event => {
    if (!window.confirm(form.dataset.confirm)) event.preventDefault();
  });
});
document.querySelectorAll('input[data-preview]').forEach(input => {
  let previousUrl;
  input.addEventListener('change', () => {
    if (previousUrl) URL.revokeObjectURL(previousUrl);
    const file = input.files[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024 || !['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
      input.setCustomValidity('Choose a JPEG, PNG, or WebP image up to 2 MB.');
      input.reportValidity();
      return;
    }
    input.setCustomValidity('');
    previousUrl = URL.createObjectURL(file);
    document.getElementById(input.dataset.preview).src = previousUrl;
  });
});
const productSelect = document.getElementById('sale-product');
const quantityInput = document.getElementById('sale-quantity');
if (productSelect && quantityInput) {
  const pesos = new Intl.NumberFormat('en-PH', {style: 'currency', currency: 'PHP'});
  function updateSale() {
    const option = productSelect.selectedOptions[0];
    const stock = Number(option.dataset.stock || 0);
    const price = Number(option.dataset.price || 0);
    const quantity = Number(quantityInput.value || 0);
    quantityInput.max = stock > 0 ? Math.min(stock, 1000000) : 1000000;
    document.getElementById('stock-note').textContent = option.value ? stock + ' unit(s) currently available.' : 'Select a product to see available stock.';
    document.getElementById('sale-price').textContent = option.value ? pesos.format(price) : '—';
    document.getElementById('sale-total').textContent = pesos.format(price * quantity);
  }
  productSelect.addEventListener('change', updateSale);
  quantityInput.addEventListener('input', updateSale);
  updateSale();
}
