import { createInertiaApp, http, router } from '@inertiajs/vue3';
import Aura from '@primeuix/themes/aura';
import PrimeVue from 'primevue/config';
import ConfirmationService from 'primevue/confirmationservice';
import ToastService from 'primevue/toastservice';
import Tooltip from 'primevue/tooltip';
import { createApp, h, type DefineComponent } from 'vue';
import { clearAuthToken, getAuthToken } from '@/lib/auth';

const appName = import.meta.env.VITE_APP_NAME || 'Kitchen Equipment Management';

http.onRequest((config) => {
    const token = getAuthToken();

    if (token) {
        config.headers = {
            ...config.headers,
            Authorization: `Bearer ${token}`,
        };
    }

    return config;
});

http.onError((error) => {
    if ('response' in error && error.response?.status === 401) {
        clearAuthToken();
        router.visit('/login');
    }
});

router.on('before', (event) => {
    const token = getAuthToken();

    if (token) {
        event.detail.visit.headers = {
            ...(event.detail.visit.headers ?? {}),
            Authorization: `Bearer ${token}`,
        };
    }
});

router.on('httpException', (event) => {
    if (event.detail.response?.status === 401) {
        clearAuthToken();
        router.visit('/login');
    }
});

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => {
        const pages = import.meta.glob('./pages/**/*.vue', { eager: true });
        const page = pages[`./pages/${name}.vue`] as { default: DefineComponent };

        if (! page) {
            throw new Error(`Page not found: ${name}`);
        }

        return page.default;
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(PrimeVue, {
                theme: {
                    preset: Aura,
                    options: {
                        darkModeSelector: false,
                        cssLayer: false,
                    },
                },
            })
            .use(ToastService)
            .use(ConfirmationService)
            .directive('tooltip', Tooltip)
            .mount(el);
    },
    progress: {
        color: '#1f6f5b',
    },
});
