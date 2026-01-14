import React from 'react';

export interface Product {
  id: number;
  title: string;
  description?: string;
  image_url?: string;
  badge?: string;
  rating?: number;
  price: number;
  currency?: string;
  affiliate_url?: string;
}

interface Props {
  product?: Product | null;
  onSelect?: (product: Product) => void;
}

export default function ProductCard({ product, onSelect }: Props) {
  if (!product) return null;

  return (
    <article className="aps-card" data-id={product.id}>
      {product.image_url && (
        <div className="aps-card__media">
          <img src={product.image_url} alt={product.title} loading="lazy" />
        </div>
      )}
      <div className="aps-card__body">
        <h3 className="aps-card__title">{product.title}</h3>
        {product.badge && <span className="aps-card__badge">{product.badge}</span>}
        {product.rating && (
          <span className="aps-card__rating">★ {Number(product.rating).toFixed(1)}</span>
        )}
        <p className="aps-card__description">{product.description}</p>
        <div className="aps-card__footer">
          <span className="aps-card__price">
            {product.currency} {Number(product.price).toFixed(2)}
          </span>
          <button type="button" className="aps-card__cta" onClick={() => onSelect?.(product)}>
            View Deal
          </button>
        </div>
      </div>
    </article>
  );
}
