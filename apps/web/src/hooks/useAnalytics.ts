'use client';

import { useEffect, useCallback, useRef } from 'react';
import { initTracker, getTracker } from '@/lib/tracker';
import type AffiliateTracker from '@/lib/tracker';

interface UseAnalyticsOptions {
  enableHeatmap?: boolean;
  enableScrollTracking?: boolean;
  enableFormTracking?: boolean;
  pageData?: {
    categoryName?: string;
    tagNames?: string[];
    contentType?: 'blog' | 'product' | 'landing_page' | 'category';
    keywords?: string[];
  };
}

export function useAnalytics(options: UseAnalyticsOptions = {}) {
  const trackerRef = useRef<AffiliateTracker | null>(null);

  useEffect(() => {
    const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3003';
    
    trackerRef.current = initTracker({
      apiUrl,
      enableHeatmap: options.enableHeatmap ?? false,
      enableScrollTracking: options.enableScrollTracking ?? true,
      enableFormTracking: options.enableFormTracking ?? true,
    });

    // Track page view
    trackerRef.current.trackPageView({
      url: window.location.href,
      referrer: document.referrer,
      title: document.title,
      ...options.pageData,
    });

    return () => {
      trackerRef.current?.destroy();
    };
  }, []);

  const trackLinkClick = useCallback((linkId: string, productId?: string) => {
    trackerRef.current?.trackLinkClick(linkId, productId ? { productId } : undefined);
  }, []);

  const trackConversion = useCallback((data: {
    orderValue: number;
    commission: number;
    linkId: string;
    productId: string;
    orderId?: string;
  }) => {
    trackerRef.current?.trackConversion(data);
  }, []);

  const trackSocialShare = useCallback((platform: string, shareType: 'button' | 'copy_link' | 'native' = 'button') => {
    trackerRef.current?.trackSocialShare(platform, shareType);
  }, []);

  const cachePrice = useCallback((productId: string, price: number, stock: string) => {
    trackerRef.current?.cachePrice(productId, price, stock);
  }, []);

  return {
    trackLinkClick,
    trackConversion,
    trackSocialShare,
    cachePrice,
    getSessionId: () => trackerRef.current?.getSessionId(),
  };
}

// Hook for scroll depth tracking
export function useScrollDepth(callback?: (depth: number) => void) {
  useEffect(() => {
    if (typeof window === 'undefined') return;

    let maxDepth = 0;
    let ticking = false;

    const calculateDepth = () => {
      const scrollTop = window.scrollY;
      const docHeight = document.documentElement.scrollHeight - window.innerHeight;
      const depth = docHeight > 0 ? Math.round((scrollTop / docHeight) * 100) : 0;
      
      if (depth > maxDepth) {
        maxDepth = depth;
        callback?.(depth);
      }
    };

    const onScroll = () => {
      if (!ticking) {
        window.requestAnimationFrame(() => {
          calculateDepth();
          ticking = false;
        });
        ticking = true;
      }
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    return () => window.removeEventListener('scroll', onScroll);
  }, [callback]);
}

// Hook for tracking affiliate link clicks
export function useAffiliateLinks() {
  const tracker = getTracker();

  useEffect(() => {
    if (typeof document === 'undefined') return;

    const handler = (e: MouseEvent) => {
      const target = e.target as HTMLElement;
      const link = target.closest('a[data-link-id]') as HTMLAnchorElement;
      
      if (link && tracker) {
        e.preventDefault();
        const linkId = link.dataset.linkId!;
        const productId = link.dataset.productId;
        tracker.trackLinkClick(linkId, productId ? { productId } : undefined);
        
        setTimeout(() => {
          window.open(link.href, link.target || '_self');
        }, 150);
      }
    };

    document.addEventListener('click', handler);
    return () => document.removeEventListener('click', handler);
  }, [tracker]);
}

// Hook for tracking time on page
export function useTimeOnPage() {
  const startTime = useRef<number>(Date.now());

  useEffect(() => {
    const handleBeforeUnload = () => {
      const timeOnPage = Math.round((Date.now() - startTime.current) / 1000);
      
      // Send via beacon API
      const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3003';
      const data = JSON.stringify({
        url: window.location.href,
        timeOnPage,
        timestamp: Date.now(),
      });
      
      if (navigator.sendBeacon) {
        navigator.sendBeacon(`${apiUrl}/analytics/track/time-on-page`, 
          new Blob([data], { type: 'application/json' }));
      }
    };

    window.addEventListener('beforeunload', handleBeforeUnload);
    return () => window.removeEventListener('beforeunload', handleBeforeUnload);
  }, []);

  return {
    getTimeOnPage: () => Math.round((Date.now() - startTime.current) / 1000),
  };
}

export default useAnalytics;
