# Phase 5: Frontend Foundation

**Duration**: 2 weeks  
**Goal**: Frontend shell, auth UI, and basic pages working  
**Prerequisites**: Phase 4 complete (Backend APIs ready)

---

## Week 1: Setup & UI Foundation

### Day 1-2: Next.js Project Setup

#### Tasks
- [ ] Initialize Next.js 15 with App Router
- [ ] Configure Tailwind CSS
- [ ] Set up shadcn/ui
- [ ] Configure TypeScript paths

```bash
# Initialize project
cd apps/web
npx create-next-app@latest . --typescript --tailwind --eslint --app --src-dir --import-alias "@/*"

# Initialize shadcn
npx shadcn-ui@latest init
```

#### Directory Structure
```
apps/web/src/
├── app/
│   ├── (auth)/
│   │   ├── login/
│   │   ├── register/
│   │   └── forgot-password/
│   ├── (marketing)/
│   │   ├── page.tsx          # Homepage
│   │   ├── about/
│   │   └── contact/
│   ├── layout.tsx
│   ├── globals.css
│   └── loading.tsx
├── components/
│   ├── ui/                   # shadcn components
│   ├── layout/               # Layout components
│   ├── forms/                # Form components
│   └── shared/               # Shared components
├── hooks/
├── lib/
│   ├── api.ts               # API client
│   ├── auth.ts              # Auth utilities
│   └── utils.ts             # Utilities
├── providers/
│   ├── auth-provider.tsx
│   ├── query-provider.tsx
│   └── theme-provider.tsx
└── types/
    └── index.ts
```

### Day 3: API Client & State Management

#### Tasks
- [ ] Configure React Query (TanStack Query)
- [ ] Create API client with Axios
- [ ] Set up auth interceptors
- [ ] Configure Zustand for client state

#### lib/api.ts
```typescript
import axios, { AxiosError, AxiosInstance } from 'axios';
import { getSession, signOut } from 'next-auth/react';

const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3001';

class ApiClient {
  private client: AxiosInstance;

  constructor() {
    this.client = axios.create({
      baseURL: `${API_BASE_URL}/api/v1`,
      headers: {
        'Content-Type': 'application/json',
      },
      timeout: 10000,
    });

    this.setupInterceptors();
  }

  private setupInterceptors(): void {
    // Request interceptor - add auth token
    this.client.interceptors.request.use(
      async (config) => {
        const session = await getSession();
        if (session?.accessToken) {
          config.headers.Authorization = `Bearer ${session.accessToken}`;
        }
        return config;
      },
      (error) => Promise.reject(error),
    );

    // Response interceptor - handle errors
    this.client.interceptors.response.use(
      (response) => response,
      async (error: AxiosError) => {
        if (error.response?.status === 401) {
          // Token expired, sign out
          await signOut({ callbackUrl: '/login' });
        }
        return Promise.reject(error);
      },
    );
  }

  // Auth endpoints
  async login(email: string, password: string) {
    const { data } = await this.client.post('/auth/login', { email, password });
    return data;
  }

  async register(userData: RegisterData) {
    const { data } = await this.client.post('/auth/register', userData);
    return data;
  }

  async forgotPassword(email: string) {
    const { data } = await this.client.post('/auth/forgot-password', { email });
    return data;
  }

  // Product endpoints
  async getProducts(params?: ProductFilterParams) {
    const { data } = await this.client.get('/products', { params });
    return data;
  }

  async getProduct(id: string) {
    const { data } = await this.client.get(`/products/${id}`);
    return data;
  }

  // Search endpoints
  async searchProducts(query: string, filters?: SearchFilters) {
    const { data } = await this.client.get('/search', {
      params: { q: query, ...filters },
    });
    return data;
  }

  // Analytics endpoints
  async trackEvent(event: AnalyticsEvent) {
    await this.client.post('/analytics/events', event);
  }
}

export const api = new ApiClient();
```

#### providers/query-provider.tsx
```typescript
'use client';

import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { ReactQueryDevtools } from '@tanstack/react-query-devtools';
import { useState } from 'react';

export function QueryProvider({ children }: { children: React.ReactNode }) {
  const [queryClient] = useState(
    () =>
      new QueryClient({
        defaultOptions: {
          queries: {
            staleTime: 1000 * 60 * 5, // 5 minutes
            retry: 1,
            refetchOnWindowFocus: false,
          },
        },
      }),
  );

  return (
    <QueryClientProvider client={queryClient}>
      {children}
      <ReactQueryDevtools initialIsOpen={false} />
    </QueryClientProvider>
  );
}
```

### Day 4-5: Auth Integration (NextAuth.js)

#### Tasks
- [ ] Configure NextAuth.js
- [ ] Set up JWT strategy
- [ ] Create credential provider
- [ ] Implement session callback

#### app/api/auth/[...nextauth]/route.ts
```typescript
import NextAuth from 'next-auth';
import CredentialsProvider from 'next-auth/providers/credentials';
import { api } from '@/lib/api';

const handler = NextAuth({
  providers: [
    CredentialsProvider({
      name: 'Credentials',
      credentials: {
        email: { label: 'Email', type: 'email' },
        password: { label: 'Password', type: 'password' },
      },
      async authorize(credentials) {
        if (!credentials?.email || !credentials?.password) {
          return null;
        }

        try {
          const response = await api.login(credentials.email, credentials.password);
          
          return {
            id: response.user.id,
            email: response.user.email,
            name: `${response.user.firstName} ${response.user.lastName}`,
            image: response.user.avatar,
            accessToken: response.accessToken,
            refreshToken: response.refreshToken,
            roles: response.user.roles,
          };
        } catch (error) {
          return null;
        }
      },
    }),
  ],
  callbacks: {
    async jwt({ token, user, account }) {
      if (user && account) {
        token.accessToken = user.accessToken;
        token.refreshToken = user.refreshToken;
        token.roles = user.roles;
      }
      return token;
    },
    async session({ session, token }) {
      session.accessToken = token.accessToken as string;
      session.refreshToken = token.refreshToken as string;
      session.user.roles = token.roles as string[];
      return session;
    },
  },
  pages: {
    signIn: '/login',
    error: '/login',
  },
  session: {
    strategy: 'jwt',
    maxAge: 30 * 24 * 60 * 60, // 30 days
  },
});

export { handler as GET, handler as POST };
```

#### types/next-auth.d.ts
```typescript
import NextAuth from 'next-auth';

declare module 'next-auth' {
  interface Session {
    accessToken: string;
    refreshToken: string;
    user: {
      id: string;
      email: string;
      name?: string | null;
      image?: string | null;
      roles: string[];
    };
  }

  interface User {
    accessToken: string;
    refreshToken: string;
    roles: string[];
  }
}

declare module 'next-auth/jwt' {
  interface JWT {
    accessToken: string;
    refreshToken: string;
    roles: string[];
  }
}
```

---

## Week 2: Auth UI & Layout

### Day 6-7: Authentication Pages

#### Tasks
- [ ] Create login page
- [ ] Create register page
- [ ] Create forgot password page
- [ ] Implement form validation with Zod

#### app/(auth)/login/page.tsx
```typescript
'use client';

import { useState } from 'react';
import { signIn } from 'next-auth/react';
import { useRouter, useSearchParams } from 'next/navigation';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Icons } from '@/components/icons';

const loginSchema = z.object({
  email: z.string().email('Invalid email address'),
  password: z.string().min(8, 'Password must be at least 8 characters'),
});

type LoginForm = z.infer<typeof loginSchema>;

export default function LoginPage() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const callbackUrl = searchParams.get('callbackUrl') || '/';
  
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState('');
  
  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<LoginForm>({
    resolver: zodResolver(loginSchema),
  });

  const onSubmit = async (data: LoginForm) => {
    setIsLoading(true);
    setError('');

    try {
      const result = await signIn('credentials', {
        email: data.email,
        password: data.password,
        redirect: false,
        callbackUrl,
      });

      if (result?.error) {
        setError('Invalid email or password');
      } else {
        router.push(callbackUrl);
        router.refresh();
      }
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="flex min-h-screen items-center justify-center px-4">
      <Card className="w-full max-w-md">
        <CardHeader className="space-y-1">
          <CardTitle className="text-2xl font-bold">Sign in</CardTitle>
          <CardDescription>
            Enter your email and password to access your account
          </CardDescription>
        </CardHeader>
        <CardContent>
          {error && (
            <Alert variant="destructive" className="mb-4">
              <AlertDescription>{error}</AlertDescription>
            </Alert>
          )}
          
          <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="email">Email</Label>
              <Input
                id="email"
                type="email"
                placeholder="name@example.com"
                {...register('email')}
              />
              {errors.email && (
                <p className="text-sm text-red-500">{errors.email.message}</p>
              )}
            </div>
            
            <div className="space-y-2">
              <Label htmlFor="password">Password</Label>
              <Input
                id="password"
                type="password"
                {...register('password')}
              />
              {errors.password && (
                <p className="text-sm text-red-500">{errors.password.message}</p>
              )}
            </div>
            
            <Button type="submit" className="w-full" disabled={isLoading}>
              {isLoading && (
                <Icons.spinner className="mr-2 h-4 w-4 animate-spin" />
              )}
              Sign in
            </Button>
          </form>
          
          <div className="mt-4 text-center text-sm">
            <a href="/forgot-password" className="text-primary hover:underline">
              Forgot password?
            </a>
          </div>
          
          <div className="mt-4 text-center text-sm">
            Don&apos;t have an account?{' '}
            <a href="/register" className="text-primary hover:underline">
              Sign up
            </a>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
```

### Day 8-9: Layout Components

#### Tasks
- [ ] Create main layout with header/footer
- [ ] Build navigation component
- [ ] Create mobile menu
- [ ] Implement user dropdown

#### components/layout/header.tsx
```typescript
'use client';

import Link from 'next/link';
import { useSession, signOut } from 'next-auth/react';
import { Button } from '@/components/ui/button';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { MainNav } from './main-nav';
import { MobileNav } from './mobile-nav';

export function Header() {
  const { data: session, status } = useSession();
  const isAuthenticated = status === 'authenticated';

  return (
    <header className="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
      <div className="container flex h-16 items-center">
        <MobileNav />
        
        <Link href="/" className="mr-6 flex items-center space-x-2">
          <span className="text-xl font-bold">AffiliatePlatform</span>
        </Link>
        
        <MainNav className="mx-6 hidden md:flex" />
        
        <div className="flex flex-1 items-center justify-end space-x-4">
          {isAuthenticated ? (
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button variant="ghost" className="relative h-8 w-8 rounded-full">
                  <Avatar className="h-8 w-8">
                    <AvatarImage src={session.user.image || ''} alt={session.user.name || ''} />
                    <AvatarFallback>{session.user.name?.charAt(0) || 'U'}</AvatarFallback>
                  </Avatar>
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent className="w-56" align="end" forceMount>
                <div className="flex items-center justify-start gap-2 p-2">
                  <div className="flex flex-col space-y-1 leading-none">
                    {session.user.name && (
                      <p className="font-medium">{session.user.name}</p>
                    )}
                    {session.user.email && (
                      <p className="w-[200px] truncate text-sm text-muted-foreground">
                        {session.user.email}
                      </p>
                    )}
                  </div>
                </div>
                <DropdownMenuSeparator />
                <DropdownMenuItem asChild>
                  <Link href="/profile">Profile</Link>
                </DropdownMenuItem>
                <DropdownMenuItem asChild>
                  <Link href="/orders">Orders</Link>
                </DropdownMenuItem>
                {session.user.roles.includes('ADMIN') && (
                  <DropdownMenuItem asChild>
                    <Link href="/admin">Admin Dashboard</Link>
                  </DropdownMenuItem>
                )}
                <DropdownMenuSeparator />
                <DropdownMenuItem
                  className="cursor-pointer"
                  onSelect={() => signOut()}
                >
                  Sign out
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          ) : (
            <Button variant="ghost" asChild>
              <Link href="/login">Sign in</Link>
            </Button>
          )}
        </div>
      </div>
    </header>
  );
}
```

### Day 10: Error Handling & Loading States

#### Tasks
- [ ] Create error boundary
- [ ] Create loading skeletons
- [ ] Implement 404 page
- [ ] Implement error page

#### app/error.tsx
```typescript
'use client';

import { useEffect } from 'react';
import { Button } from '@/components/ui/button';

export default function ErrorPage({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  useEffect(() => {
    // Log to error tracking service
    console.error(error);
  }, [error]);

  return (
    <div className="flex min-h-[400px] flex-col items-center justify-center">
      <h2 className="text-2xl font-bold">Something went wrong!</h2>
      <p className="mt-2 text-muted-foreground">
        {error.message || 'An unexpected error occurred'}
      </p>
      <Button onClick={reset} className="mt-4">
        Try again
      </Button>
    </div>
  );
}
```

---

## Deliverables Checklist

- [ ] Next.js 15 project configured
- [ ] Tailwind + shadcn/ui set up
- [ ] React Query configured
- [ ] API client with auth interceptors
- [ ] NextAuth.js authentication
- [ ] Login page with validation
- [ ] Register page with validation
- [ ] Forgot password page
- [ ] Header with navigation
- [ ] Footer component
- [ ] Mobile responsive navigation
- [ ] Error boundaries
- [ ] Loading states

## Success Metrics

| Metric | Target | Measurement |
|--------|--------|-------------|
| Bundle size | < 200KB | First load JS |
| Login time | < 2s | From submit to redirect |
| Time to Interactive | < 3s | Lighthouse TTI |
| API response | < 200ms | Cached requests |

## Risks & Mitigation

| Risk | Impact | Mitigation |
|------|--------|------------|
| NextAuth complexity | Medium | Document token refresh flow |
| Bundle bloat | Medium | Dynamic imports for heavy components |
| Hydration errors | Low | Proper use of 'use client' |

## Next Phase Handoff

**Phase 6 Prerequisites:**
- [ ] Auth flow working end-to-end
- [ ] API client tested
- [ ] Layout shell complete
- [ ] Responsive navigation working
- [ ] All auth pages functional
