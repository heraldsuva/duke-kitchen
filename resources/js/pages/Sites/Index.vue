<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import {
    destroy,
    store,
    update,
} from '@/actions/App/Http/Controllers/SiteController';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import { useConfirm } from 'primevue/useconfirm';
import { computed, reactive, ref } from 'vue';

type SiteRow = {
    id: number;
    description: string;
    active: boolean;
    equipment_count: number;
    owner: { id: number; name: string; user_name: string };
};

type OwnerOption = {
    id: number;
    name: string;
    user_name: string;
};

const props = defineProps<{
    sites: SiteRow[];
    owners: OwnerOption[];
    isSuperAdmin: boolean;
}>();

const page = usePage();
const confirm = useConfirm();
const visible = ref(false);
const editing = ref<SiteRow | null>(null);
const globalFilter = ref('');

const filters = reactive({
    active: null as boolean | null,
});

const form = useForm({
    description: '',
    active: true,
    user_id: page.props.auth.user?.id ?? null as number | null,
});

const activeOptions = [
    { label: 'Active', value: true },
    { label: 'Inactive', value: false },
];

const filteredSites = computed(() => {
    let rows = props.sites;

    if (filters.active !== null) {
        rows = rows.filter((site) => site.active === filters.active);
    }

    return rows;
});

const openCreate = () => {
    editing.value = null;
    form.reset();
    form.clearErrors();
    form.active = true;
    form.user_id = page.props.auth.user?.id ?? null;
    visible.value = true;
};

const openEdit = (site: SiteRow) => {
    editing.value = site;
    form.clearErrors();
    form.description = site.description;
    form.active = site.active;
    form.user_id = site.owner.id;
    visible.value = true;
};

const submit = () => {
    if (editing.value) {
        form.transform((data) => ({
            description: data.description,
            active: data.active,
        })).put(update.url(editing.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                visible.value = false;
            },
        });
    } else {
        form.post(store.url(), {
            preserveScroll: true,
            onSuccess: () => {
                visible.value = false;
            },
        });
    }
};

const confirmDelete = (site: SiteRow) => {
    confirm.require({
        message:
            'Are you sure you want to delete this Site? All equipment registrations for this Site will be removed. Equipment itself will not be deleted.',
        header: 'Delete Site',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            form.delete(destroy.url(site.id), { preserveScroll: true });
        },
    });
};
</script>

<template>
    <Head title="Sites" />

    <AppLayout title="Site Maintenance">
        <template #actions>
            <Button
                label="Add Site"
                icon="pi pi-plus"
                size="small"
                @click="openCreate"
            />
        </template>

        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
            <InputText
                v-model="globalFilter"
                placeholder="Search site description..."
                class="w-full sm:max-w-sm"
            />
            <Select
                v-model="filters.active"
                :options="activeOptions"
                option-label="label"
                option-value="value"
                placeholder="Filter active status"
                show-clear
                class="w-full sm:max-w-xs"
            />
        </div>

        <DataTable
            :value="filteredSites"
            paginator
            :rows="10"
            data-key="id"
            :global-filter-fields="['description', 'owner.name']"
            :global-filter="globalFilter"
            empty-message="No sites found."
            responsive-layout="scroll"
        >
            <Column field="description" header="Site" sortable />
            <Column
                v-if="isSuperAdmin"
                field="owner.name"
                header="Owner"
                sortable
            />
            <Column field="active" header="Active" sortable>
                <template #body="{ data }">
                    <Tag
                        :value="data.active ? 'Active' : 'Inactive'"
                        :severity="data.active ? 'success' : 'secondary'"
                    />
                </template>
            </Column>
            <Column field="equipment_count" header="Equipment Count" sortable />
            <Column header="Actions">
                <template #body="{ data }">
                    <div class="flex flex-wrap gap-1">
                        <Button
                            icon="pi pi-pencil"
                            severity="secondary"
                            text
                            rounded
                            v-tooltip.top="'Edit'"
                            @click="openEdit(data)"
                        />
                        <Link :href="`/sites/${data.id}/equipment`">
                            <Button
                                icon="pi pi-list"
                                severity="info"
                                text
                                rounded
                                v-tooltip.top="'Site Equipment'"
                            />
                        </Link>
                        <Button
                            icon="pi pi-trash"
                            severity="danger"
                            text
                            rounded
                            v-tooltip.top="'Delete'"
                            @click="confirmDelete(data)"
                        />
                    </div>
                </template>
            </Column>
        </DataTable>

        <Dialog
            v-model:visible="visible"
            modal
            :header="editing ? 'Edit Site' : 'Add Site'"
            class="w-full max-w-lg"
        >
            <form class="space-y-4" @submit.prevent="submit">
                <div class="space-y-2">
                    <label class="text-sm font-medium">Description</label>
                    <InputText
                        v-model="form.description"
                        class="w-full"
                        :invalid="!!form.errors.description"
                    />
                    <small class="text-red-600">{{
                        form.errors.description
                    }}</small>
                </div>

                <div
                    v-if="isSuperAdmin && !editing"
                    class="space-y-2"
                >
                    <label class="text-sm font-medium">Owner</label>
                    <Select
                        v-model="form.user_id"
                        :options="owners"
                        option-label="name"
                        option-value="id"
                        class="w-full"
                        placeholder="Select owner"
                    />
                    <small class="text-red-600">{{ form.errors.user_id }}</small>
                </div>

                <div class="flex items-center gap-2">
                    <Checkbox
                        v-model="form.active"
                        binary
                        input-id="active"
                    />
                    <label for="active" class="text-sm font-medium"
                        >Active</label
                    >
                </div>
                <small class="text-red-600">{{ form.errors.active }}</small>

                <div class="flex justify-end gap-2">
                    <Button
                        type="button"
                        label="Cancel"
                        severity="secondary"
                        text
                        @click="visible = false"
                    />
                    <Button
                        type="submit"
                        :label="editing ? 'Save' : 'Create'"
                        :loading="form.processing"
                    />
                </div>
            </form>
        </Dialog>
    </AppLayout>
</template>
