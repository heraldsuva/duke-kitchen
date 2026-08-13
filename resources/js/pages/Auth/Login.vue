<script setup lang="ts">
import { login as loginRoute, signup } from '@/routes';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import Password from 'primevue/password';

const form = useForm({
    login: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(loginRoute.url(), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Login" />

    <div
        class="flex min-h-screen items-center justify-center bg-[radial-gradient(circle_at_top_left,#d5ebe5,transparent_40%),linear-gradient(180deg,#f7f8f8,#eef1f1)] px-4"
    >
        <div
            class="w-full max-w-md rounded-2xl border border-brand-100 bg-white p-8 shadow-lg"
        >
            <div class="mb-8 text-center">
                <p
                    class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-600"
                >
                    Kitchen Equipment Management
                </p>
                <h1 class="mt-2 text-2xl font-semibold text-ink">Sign in</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Use your username or email address.
                </p>
            </div>

            <form class="space-y-4" @submit.prevent="submit">
                <div class="space-y-2">
                    <label class="text-sm font-medium" for="login"
                        >Username or Email</label
                    >
                    <InputText
                        id="login"
                        v-model="form.login"
                        class="w-full"
                        autocomplete="username"
                        :invalid="!!form.errors.login"
                    />
                    <small v-if="form.errors.login" class="text-red-600">{{
                        form.errors.login
                    }}</small>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium" for="password"
                        >Password</label
                    >
                    <Password
                        id="password"
                        v-model="form.password"
                        class="w-full"
                        input-class="w-full"
                        :feedback="false"
                        toggle-mask
                        :invalid="!!form.errors.password"
                    />
                    <small v-if="form.errors.password" class="text-red-600">{{
                        form.errors.password
                    }}</small>
                </div>

                <Message
                    v-if="form.errors.login && !form.login"
                    severity="error"
                    :closable="false"
                >
                    {{ form.errors.login }}
                </Message>

                <Button
                    type="submit"
                    label="Login"
                    class="w-full"
                    :loading="form.processing"
                />
            </form>

            <p class="mt-6 text-center text-sm text-slate-600">
                Need an account?
                <Link
                    :href="signup.url()"
                    class="font-medium text-brand-600 hover:underline"
                >
                    Sign up
                </Link>
            </p>
        </div>
    </div>
</template>
