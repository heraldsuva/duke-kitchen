<script setup lang="ts">
import { dashboard } from '@/routes';
import { index as equipmentIndex } from '@/routes/equipment';
import { index as sitesIndex } from '@/routes/sites';
import { index as usersIndex } from '@/routes/users';
import { login } from '@/routes';
import { clearAuthToken } from '@/lib/auth';
import { Link, router, useHttp, usePage } from '@inertiajs/vue3';
import ConfirmDialog from 'primevue/confirmdialog';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import { computed, onMounted, watch } from 'vue';

const props = defineProps<{
    title: string;
}>();

const page = usePage();
const toast = useToast();
const logoutForm = useHttp({});

const user = computed(() => page.props.auth.user);
const isSuperAdmin = computed(() => user.value?.is_super_admin ?? false);

const navItems = computed(() => {
    const items = [
        { label: 'Dashboard', href: dashboard.url(), icon: 'pi pi-home' },
        ...(isSuperAdmin.value
            ? [{ label: 'Users', href: usersIndex.url(), icon: 'pi pi-users' }]
            : []),
        ...(isSuperAdmin.value
            ? [{ label: 'Roles', href: '/roles', icon: 'pi pi-shield' }]
            : []),
        { label: 'Sites', href: sitesIndex.url(), icon: 'pi pi-building' },
        {
            label: 'Equipment',
            href: equipmentIndex.url(),
            icon: 'pi pi-wrench',
        },
    ];

    return items;
});

const isActive = (href: string) => {
    if (href === dashboard.url()) {
        return page.url === '/dashboard' || page.url.startsWith('/dashboard?');
    }

    return page.url === href || page.url.startsWith(`${href}/`);
};

const showFlash = () => {
    const success = page.props.flash?.success;
    const error = page.props.flash?.error;

    if (success) {
        toast.add({
            severity: 'success',
            summary: 'Success',
            detail: success,
            life: 3500,
        });
    }

    if (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: error,
            life: 4500,
        });
    }
};

onMounted(showFlash);
watch(() => page.props.flash, showFlash, { deep: true });

const submitLogout = () => {
    logoutForm.post('/api/auth/logout', {
        onSuccess: () => {
            clearAuthToken();
            router.visit(login.url());
        },
        onError: () => {
            clearAuthToken();
            router.visit(login.url());
        },
    });
};
</script>

<template>
    <div class="min-h-screen bg-surface-50 text-ink">
        <Toast position="top-right" />
        <ConfirmDialog />

        <header
            class="border-b border-brand-100 bg-brand-700 text-white shadow-sm"
        >
            <div
                class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6"
            >
                <div>
                    <h1 class="text-lg font-semibold sm:text-xl">
                        DSDC : Kitchen Equipment Management
                    </h1>
                </div>
                <div class="hidden text-right text-sm sm:block">
                    <p class="font-medium">{{ user?.full_name }}</p>
                    <p class="text-brand-100">{{ user?.role_name }}</p>
                </div>
            </div>
        </header>

        <div class="mx-auto grid max-w-7xl gap-6 px-4 py-6 sm:px-6 lg:grid-cols-[240px_1fr]">
            <aside
                class="h-fit rounded-xl border border-surface-100 bg-white p-3 shadow-sm"
            >
                <nav class="space-y-1">
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition"
                        :class="
                            isActive(item.href)
                                ? 'bg-brand-50 text-brand-700'
                                : 'text-slate-600 hover:bg-surface-50 hover:text-brand-700'
                        "
                    >
                        <i :class="item.icon" />
                        <span>{{ item.label }}</span>
                    </Link>

                    <button
                        type="button"
                        class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-slate-600 transition hover:bg-red-50 hover:text-red-700"
                        @click="submitLogout"
                    >
                        <i class="pi pi-sign-out" />
                        <span>Logout</span>
                    </button>
                </nav>
            </aside>

            <main class="min-w-0 space-y-4">
                <div
                    class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-surface-100 bg-white px-5 py-4 shadow-sm"
                >
                    <h2 class="text-xl font-semibold text-ink">
                        {{ props.title }}
                    </h2>
                    <div class="flex flex-wrap items-center gap-2">
                        <slot name="actions" />
                    </div>
                </div>

                <div
                    class="rounded-xl border border-surface-100 bg-white p-5 shadow-sm"
                >
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
