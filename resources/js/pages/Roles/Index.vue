<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useHttp } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import { useConfirm } from 'primevue/useconfirm';
import { useToast } from 'primevue/usetoast';
import { computed, ref } from 'vue';

type RoleRow = {
    id: number;
    name: string;
    users_count: number;
};

const props = defineProps<{
    roles: RoleRow[];
}>();

const confirm = useConfirm();
const toast = useToast();
const visible = ref(false);
const editing = ref<RoleRow | null>(null);

const form = useHttp({
    name: '',
});

const deleteForm = useHttp({});

const dialogTitle = computed(() =>
    editing.value ? 'Edit Role' : 'Add Role',
);

const openCreate = () => {
    editing.value = null;
    form.reset();
    form.clearErrors();
    visible.value = true;
};

const openEdit = (role: RoleRow) => {
    editing.value = role;
    form.clearErrors();
    form.name = role.name;
    visible.value = true;
};

const submit = () => {
    const onSuccess = () => {
        visible.value = false;
        toast.add({
            severity: 'success',
            summary: 'Success',
            detail: editing.value
                ? 'Role updated successfully.'
                : 'Role created successfully.',
            life: 3500,
        });
        router.reload();
    };

    if (editing.value) {
        form.put(`/api/roles/${editing.value.id}`, { onSuccess });
    } else {
        form.post('/api/roles', { onSuccess });
    }
};

const confirmDelete = (role: RoleRow) => {
    if (role.users_count > 0) {
        toast.add({
            severity: 'warn',
            summary: 'Not allowed',
            detail: `Cannot delete "${role.name}" because it is assigned to ${role.users_count} user(s).`,
            life: 4000,
        });
        return;
    }

    confirm.require({
        message: `Are you sure you want to delete the "${role.name}" role?`,
        header: 'Delete Role',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            deleteForm.delete(`/api/roles/${role.id}`, {
                onSuccess: () => {
                    toast.add({
                        severity: 'success',
                        summary: 'Success',
                        detail: 'Role deleted successfully.',
                        life: 3500,
                    });
                    router.reload();
                },
                onError: (errors) => {
                    const detail =
                        errors.role ||
                        Object.values(errors)[0] ||
                        'Unable to delete this role.';
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
</script>

<template>
    <Head title="Roles" />

    <AppLayout title="Role Maintenance">
        <template #actions>
            <Button
                label="Add Role"
                icon="pi pi-plus"
                size="small"
                @click="openCreate"
            />
        </template>

        <DataTable
            :value="props.roles"
            paginator
            :rows="10"
            :rows-per-page-options="[5, 10, 20]"
            data-key="id"
            sort-mode="multiple"
            responsive-layout="scroll"
        >
            <Column field="name" header="Role Name" sortable />
            <Column field="users_count" header="Users" sortable>
                <template #body="{ data }">
                    <Tag
                        :value="String(data.users_count)"
                        :severity="data.users_count > 0 ? 'info' : 'secondary'"
                    />
                </template>
            </Column>
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
            class="w-full max-w-md"
        >
            <form class="grid gap-4" @submit.prevent="submit">
                <div class="space-y-2">
                    <label class="text-sm font-medium">Role Name</label>
                    <InputText
                        v-model="form.name"
                        class="w-full"
                        :invalid="!!form.errors.name"
                        autofocus
                    />
                    <small class="text-red-600">{{ form.errors.name }}</small>
                </div>
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
