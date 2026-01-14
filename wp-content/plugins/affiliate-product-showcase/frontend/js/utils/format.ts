export function formatPrice(amount: number | string, currency: string = 'USD'): string {
  const formatted = Number(amount || 0).toFixed(2);
  const symbols: { [key: string]: string } = { USD: '$', EUR: '€', GBP: '£' };
  return `${symbols[currency] || currency} ${formatted}`.trim();
}
