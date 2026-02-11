/**
 * Device Detection Utility
 * Detects device type, browser, OS, screen, and capabilities
 */

export interface DeviceInfo {
  // Device classification
  deviceType: 'mobile' | 'tablet' | 'desktop' | 'smarttv' | 'wearable';
  isTouch: boolean;
  isMobile: boolean;
  isTablet: boolean;
  isDesktop: boolean;
  
  // Browser info
  browser: string;
  browserVersion: string;
  browserEngine: string;
  
  // OS info
  os: string;
  osVersion: string;
  platform: string;
  
  // Screen/Display
  screenWidth: number;
  screenHeight: number;
  screenRatio: number;
  viewportWidth: number;
  viewportHeight: number;
  devicePixelRatio: number;
  colorDepth: number;
  orientation: 'portrait' | 'landscape';
  
  // Capabilities
  language: string;
  languages: string[];
  timezone: string;
  timezoneOffset: number;
  cookieEnabled: boolean;
  localStorageEnabled: boolean;
  sessionStorageEnabled: boolean;
  indexedDBEnabled: boolean;
  webglEnabled: boolean;
  canvasEnabled: boolean;
  
  // Connection
  connectionType?: '4g' | '3g' | '2g' | 'wifi' | 'ethernet' | 'unknown';
  connectionSpeed?: 'slow-2g' | '2g' | '3g' | '4g';
  downlink?: number;
  rtt?: number;
  saveData?: boolean;
  
  // Features
  webpSupport: boolean;
  avifSupport: boolean;
  serviceWorker: boolean;
  pushNotifications: boolean;
  
  // User preferences
  colorScheme: 'light' | 'dark' | 'no-preference';
  reducedMotion: boolean;
  prefersContrast: 'high' | 'low' | 'no-preference';
}

class DeviceDetector {
  private static instance: DeviceDetector;
  private cachedInfo: DeviceInfo | null = null;

  static getInstance(): DeviceDetector {
    if (!DeviceDetector.instance) {
      DeviceDetector.instance = new DeviceDetector();
    }
    return DeviceDetector.instance;
  }

  detect(): DeviceInfo {
    if (this.cachedInfo) return this.cachedInfo;

    const ua = navigator.userAgent;
    const uaLower = ua.toLowerCase();

    this.cachedInfo = {
      // Device classification
      deviceType: this.getDeviceType(ua),
      isTouch: this.isTouchDevice(),
      isMobile: this.isMobile(ua),
      isTablet: this.isTablet(ua),
      isDesktop: !this.isMobile(ua) && !this.isTablet(ua),
      
      // Browser
      browser: this.getBrowser(ua),
      browserVersion: this.getBrowserVersion(ua),
      browserEngine: this.getBrowserEngine(),
      
      // OS
      os: this.getOS(ua),
      osVersion: this.getOSVersion(ua),
      platform: navigator.platform,
      
      // Screen
      screenWidth: window.screen.width,
      screenHeight: window.screen.height,
      screenRatio: window.screen.width / window.screen.height,
      viewportWidth: window.innerWidth,
      viewportHeight: window.innerHeight,
      devicePixelRatio: window.devicePixelRatio || 1,
      colorDepth: window.screen.colorDepth,
      orientation: this.getOrientation(),
      
      // Capabilities
      language: navigator.language,
      languages: Array.from(navigator.languages || []),
      timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
      timezoneOffset: new Date().getTimezoneOffset(),
      cookieEnabled: navigator.cookieEnabled,
      localStorageEnabled: this.isLocalStorageEnabled(),
      sessionStorageEnabled: this.isSessionStorageEnabled(),
      indexedDBEnabled: !!window.indexedDB,
      webglEnabled: this.isWebGLEnabled(),
      canvasEnabled: this.isCanvasEnabled(),
      
      // Connection
      ...this.getConnectionInfo(),
      
      // Features
      webpSupport: false, // Will be detected async
      avifSupport: false,
      serviceWorker: 'serviceWorker' in navigator,
      pushNotifications: 'PushManager' in window,
      
      // Preferences
      colorScheme: this.getColorScheme(),
      reducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
      prefersContrast: this.getContrastPreference(),
    };

    // Async image format detection
    this.detectImageFormats().then(formats => {
      if (this.cachedInfo) {
        this.cachedInfo.webpSupport = formats.webp;
        this.cachedInfo.avifSupport = formats.avif;
      }
    });

    return this.cachedInfo;
  }

  private getDeviceType(ua: string): 'mobile' | 'tablet' | 'desktop' | 'smarttv' | 'wearable' {
    const uaLower = ua.toLowerCase();
    
    // Smart TV
    if (/smart-tv|smarttv|googletv|appletv|hbbtv|pov_tv|netcast.tv/.test(uaLower)) {
      return 'smarttv';
    }
    
    // Wearable
    if (/watch|wearable|glass/.test(uaLower) && !/watchos/.test(uaLower)) {
      return 'wearable';
    }
    
    // Tablet
    if (this.isTablet(ua)) {
      return 'tablet';
    }
    
    // Mobile
    if (this.isMobile(ua)) {
      return 'mobile';
    }
    
    return 'desktop';
  }

  private isMobile(ua: string): boolean {
    const mobileRegex = /mobile|iphone|ipod|android.*mobile|windows phone|blackberry|opera mini|iemobile/i;
    const notTablet = !/ipad|android(?!.*mobile)|tablet/i.test(ua);
    return mobileRegex.test(ua) && notTablet;
  }

  private isTablet(ua: string): boolean {
    return /ipad|android(?!.*mobile)|tablet|kindle|silk/i.test(ua.toLowerCase());
  }

  private isTouchDevice(): boolean {
    return 'ontouchstart' in window || navigator.maxTouchPoints > 0;
  }

  private getBrowser(ua: string): string {
    const uaLower = ua.toLowerCase();
    
    if (/edg/.test(uaLower)) return 'Edge';
    if (/opr|opera|opt/.test(uaLower)) return 'Opera';
    if (/samsungbrowser/.test(uaLower)) return 'Samsung Internet';
    if (/ucbrowser|ucweb/.test(uaLower)) return 'UC Browser';
    if (/firefox|fxios/.test(uaLower)) return 'Firefox';
    if (/safari|applewebkit/.test(uaLower) && !/chrome|chromium|crios/.test(uaLower)) return 'Safari';
    if (/chrome|chromium|crios/.test(uaLower)) return 'Chrome';
    if (/msie|trident/.test(uaLower)) return 'Internet Explorer';
    
    return 'Unknown';
  }

  private getBrowserVersion(ua: string): string {
    const matchers: [RegExp, number][] = [
      [/edg\/([\d.]+)/i, 1],
      [/opr\/([\d.]+)/i, 1],
      [/samsungbrowser\/([\d.]+)/i, 1],
      [/ucbrowser\/([\d.]+)/i, 1],
      [/firefox\/([\d.]+)/i, 1],
      [/fxios\/([\d.]+)/i, 1],
      [/chrome\/([\d.]+)/i, 1],
      [/crios\/([\d.]+)/i, 1],
      [/safari\/([\d.]+)/i, 1],
      [/version\/([\d.]+)/i, 1],
      [/msie ([\d.]+)/i, 1],
      [/trident.*rv:([\d.]+)/i, 1],
    ];

    for (const [regex, group] of matchers) {
      const match = ua.match(regex);
      if (match) return match[group];
    }

    return 'unknown';
  }

  private getBrowserEngine(): string {
    const ua = navigator.userAgent;
    if (/blink/.test(ua.toLowerCase())) return 'Blink';
    if (/webkit/.test(ua.toLowerCase())) return 'WebKit';
    if (/gecko/.test(ua.toLowerCase()) && /firefox/.test(ua.toLowerCase())) return 'Gecko';
    if (/trident/.test(ua.toLowerCase())) return 'Trident';
    return 'Unknown';
  }

  private getOS(ua: string): string {
    const uaLower = ua.toLowerCase();
    
    if (/windows nt/.test(uaLower)) return 'Windows';
    if (/macintosh|mac os x/.test(uaLower)) return 'macOS';
    if (/iphone|ipad|ipod/.test(uaLower)) return 'iOS';
    if (/android/.test(uaLower)) return 'Android';
    if (/linux/.test(uaLower)) return 'Linux';
    if (/cros/.test(uaLower)) return 'Chrome OS';
    if (/playstation/.test(uaLower)) return 'PlayStation';
    if (/xbox/.test(uaLower)) return 'Xbox';
    if (/nintendo/.test(uaLower)) return 'Nintendo';
    
    return 'Unknown';
  }

  private getOSVersion(ua: string): string {
    const matchers: [RegExp, number][] = [
      [/windows nt ([\d.]+)/i, 1],
      [/mac os x ([\d_]+)/i, 1],
      [/os (\d+)[._](\d+)(?:[._](\d+))?/i, 1],
      [/android ([\d.]+)/i, 1],
      [/chrome\/[\d.]+.*os\/([\d.]+)/i, 1],
    ];

    for (const [regex, group] of matchers) {
      const match = ua.match(regex);
      if (match) {
        return match[group].replace(/_/g, '.');
      }
    }

    return 'unknown';
  }

  private getOrientation(): 'portrait' | 'landscape' {
    if (window.screen.orientation) {
      return window.screen.orientation.type.includes('portrait') ? 'portrait' : 'landscape';
    }
    return window.innerHeight > window.innerWidth ? 'portrait' : 'landscape';
  }

  private isLocalStorageEnabled(): boolean {
    try {
      const test = '__storage_test__';
      localStorage.setItem(test, test);
      localStorage.removeItem(test);
      return true;
    } catch (e) {
      return false;
    }
  }

  private isSessionStorageEnabled(): boolean {
    try {
      const test = '__storage_test__';
      sessionStorage.setItem(test, test);
      sessionStorage.removeItem(test);
      return true;
    } catch (e) {
      return false;
    }
  }

  private isWebGLEnabled(): boolean {
    try {
      const canvas = document.createElement('canvas');
      return !!(window.WebGLRenderingContext && 
        (canvas.getContext('webgl') || canvas.getContext('experimental-webgl')));
    } catch (e) {
      return false;
    }
  }

  private isCanvasEnabled(): boolean {
    try {
      const canvas = document.createElement('canvas');
      return !!(canvas.getContext && canvas.getContext('2d'));
    } catch (e) {
      return false;
    }
  }

  private getConnectionInfo() {
    const nav = navigator as any;
    const conn = nav.connection || nav.mozConnection || nav.webkitConnection;
    
    if (conn) {
      return {
        connectionType: conn.type || conn.effectiveType || 'unknown',
        connectionSpeed: conn.effectiveType,
        downlink: conn.downlink,
        rtt: conn.rtt,
        saveData: conn.saveData,
      };
    }

    return {};
  }

  private async detectImageFormats(): Promise<{ webp: boolean; avif: boolean }> {
    const results = { webp: false, avif: false };

    try {
      // WebP detection
      const webpCanvas = document.createElement('canvas');
      webpCanvas.width = 1;
      webpCanvas.height = 1;
      results.webp = webpCanvas.toDataURL('image/webp').indexOf('data:image/webp') === 0;
    } catch (e) {
      // WebP not supported
    }

    try {
      // AVIF detection
      const avifImage = new Image();
      avifImage.src = 'data:image/avif;base64,AAAAIGZ0eXBhdmlmAAAAAGF2aWZtaWYxbWlhZk1BMUIAAADybWV0YQAAAAAAAAAoaGRscgAAAAAAAAAAcGljdAAAAAAAAAAAAAAAAGxpYmF2aWYAAAAADnBpdG0AAAAAAAEAAAAeaWxvYwAAAABEAAABAAEAAAABAAABGgAAAB0AAAAoaWluZgAAAAAAAQAAABppbmZlAgAAAAABAABhdjAxQ29sb3IAAAAAamlwcnAAAABLaXBjbwAAABRpc3BlAAAAAAAAAAIAAAACAAAAEHBpeGkAAAAAAwgICAAAAAxhdjFDgQ0MAAAAABNjb2xybmNseAACAAIAAYAAAAAXaXBtYQAAAAAAAAABAAEEAQKDBAAAACVtZGF0EgAKCBgANogQEAwgMg8f8D///8WfhwB8+ErK42A=';
      await new Promise((resolve) => {
        avifImage.onload = () => { results.avif = true; resolve(true); };
        avifImage.onerror = () => resolve(false);
        setTimeout(() => resolve(false), 100);
      });
    } catch (e) {
      // AVIF not supported
    }

    return results;
  }

  private getColorScheme(): 'light' | 'dark' | 'no-preference' {
    if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
      return 'dark';
    }
    if (window.matchMedia('(prefers-color-scheme: light)').matches) {
      return 'light';
    }
    return 'no-preference';
  }

  private getContrastPreference(): 'high' | 'low' | 'no-preference' {
    if (window.matchMedia('(prefers-contrast: high)').matches) {
      return 'high';
    }
    if (window.matchMedia('(prefers-contrast: low)').matches) {
      return 'low';
    }
    return 'no-preference';
  }

  // Utility to get fingerprint
  getFingerprint(): string {
    const info = this.detect();
    const components = [
      info.browser,
      info.browserVersion,
      info.os,
      info.osVersion,
      info.screenWidth,
      info.screenHeight,
      info.colorDepth,
      info.timezone,
      navigator.userAgent.slice(-50),
    ];
    
    // Simple hash
    let hash = 0;
    const str = components.join('|');
    for (let i = 0; i < str.length; i++) {
      const char = str.charCodeAt(i);
      hash = ((hash << 5) - hash) + char;
      hash = hash & hash;
    }
    return Math.abs(hash).toString(16);
  }
}

export const deviceDetector = DeviceDetector.getInstance();
export default deviceDetector;
