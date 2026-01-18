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
    <article 
      className="aps-card" 
      data-id={product.id}
      aria-labelledby={`product-title-${product.id}`}
    >
      {product.image_url && (
        <div className="aps-card__media">
          <img src={product.image_url} alt={product.title} loading="lazy" />
        </div>
      )}
      <div className="aps-card__body">
        <h3 id={`product-title-${product.id}`} className="aps-card__title">{product.title}</h3>
        {product.badge && <span className="aps-card__badge">{product.badge}</span>}
        {product.rating && (
          <span 
            className="aps-card__rating" 
            aria-label={`Rating: ${Number(product.rating).toFixed(1)} out of 5 stars`}
          >
            <span aria-hidden="true">★</span>
            {Number(product.rating).toFixed(1)}
          </span>
        )}
        <p className="aps-card__description">{product.description}</p>
        <div className="aps-card__footer">
          <span className="aps-card__price">
            <span className="aps-card__price-currency" aria-label="Currency">{product.currency}</span>
            <span className="aps-card__price-value" aria-label="Price">{Number(product.price).toFixed(2)}</span>
          </span>
          <button 
            type="button" 
            className="aps-card__cta" 
            onClick={() => onSelect?.(product)}
            aria-label={`View deal for ${product.title}`}
          >
            <span aria-hidden="true">View Deal</span>
          </button>
        </div>
      </div>
    </article>
  );
}
