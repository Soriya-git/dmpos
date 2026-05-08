export type User = {
    id: number;
    name: string;
    email: string;
    image?: string | null;
    image_url?: string | null;
    email_verified_at: string | null;
    created_at: string | null;
    updated_at: string | null;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};
