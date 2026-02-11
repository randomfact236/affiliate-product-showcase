/**
 * Session Manager
 * Handles session persistence, cookies, and UTM tracking
 */

import { deviceDetector, DeviceInfo } from './device-detector';

export interface SessionData {
  sessionId: string;
  userId?: string;
  visitorId: string;
  firstVisit: number;
  visitCount: number;
  lastVisit: number;
  isNewVisitor: boolean;
  isReturning: boolean;
  referrer?: string;
  landingPage: string;
  utmParams: UTMParams;
  deviceInfo: DeviceInfo;
  pagesViewed: string[];
}

export interface UTMParams {
  source?: string;
  medium?: string;
  campaign?: string;
  content?: string;
  term?: string;
}

const SESSION_KEY = '__affiliate_session__';
const VISITOR_KEY = '__affiliate_visitor__';
const SESSION_TIMEOUT = 30 * 60 * 1000; // 30 minutes

class SessionManager {
  private static instance: SessionManager;
  private currentSession: SessionData | null = null;

  static getInstance(): SessionManager {
    if (!SessionManager.instance) {
      SessionManager.instance = new SessionManager();
    }
    return SessionManager.instance;
  }

  init(): SessionData {
    if (this.currentSession) return this.currentSession;

    const visitorId = this.getOrCreateVisitorId();
    const existingSession = this.getExistingSession();
    const utmParams = this.extractUTMParams();
    const deviceInfo = deviceDetector.detect();

    if (existingSession && !this.isSessionExpired(existingSession)) {
      // Continue existing session
      existingSession.lastVisit = Date.now();
      existingSession.isNewVisitor = false;
      this.currentSession = existingSession;
    } else {
      // Create new session
      const visitorData = this.getVisitorData(visitorId);
      const isFirstVisit = visitorData.visitCount === 0;
      
      this.currentSession = {
        sessionId: this.generateId(),
        visitorId,
        userId: this.getUserId(),
        firstVisit: visitorData.firstVisit,
        visitCount: visitorData.visitCount + 1,
        lastVisit: Date.now(),
        isNewVisitor: isFirstVisit,
        isReturning: !isFirstVisit,
        referrer: document.referrer || undefined,
        landingPage: window.location.href,
        utmParams,
        deviceInfo,
        pagesViewed: [window.location.pathname],
      };

      // Update visitor data
      this.updateVisitorData(visitorId, {
        visitCount: this.currentSession.visitCount,
        lastVisit: Date.now(),
      });
    }

    // Store session
    this.storeSession(this.currentSession);
    
    return this.currentSession;
  }

  getSession(): SessionData | null {
    return this.currentSession;
  }

  updateSession(updates: Partial<SessionData>) {
    if (this.currentSession) {
      this.currentSession = { ...this.currentSession, ...updates };
      this.storeSession(this.currentSession);
    }
  }

  addPageView(path: string) {
    if (this.currentSession) {
      this.currentSession.pagesViewed.push(path);
      this.currentSession.lastVisit = Date.now();
      this.storeSession(this.currentSession);
    }
  }

  endSession() {
    if (this.currentSession) {
      this.storeSession({ ...this.currentSession, lastVisit: Date.now() });
      this.currentSession = null;
      this.clearSessionStorage();
    }
  }

  private getOrCreateVisitorId(): string {
    let visitorId = this.getCookie(VISITOR_KEY) || this.getFromStorage(VISITOR_KEY);
    
    if (!visitorId) {
      visitorId = this.generateId();
      this.setCookie(VISITOR_KEY, visitorId, 365); // 1 year
      this.setInStorage(VISITOR_KEY, visitorId);
      
      // Initialize visitor data
      this.updateVisitorData(visitorId, {
        firstVisit: Date.now(),
        visitCount: 0,
        lastVisit: Date.now(),
      });
    }
    
    return visitorId;
  }

  private getVisitorData(visitorId: string) {
    const data = this.getFromStorage(`${VISITOR_KEY}_${visitorId}`);
    if (data) {
      return JSON.parse(data);
    }
    return {
      firstVisit: Date.now(),
      visitCount: 0,
      lastVisit: Date.now(),
    };
  }

  private updateVisitorData(visitorId: string, data: any) {
    const existing = this.getVisitorData(visitorId);
    this.setInStorage(`${VISITOR_KEY}_${visitorId}`, JSON.stringify({
      ...existing,
      ...data,
    }));
  }

  private getExistingSession(): SessionData | null {
    const sessionData = this.getFromStorage(SESSION_KEY);
    if (sessionData) {
      try {
        return JSON.parse(sessionData);
      } catch (e) {
        return null;
      }
    }
    return null;
  }

  private isSessionExpired(session: SessionData): boolean {
    return Date.now() - session.lastVisit > SESSION_TIMEOUT;
  }

  private storeSession(session: SessionData) {
    this.setInStorage(SESSION_KEY, JSON.stringify(session));
  }

  private clearSessionStorage() {
    try {
      localStorage.removeItem(SESSION_KEY);
      sessionStorage.removeItem(SESSION_KEY);
    } catch (e) {
      // Storage not available
    }
  }

  private getUserId(): string | undefined {
    // Check for logged in user
    try {
      const userData = localStorage.getItem('user');
      if (userData) {
        const user = JSON.parse(userData);
        return user.id;
      }
    } catch (e) {
      // No user data
    }
    return undefined;
  }

  private extractUTMParams(): UTMParams {
    const url = new URL(window.location.href);
    return {
      source: url.searchParams.get('utm_source') || undefined,
      medium: url.searchParams.get('utm_medium') || undefined,
      campaign: url.searchParams.get('utm_campaign') || undefined,
      content: url.searchParams.get('utm_content') || undefined,
      term: url.searchParams.get('utm_term') || undefined,
    };
  }

  private generateId(): string {
    return `${Date.now().toString(36)}-${Math.random().toString(36).substr(2, 9)}`;
  }

  // Cookie helpers
  private setCookie(name: string, value: string, days: number) {
    const expires = new Date(Date.now() + days * 24 * 60 * 60 * 1000).toUTCString();
    document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/; SameSite=Lax`;
  }

  private getCookie(name: string): string | null {
    const match = document.cookie.match(new RegExp(`(^| )${name}=([^;]+)`));
    return match ? decodeURIComponent(match[2]) : null;
  }

  // Storage helpers (try localStorage, fallback to memory)
  private setInStorage(key: string, value: string) {
    try {
      localStorage.setItem(key, value);
    } catch (e) {
      try {
        sessionStorage.setItem(key, value);
      } catch (e) {
        // Storage not available, keep in memory only
      }
    }
  }

  private getFromStorage(key: string): string | null {
    try {
      return localStorage.getItem(key) || sessionStorage.getItem(key);
    } catch (e) {
      return null;
    }
  }

  // Get attribution info
  getAttribution(): {
    source: string;
    medium: string;
    campaign?: string;
    referrer?: string;
    isDirect: boolean;
    isOrganic: boolean;
    isPaid: boolean;
    isSocial: boolean;
    isEmail: boolean;
  } {
    const session = this.currentSession;
    const utm = session?.utmParams || {};
    const referrer = session?.referrer;

    // Determine source
    let source = utm.source || 'direct';
    let medium = utm.medium || 'none';

    if (!utm.source && referrer) {
      const refUrl = new URL(referrer);
      const refHost = refUrl.hostname.toLowerCase();

      if (refHost.includes('google')) { source = 'google'; medium = 'organic'; }
      else if (refHost.includes('bing')) { source = 'bing'; medium = 'organic'; }
      else if (refHost.includes('yahoo')) { source = 'yahoo'; medium = 'organic'; }
      else if (refHost.includes('duckduckgo')) { source = 'duckduckgo'; medium = 'organic'; }
      else if (refHost.includes('facebook') || refHost.includes('fb.com')) { source = 'facebook'; medium = 'social'; }
      else if (refHost.includes('twitter') || refHost.includes('t.co')) { source = 'twitter'; medium = 'social'; }
      else if (refHost.includes('linkedin')) { source = 'linkedin'; medium = 'social'; }
      else if (refHost.includes('pinterest')) { source = 'pinterest'; medium = 'social'; }
      else if (refHost.includes('instagram')) { source = 'instagram'; medium = 'social'; }
      else if (refHost.includes('reddit')) { source = 'reddit'; medium = 'social'; }
      else if (refHost.includes('youtube')) { source = 'youtube'; medium = 'social'; }
      else { source = 'referral'; medium = 'referral'; }
    }

    return {
      source,
      medium,
      campaign: utm.campaign,
      referrer,
      isDirect: source === 'direct',
      isOrganic: medium === 'organic',
      isPaid: medium === 'cpc' || medium === 'ppc' || medium === 'paid',
      isSocial: medium === 'social',
      isEmail: medium === 'email',
    };
  }
}

export const sessionManager = SessionManager.getInstance();
export default sessionManager;
