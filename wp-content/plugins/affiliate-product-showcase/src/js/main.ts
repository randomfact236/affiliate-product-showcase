/**
 * Affiliate Product Showcase - Main JavaScript Entry
 * 
 * Single source of truth for all plugin JavaScript.
 * Handles interactions for all shortcodes: aps_product, aps_products, aps_showcase
 * 
 * @package AffiliateProductShowcase
 * @version 1.0.0
 */

import '../css/main.css';

declare global {
  interface Window {
    affiliateProductShowcase?: {
      ajaxurl: string;
      nonce: string;
      restUrl: string;
      restNonce: string;
    };
  }
}

/**
 * Product Card Interactions
 */
class ProductCards {
  constructor() {
    this.init();
  }

  private init(): void {
    this.initBookmarks();
    this.initLazyLoading();
  }

  /**
   * Initialize bookmark buttons
   */
  private initBookmarks(): void {
    document.querySelectorAll('.aps-bookmark').forEach((btn) => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        
        const button = e.currentTarget as HTMLElement;
        const icon = button.querySelector('.aps-bookmark__icon');
        
        // Toggle bookmark state
        button.classList.toggle('aps-bookmark--active');
        
        if (icon) {
          const isBookmarked = button.classList.contains('aps-bookmark--active');
          icon.setAttribute('fill', isBookmarked ? 'currentColor' : 'none');
        }
        
        // TODO: Send bookmark state to server
        this.trackEvent('bookmark_toggle', {
          productId: button.closest('.aps-card')?.getAttribute('data-product-id'),
          state: button.classList.contains('aps-bookmark--active'),
        });
      });
    });
  }

  /**
   * Initialize lazy loading for images
   */
  private initLazyLoading(): void {
    if ('IntersectionObserver' in window) {
      const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            const img = entry.target as HTMLImageElement;
            if (img.dataset.src) {
              img.src = img.dataset.src;
              img.removeAttribute('data-src');
              img.classList.add('aps-loaded');
              imageObserver.unobserve(img);
            }
          }
        });
      }, {
        rootMargin: '50px 0px',
        threshold: 0.01,
      });

      document.querySelectorAll('img[data-src]').forEach((img) => {
        imageObserver.observe(img);
      });
    }
  }

  /**
   * Track custom events
   */
  private trackEvent(eventName: string, data: Record<string, unknown>): void {
    // Send to WordPress REST API or admin-ajax
    if (window.affiliateProductShowcase?.ajaxurl) {
      fetch(window.affiliateProductShowcase.ajaxurl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          action: 'aps_track_event',
          event: eventName,
          data: JSON.stringify(data),
          nonce: window.affiliateProductShowcase.nonce || '',
        }),
      }).catch(console.error);
    }
  }
}

/**
 * Product Showcase (Filter & Sort)
 */
class ProductShowcase {
  private container: HTMLElement | null;
  private filterButtons: NodeListOf<HTMLElement>;
  private tagButtons: NodeListOf<HTMLElement>;
  private clearButton: HTMLElement | null;
  private sortButton: HTMLElement | null;

  constructor() {
    this.container = document.querySelector('.aps-showcase');
    
    if (!this.container) {
      return;
    }

    this.filterButtons = this.container.querySelectorAll('[data-category]');
    this.tagButtons = this.container.querySelectorAll('[data-tag]');
    this.clearButton = this.container.querySelector('#clearAll');
    this.sortButton = this.container.querySelector('.sort-btn');

    this.init();
  }

  private init(): void {
    this.initCategoryFilters();
    this.initTagFilters();
    this.initClearButton();
    this.initSortButton();
  }

  /**
   * Initialize category filter buttons
   */
  private initCategoryFilters(): void {
    this.filterButtons.forEach((btn) => {
      btn.addEventListener('click', () => {
        const category = btn.getAttribute('data-category');
        
        // Update active state
        this.filterButtons.forEach((b) => b.classList.remove('aps-filter-btn--active', 'tab-active'));
        btn.classList.add('aps-filter-btn--active', 'tab-active');
        
        // Filter products
        this.filterProducts({ category });
        
        // Track event
        this.trackEvent('filter_category', { category });
      });
    });
  }

  /**
   * Initialize tag filter buttons
   */
  private initTagFilters(): void {
    this.tagButtons.forEach((btn) => {
      btn.addEventListener('click', () => {
        const tag = btn.getAttribute('data-tag');
        
        // Toggle active state
        btn.classList.toggle('tag-active');
        
        // Get all active tags
        const activeTags = Array.from(this.tagButtons)
          .filter((b) => b.classList.contains('tag-active'))
          .map((b) => b.getAttribute('data-tag'));
        
        // Filter products
        this.filterProducts({ tags: activeTags });
        
        // Track event
        this.trackEvent('filter_tag', { tag, activeTags });
      });
    });
  }

  /**
   * Initialize clear all button
   */
  private initClearButton(): void {
    this.clearButton?.addEventListener('click', () => {
      // Reset category filters
      this.filterButtons.forEach((b) => b.classList.remove('aps-filter-btn--active', 'tab-active'));
      this.filterButtons[0]?.classList.add('aps-filter-btn--active', 'tab-active');
      
      // Reset tag filters
      this.tagButtons.forEach((b) => b.classList.remove('tag-active'));
      
      // Show all products
      this.filterProducts({ category: 'all', tags: [] });
      
      // Track event
      this.trackEvent('filter_clear');
    });
  }

  /**
   * Initialize sort dropdown
   */
  private initSortButton(): void {
    this.sortButton?.addEventListener('click', () => {
      // Toggle dropdown (simplified - you may want to use a proper dropdown component)
      const sortValue = this.sortButton?.querySelector('.sort-value');
      const currentSort = sortValue?.textContent || 'Featured';
      
      // Cycle through sort options
      const sorts = ['Featured', 'Price: Low to High', 'Price: High to Low', 'Rating'];
      const currentIndex = sorts.indexOf(currentSort);
      const nextSort = sorts[(currentIndex + 1) % sorts.length];
      
      if (sortValue) {
        sortValue.textContent = nextSort;
      }
      
      // Sort products
      this.sortProducts(nextSort);
      
      // Track event
      this.trackEvent('sort_change', { sort: nextSort });
    });
  }

  /**
   * Filter products based on criteria
   */
  private filterProducts(filters: { category?: string | null; tags?: string[] }): void {
    const cards = this.container?.querySelectorAll('.tool-card, .aps-card');
    
    cards?.forEach((card) => {
      const cardEl = card as HTMLElement;
      const cardCategory = cardEl.getAttribute('data-category');
      const cardTags = (cardEl.getAttribute('data-tags') || '').split(',');
      
      let visible = true;
      
      // Check category
      if (filters.category && filters.category !== 'all') {
        visible = visible && cardCategory === filters.category;
      }
      
      // Check tags
      if (filters.tags && filters.tags.length > 0) {
        visible = visible && filters.tags.some((tag) => cardTags.includes(tag));
      }
      
      // Show/hide card
      cardEl.style.display = visible ? '' : 'none';
    });
  }

  /**
   * Sort products
   */
  private sortProducts(sortBy: string): void {
    const grid = this.container?.querySelector('.aps-grid-products');
    if (!grid) return;
    
    const cards = Array.from(grid.querySelectorAll('.tool-card, .aps-card'));
    
    cards.sort((a, b) => {
      const aEl = a as HTMLElement;
      const bEl = b as HTMLElement;
      
      switch (sortBy) {
        case 'Price: Low to High':
          return this.getPrice(aEl) - this.getPrice(bEl);
        case 'Price: High to Low':
          return this.getPrice(bEl) - this.getPrice(aEl);
        case 'Rating':
          return this.getRating(bEl) - this.getRating(aEl);
        default:
          return 0;
      }
    });
    
    // Re-append in new order
    cards.forEach((card) => grid.appendChild(card));
  }

  /**
   * Get price from card element
   */
  private getPrice(card: HTMLElement): number {
    const priceEl = card.querySelector('.aps-price--current');
    const priceText = priceEl?.textContent || '0';
    return parseFloat(priceText.replace(/[^0-9.]/g, ''));
  }

  /**
   * Get rating from card element
   */
  private getRating(card: HTMLElement): number {
    const ratingEl = card.querySelector('.aps-rating__value');
    const ratingText = ratingEl?.textContent || '0';
    return parseFloat(ratingText.replace(/[^0-9.]/g, ''));
  }

  /**
   * Track custom events
   */
  private trackEvent(eventName: string, data?: Record<string, unknown>): void {
    if (window.affiliateProductShowcase?.ajaxurl) {
      fetch(window.affiliateProductShowcase.ajaxurl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          action: 'aps_track_event',
          event: eventName,
          data: JSON.stringify(data || {}),
          nonce: window.affiliateProductShowcase.nonce || '',
        }),
      }).catch(console.error);
    }
  }
}

/**
 * Affiliate Link Tracking
 */
class AffiliateTracking {
  constructor() {
    this.init();
  }

  private init(): void {
    document.querySelectorAll('a[href*="affiliate"], .aps-btn').forEach((link) => {
      link.addEventListener('click', (e) => {
        const anchor = e.currentTarget as HTMLAnchorElement;
        const productId = anchor.closest('.aps-card')?.getAttribute('data-product-id');
        
        this.trackClick({
          productId,
          href: anchor.href,
          timestamp: new Date().toISOString(),
        });
      });
    });
  }

  private trackClick(data: Record<string, unknown>): void {
    // Send to tracking endpoint
    if (window.affiliateProductShowcase?.ajaxurl) {
      fetch(window.affiliateProductShowcase.ajaxurl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          action: 'aps_track_click',
          data: JSON.stringify(data),
          nonce: window.affiliateProductShowcase.nonce || '',
        }),
      }).catch(console.error);
    }
  }
}

/**
 * Initialize all components when DOM is ready
 */
function init(): void {
  // Initialize product cards
  new ProductCards();
  
  // Initialize showcase (if present)
  new ProductShowcase();
  
  // Initialize affiliate tracking
  new AffiliateTracking();
}

// Run initialization
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}

// Also run on dynamic content updates (for AJAX-loaded content)
window.addEventListener('aps:contentUpdated', init);
