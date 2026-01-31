import React, { useEffect, useRef } from 'react';

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
  const modalRef = useRef<HTMLDivElement>(null);
  const triggerRef = useRef<HTMLElement | null>(document.activeElement as HTMLElement);

  const handleOverlayKeyDown = (e: React.KeyboardEvent) => {
    if (e.key === 'Enter' || e.key === ' ' || e.key === 'Escape') {
      onClose?.();
    }
  };

  useEffect(() => {
    if (!product) return;
    const trigger = triggerRef.current;

    // Focus modal when opened
    const focusableElements = modalRef.current?.querySelectorAll(
      'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    const firstFocusable = focusableElements?.[0] as HTMLElement;
    firstFocusable?.focus();

    // Trap focus within modal
    const handleTab = (e: KeyboardEvent) => {
      if (e.key !== 'Tab') return;
      
      const focusable = Array.from(focusableElements || []) as HTMLElement[];
      const first = focusable[0];
      const last = focusable[focusable.length - 1];

      if (e.shiftKey && document.activeElement === first) {
        e.preventDefault();
        last?.focus();
      } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault();
        first?.focus();
      }
    };

    document.addEventListener('keydown', handleTab);

    return () => {
      document.removeEventListener('keydown', handleTab);
      // Return focus to trigger
      trigger?.focus();
    };
  }, [product]);

  if (!product) return null;

  return (
    <div 
      className="aps-modal" 
      role="dialog" 
      aria-modal="true"
      aria-labelledby={`modal-title-${product.id}`}
      aria-describedby={`modal-desc-${product.id}`}
    >
      <div 
        className="aps-modal__overlay" 
        onClick={onClose}
        onKeyDown={handleOverlayKeyDown}
        role="button"
        tabIndex={0}
        aria-label="Close modal"
      />
      <div ref={modalRef} className="aps-modal__content">
        <button 
          className="aps-modal__close" 
          onClick={onClose} 
          aria-label="Close modal"
        >
          <span aria-hidden="true">&times;</span>
        </button>
        <div id={`modal-desc-${product.id}`} className="aps-modal__body">
          <h2 id={`modal-title-${product.id}`}>{product.title}</h2>
          <p>{product.description}</p>
          <a
            className="aps-modal__cta"
            href={product.affiliate_url}
            target="_blank"
            rel="nofollow noreferrer"
            aria-label={`View deal for ${product.title} (opens in new tab)`}
          >
            View Deal
            <span className="sr-only">(opens in new tab)</span>
          </a>
        </div>
      </div>
    </div>
  );
}
