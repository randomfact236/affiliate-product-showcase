export function formatPrice(amount, currency = 'USD') {
  const formatted = Number(amount || 0).toFixed(2);
  const symbols = { USD: '$', EUR: '€', GBP: '£' };
  return `${symbols[currency] || currency} ${formatted}`.trim();
}
