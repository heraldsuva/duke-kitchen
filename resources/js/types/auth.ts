export type User = {
    id: number;
    first_name: string;
    last_name: string;
    full_name: string;
    user_name: string;
    email_address: string;
    user_type: 'SuperAdmin' | 'Admin';
    is_super_admin: boolean;
};

export type Auth = {
    user: User | null;
};

export type Flash = {
    success: string | null;
    error: string | null;
};
