'use client';

import { useEffect, useCallback } from 'react';
import { useAnalytics } from '@/hooks/useAnalytics';

interface AnalyticsTrackerProps {
  pageType?: 'blog' | 'product' | 'landing_page' | 'category';
  categoryName?: string;
  tagNames?: string[];
  keywords?: string[];
  productId?: string;
  enableHeatmap?: boolean;
}

export function AnalyticsTracker({
  pageType = 'landing_page',
  categoryName,
  tagNames,
  keywords,
  productId,
  enableHeatmap = false,
}: AnalyticsTrackerProps) {
  const { cachePrice } = useAnalytics({
    enableHeatmap,
    enableScrollTracking: true,
    enableFormTracking: true,
    pageData: {
      categoryName,
      tagNames,
      contentType: pageType,
      keywords,
    },
  });

  // Cache product price when available
  useEffect(() => {
    if (productId && typeof window !== 'undefined') {
      // Try to extract price from DOM
      const priceElement = document.querySelector('[data-price]');
      const stockElement = document.querySelector('[data-stock]');
      
      if (priceElement) {
        const price = parseInt(priceElement.getAttribute('data-price') || '0');
        const stock = stockElement?.getAttribute('data-stock') || 'in_stock';
        cachePrice(productId, price, stock);
      }
    }
  }, [productId, cachePrice]);

  return null; // This is a tracking-only component
}

// Button component for social sharing
interface SocialShareButtonProps {
  platform: 'facebook' | 'twitter' | 'pinterest' | 'linkedin' | 'email';
  url?: string;
  title?: string;
  children: React.ReactNode;
  className?: string;
}

export function SocialShareButton({
  platform,
  url,
  title,
  children,
  className,
}: SocialShareButtonProps) {
  const { trackSocialShare } = useAnalytics();

  const handleShare = useCallback(() => {
    const shareUrl = url || (typeof window !== 'undefined' ? window.location.href : '');
    const shareTitle = title || (typeof document !== 'undefined' ? document.title : '');
    
    let platformUrl = '';
    
    switch (platform) {
      case 'facebook':
        platformUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`;
        break;
      case 'twitter':
        platformUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(shareUrl)}&text=${encodeURIComponent(shareTitle)}`;
        break;
      case 'pinterest':
        platformUrl = `https://pinterest.com/pin/create/button/?url=${encodeURIComponent(shareUrl)}`;
        break;
      case 'linkedin':
        platformUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(shareUrl)}`;
        break;
      case 'email':
        platformUrl = `mailto:?subject=${encodeURIComponent(shareTitle)}&body=${encodeURIComponent(shareUrl)}`;
        break;
    }

    // Track the share
    trackSocialShare(platform, 'button');

    // Open share window
    if (platform !== 'email') {
      window.open(platformUrl, '_blank', 'width=600,height=400');
    } else {
      window.location.href = platformUrl;
    }
  }, [platform, url, title, trackSocialShare]);

  return (
    <button onClick={handleShare} className={className}>
      {children}
    </button>
  );
}

// Affiliate link wrapper
interface AffiliateLinkProps {
  linkId: string;
  productId?: string;
  href: string;
  children: React.ReactNode;
  className?: string;
  target?: string;
  rel?: string;
}

export function AffiliateLink({
  linkId,
  productId,
  href,
  children,
  className,
  target = '_blank',
  rel = 'noopener noreferrer sponsored',
}: AffiliateLinkProps) {
  return (
    <a
      href={href}
      data-link-id={linkId}
      data-product-id={productId}
      className={className}
      target={target}
      rel={rel}
    >
      {children}
    </a>
  );
}

// Price tracker component
interface PriceTrackerProps {
  productId: string;
  linkId: string;
  platform: string;
  price: number;
  originalPrice?: number;
  stockStatus?: 'in_stock' | 'out_of_stock' | 'low_stock';
}

export function PriceTracker({
  productId,
  linkId,
  platform,
  price,
  originalPrice,
  stockStatus = 'in_stock',
}: PriceTrackerProps) {
  const { cachePrice } = useAnalytics();

  useEffect(() => {
    cachePrice(productId, price, stockStatus);
    
    // Also send to backend
    const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3003';
    fetch(`${apiUrl}/analytics/track/price`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        productId,
        linkId,
        platform,
        price,
        originalPrice,
        stockStatus,
      }),
    }).catch(() => {
      // Silently fail for analytics
    });
  }, [productId, linkId, platform, price, originalPrice, stockStatus, cachePrice]);

  return null;
}

// Scroll depth indicator
export function ScrollDepthIndicator() {
  return (
    <div className="fixed top-0 left-0 h-1 bg-blue-500 z-50" id="scroll-depth-indicator" />
  );
}

// Form tracker wrapper
interface FormTrackerProps {
  formId?: string;
  formName?: string;
  children: React.ReactNode;
  onSubmit?: (e: React.FormEvent) => void;
}

export function FormTracker({
  formId,
  formName,
  children,
  onSubmit,
}: FormTrackerProps) {
  return (
    <form
      id={formId}
      name={formName}
      data-form-id={formId}
      data-form-name={formName}
      onSubmit={onSubmit}
    >
      {children}
    </form>
  );
}

export default AnalyticsTracker;
