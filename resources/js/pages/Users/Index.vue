<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useHttp } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import { useConfirm } from 'primevue/useconfirm';
import { useToast } from 'primevue/usetoast';
import { computed, reactive, ref } from 'vue';

type UserRow = {
    id: number;
    first_name: string;
    last_name: string;
    full_name: string;
    user_name: string;
    email_address: string;
    user_type: string;
    sites_count: number;
    equipment_count: number;
};

const props = defineProps<{
    users: UserRow[];
    userTypes: Array<{ label: string; value: string }>;
    authUserId: number;
}>();

const confirm = useConfirm();
const toast = useToast();
const visible = ref(false);
const editing = ref<UserRow | null>(null);
const globalFilter = ref('');

const filters = reactive({
    user_type: null as string | null,
});

const form = useHttp({
    first_name: '',
    last_name: '',
    email_address: '',
    user_name: '',
    password: '',
    password_confirmation: '',
    user_type: 'Admin',
});

const deleteForm = useHttp({});

const dialogTitle = computed(() =>
    editing.value ? 'Edit User' : 'Add User',
);

const filteredUsers = computed(() => {
    let rows = props.users;

    if (filters.user_type) {
        rows = rows.filter((user) => user.user_type === filters.user_type);
    }

    return rows;
});

const openCreate = () => {
    editing.value = null;
    form.reset();
    form.clearErrors();
    form.user_type = 'Admin';
    visible.value = true;
};

const openEdit = (user: UserRow) => {
    editing.value = user;
    form.clearErrors();
    form.first_name = user.first_name;
    form.last_name = user.last_name;
    form.email_address = user.email_address;
    form.user_name = user.user_name;
    form.user_type = user.user_type;
    form.password = '';
    form.password_confirmation = '';
    visible.value = true;
};

const submit = () => {
    const onSuccess = () => {
        visible.value = false;
        toast.add({
            severity: 'success',
            summary: 'Success',
            detail: editing.value
                ? 'User updated successfully.'
                : 'User created successfully.',
            life: 3500,
        });
        router.reload();
    };

    if (editing.value) {
        form.put(`/api/users/${editing.value.id}`, {
            onSuccess,
        });
    } else {
        form.post('/api/users', {
            onSuccess,
        });
    }
};

const confirmDelete = (user: UserRow) => {
    if (user.id === props.authUserId) {
        toast.add({
            severity: 'warn',
            summary: 'Not allowed',
            detail: 'You cannot delete your own currently authenticated account.',
            life: 4000,
        });
        return;
    }

    confirm.require({
        message: `Are you sure you want to delete ${user.full_name}? Users who still own sites or equipment cannot be deleted.`,
        header: 'Delete User',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            deleteForm.delete(`/api/users/${user.id}`, {
                onSuccess: () => {
                    toast.add({
                        severity: 'success',
                        summary: 'Success',
                        detail: 'User deleted successfully.',
                        life: 3500,
                    });
                    router.reload();
                },
                onError: (errors) => {
                    const detail =
                        errors.user ||
                        Object.values(errors)[0] ||
                        'Unable to delete this user.';
                    toast.add({
                        severity: 'error',
                        summary: 'Delete failed',
                        detail: String(detail),
                        life: 5000,
                    });
                },
            });
        },
    });
};

const typeSeverity = (type: string) =>
    type === 'SuperAdmin' ? 'info' : 'secondary';
</script>

<template>
    <Head title="Users" />

    <AppLayout title="User Maintenance">
        <template #actions>
            <Button
                label="Add User"
                icon="pi pi-plus"
                size="small"
                @click="openCreate"
            />
        </template>

        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
            <InputText
                v-model="globalFilter"
                placeholder="Search name, email, username..."
                class="w-full sm:max-w-sm"
            />
            <Select
                v-model="filters.user_type"
                :options="userTypes"
                option-label="label"
                option-value="value"
                placeholder="Filter by user type"
                show-clear
                class="w-full sm:max-w-xs"
            />
        </div>

        <DataTable
            :value="filteredUsers"
            paginator
            :rows="10"
            :rows-per-page-options="[5, 10, 20]"
            data-key="id"
            :global-filter-fields="[
                'full_name',
                'user_name',
                'email_address',
                'user_type',
            ]"
            :global-filter="globalFilter"
            sort-mode="multiple"
            empty-message="No users found."
            responsive-layout="scroll"
        >
            <Column field="full_name" header="Name" sortable />
            <Column field="user_name" header="Username" sortable />
            <Column field="email_address" header="Email" sortable />
            <Column field="user_type" header="User Type" sortable>
                <template #body="{ data }">
                    <Tag
                        :value="data.user_type"
                        :severity="typeSeverity(data.user_type)"
                    />
                </template>
            </Column>
            <Column field="sites_count" header="Sites" sortable />
            <Column field="equipment_count" header="Equipment" sortable />
            <Column header="Actions">
                <template #body="{ data }">
                    <div class="flex gap-2">
                        <Button
                            icon="pi pi-pencil"
                            severity="secondary"
                            text
                            rounded
                            @click="openEdit(data)"
                        />
                        <Button
                            icon="pi pi-trash"
                            severity="danger"
                            text
                            rounded
                            @click="confirmDelete(data)"
                        />
                    </div>
                </template>
            </Column>
        </DataTable>

        <Dialog
            v-model:visible="visible"
            modal
            :header="dialogTitle"
            class="w-full max-w-xl"
        >
            <form class="grid gap-4 sm:grid-cols-2" @submit.prevent="submit">
                <div class="space-y-2">
                    <label class="text-sm font-medium">First Name</label>
                    <InputText
                        v-model="form.first_name"
                        class="w-full"
                        :invalid="!!form.errors.first_name"
                    />
                    <small class="text-red-600">{{
                        form.errors.first_name
                    }}</small>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Last Name</label>
                    <InputText
                        v-model="form.last_name"
                        class="w-full"
                        :invalid="!!form.errors.last_name"
                    />
                    <small class="text-red-600">{{
                        form.errors.last_name
                    }}</small>
                </div>
                <div class="space-y-2 sm:col-span-2">
                    <label class="text-sm font-medium">Email</label>
                    <InputText
                        v-model="form.email_address"
                        class="w-full"
                        :invalid="!!form.errors.email_address"
                    />
                    <small class="text-red-600">{{
                        form.errors.email_address
                    }}</small>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Username</label>
                    <InputText
                        v-model="form.user_name"
                        class="w-full"
                        :invalid="!!form.errors.user_name"
                    />
                    <small class="text-red-600">{{
                        form.errors.user_name
                    }}</small>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">User Type</label>
                    <Select
                        v-model="form.user_type"
                        :options="userTypes"
                        option-label="label"
                        option-value="value"
                        class="w-full"
                    />
                    <small class="text-red-600">{{
                        form.errors.user_type
                    }}</small>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Password</label>
                    <Password
                        v-model="form.password"
                        class="w-full"
                        input-class="w-full"
                        :feedback="false"
                        toggle-mask
                        :placeholder="
                            editing ? 'Leave blank to keep current' : ''
                        "
                    />
                    <small class="text-red-600">{{
                        form.errors.password
                    }}</small>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Confirm Password</label>
                    <Password
                        v-model="form.password_confirmation"
                        class="w-full"
                        input-class="w-full"
                        :feedback="false"
                        toggle-mask
                    />
                </div>
                <div class="flex justify-end gap-2 sm:col-span-2">
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
