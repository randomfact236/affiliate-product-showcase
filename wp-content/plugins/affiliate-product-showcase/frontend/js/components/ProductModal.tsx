import React from 'react';

interface Product {
  id: number;
  title: string;
  description?: string;
  affiliate_url?: string;
}

interface Props {
  product?: Product | null;
  onClose?: () => void;
}

export default function ProductModal({ product, onClose }: Props) {
  if (!product) return null;

  const handleOverlayKeyDown = (e: React.KeyboardEvent) => {
    if (e.key === 'Enter' || e.key === ' ' || e.key === 'Escape') {
      onClose?.();
    }
  };

  return (
    <div className="aps-modal" role="dialog" aria-modal="true">
      <div 
        className="aps-modal__overlay" 
        onClick={onClose}
        onKeyDown={handleOverlayKeyDown}
        role="button"
        tabIndex={0}
        aria-label="Close modal"
      />
      <div className="aps-modal__content">
        <button className="aps-modal__close" onClick={onClose} aria-label="Close">
          ×
        </button>
        <div className="aps-modal__body">
          <h2>{product.title}</h2>
          <p>{product.description}</p>
          <a
            className="aps-modal__cta"
            href={product.affiliate_url}
            target="_blank"
            rel="nofollow noreferrer"
          >
            View Deal
          </a>
        </div>
      </div>
    </div>
  );
}
