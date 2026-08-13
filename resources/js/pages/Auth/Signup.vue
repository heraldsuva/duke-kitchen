<script setup lang="ts">
import { login, signup as signupRoute } from '@/routes';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';

const form = useForm({
    first_name: '',
    last_name: '',
    email_address: '',
    user_name: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(signupRoute.url());
};
</script>

<template>
    <Head title="Signup" />

    <div
        class="flex min-h-screen items-center justify-center bg-[radial-gradient(circle_at_top_left,#d5ebe5,transparent_40%),linear-gradient(180deg,#f7f8f8,#eef1f1)] px-4 py-10"
    >
        <div
            class="w-full max-w-xl rounded-2xl border border-brand-100 bg-white p-8 shadow-lg"
        >
            <div class="mb-8 text-center">
                <p
                    class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-600"
                >
                    Kitchen Equipment Management
                </p>
                <h1 class="mt-2 text-2xl font-semibold text-ink">
                    Create an account
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    New accounts are created as Admin.
                </p>
            </div>

            <form class="grid gap-4 sm:grid-cols-2" @submit.prevent="submit">
                <div class="space-y-2">
                    <label class="text-sm font-medium" for="first_name"
                        >First Name</label
                    >
                    <InputText
                        id="first_name"
                        v-model="form.first_name"
                        class="w-full"
                        :invalid="!!form.errors.first_name"
                    />
                    <small
                        v-if="form.errors.first_name"
                        class="text-red-600"
                        >{{ form.errors.first_name }}</small
                    >
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium" for="last_name"
                        >Last Name</label
                    >
                    <InputText
                        id="last_name"
                        v-model="form.last_name"
                        class="w-full"
                        :invalid="!!form.errors.last_name"
                    />
                    <small v-if="form.errors.last_name" class="text-red-600">{{
                        form.errors.last_name
                    }}</small>
                </div>

                <div class="space-y-2 sm:col-span-2">
                    <label class="text-sm font-medium" for="email_address"
                        >Email Address</label
                    >
                    <InputText
                        id="email_address"
                        v-model="form.email_address"
                        class="w-full"
                        type="email"
                        :invalid="!!form.errors.email_address"
                    />
                    <small
                        v-if="form.errors.email_address"
                        class="text-red-600"
                        >{{ form.errors.email_address }}</small
                    >
                </div>

                <div class="space-y-2 sm:col-span-2">
                    <label class="text-sm font-medium" for="user_name"
                        >Username</label
                    >
                    <InputText
                        id="user_name"
                        v-model="form.user_name"
                        class="w-full"
                        :invalid="!!form.errors.user_name"
                    />
                    <small v-if="form.errors.user_name" class="text-red-600">{{
                        form.errors.user_name
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
                        toggle-mask
                        :invalid="!!form.errors.password"
                    />
                    <small v-if="form.errors.password" class="text-red-600">{{
                        form.errors.password
                    }}</small>
                </div>

                <div class="space-y-2">
                    <label
                        class="text-sm font-medium"
                        for="password_confirmation"
                        >Confirm Password</label
                    >
                    <Password
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        class="w-full"
                        input-class="w-full"
                        :feedback="false"
                        toggle-mask
                    />
                </div>

                <div class="sm:col-span-2">
                    <Button
                        type="submit"
                        label="Create Account"
                        class="w-full"
                        :loading="form.processing"
                    />
                </div>
            </form>

            <p class="mt-6 text-center text-sm text-slate-600">
                Already registered?
                <Link
                    :href="login.url()"
                    class="font-medium text-brand-600 hover:underline"
                >
                    Login
                </Link>
            </p>
        </div>
    </div>
</template>
