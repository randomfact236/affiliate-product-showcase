export interface User {
    id: string;
    email: string;
    firstName?: string;
    lastName?: string;
    status: 'ACTIVE' | 'INACTIVE' | 'SUSPENDED';
    roles: string[];
    createdAt: Date;
    lastLoginAt?: Date;
}

export interface AuthResponse {
    user: User;
    accessToken: string;
    refreshToken: string;
}
