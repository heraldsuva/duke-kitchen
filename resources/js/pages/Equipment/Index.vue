<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useHttp } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import { useConfirm } from 'primevue/useconfirm';
import { useToast } from 'primevue/usetoast';
import { computed, reactive, ref } from 'vue';

type EquipmentRow = {
    id: number;
    serial_number: string;
    description: string;
    condition: string;
    site_id: number | null;
    site: string | null;
    owner: string;
    user_id: number;
};

type SiteOption = {
    id: number;
    description: string;
    user_id: number;
    active: boolean;
};

const props = defineProps<{
    equipment: EquipmentRow[];
    sites: SiteOption[];
    conditions: Array<{ label: string; value: string }>;
    isSuperAdmin: boolean;
}>();

const confirm = useConfirm();
const toast = useToast();
const visible = ref(false);
const editing = ref<EquipmentRow | null>(null);
const globalFilter = ref('');

const filters = reactive({
    condition: null as string | null,
});

const form = useHttp({
    serial_number: '',
    description: '',
    condition: 'Working',
    site_id: null as number | null,
});

const deleteForm = useHttp({});

const filteredEquipment = computed(() => {
    let rows = props.equipment;

    if (filters.condition) {
        rows = rows.filter((item) => item.condition === filters.condition);
    }

    return rows;
});

const availableSites = computed(() => {
    if (!editing.value) {
        return props.sites;
    }

    return props.sites.filter(
        (site) => site.user_id === editing.value?.user_id,
    );
});

const openCreate = () => {
    editing.value = null;
    form.reset();
    form.clearErrors();
    form.condition = 'Working';
    form.site_id = null;
    visible.value = true;
};

const openEdit = (item: EquipmentRow) => {
    editing.value = item;
    form.clearErrors();
    form.serial_number = item.serial_number;
    form.description = item.description;
    form.condition = item.condition;
    form.site_id = item.site_id;
    visible.value = true;
};

const submit = () => {
    const onSuccess = () => {
        visible.value = false;
        toast.add({
            severity: 'success',
            summary: 'Success',
            detail: editing.value
                ? 'Equipment updated successfully.'
                : 'Equipment created successfully.',
            life: 3500,
        });
        router.reload();
    };

    if (editing.value) {
        form.put(`/api/equipment/${editing.value.id}`, {
            onSuccess,
        });
    } else {
        form.post('/api/equipment', {
            onSuccess,
        });
    }
};

const confirmDelete = (item: EquipmentRow) => {
    confirm.require({
        message:
            'Are you sure you want to delete this equipment? Its site registration will also be removed.',
        header: 'Delete Equipment',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            deleteForm.delete(`/api/equipment/${item.id}`, {
                onSuccess: () => {
                    toast.add({
                        severity: 'success',
                        summary: 'Success',
                        detail: 'Equipment deleted successfully.',
                        life: 3500,
                    });
                    router.reload();
                },
            });
        },
    });
};
</script>

<template>
    <Head title="Equipment" />

    <AppLayout title="Equipment Maintenance">
        <template #actions>
            <Button
                label="Add Equipment"
                icon="pi pi-plus"
                size="small"
                @click="openCreate"
            />
        </template>

        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
            <InputText
                v-model="globalFilter"
                placeholder="Search serial number or description..."
                class="w-full sm:max-w-sm"
            />
            <Select
                v-model="filters.condition"
                :options="conditions"
                option-label="label"
                option-value="value"
                placeholder="Filter condition"
                show-clear
                class="w-full sm:max-w-xs"
            />
        </div>

        <DataTable
            :value="filteredEquipment"
            paginator
            :rows="10"
            data-key="id"
            :global-filter-fields="[
                'serial_number',
                'description',
                'condition',
                'site',
                'owner',
            ]"
            :global-filter="globalFilter"
            empty-message="No equipment found."
            responsive-layout="scroll"
        >
            <Column field="serial_number" header="Serial Number" sortable />
            <Column field="description" header="Description" sortable />
            <Column field="condition" header="Condition" sortable>
                <template #body="{ data }">
                    <Tag
                        :value="data.condition"
                        :severity="
                            data.condition === 'Working' ? 'success' : 'danger'
                        "
                    />
                </template>
            </Column>
            <Column field="site" header="Registered Site" sortable>
                <template #body="{ data }">
                    <span v-if="data.site">{{ data.site }}</span>
                    <Tag v-else value="No Site Registered" severity="secondary" />
                </template>
            </Column>
            <Column
                v-if="isSuperAdmin"
                field="owner"
                header="Owner"
                sortable
            />
            <Column header="Actions">
                <template #body="{ data }">
                    <div class="flex gap-1">
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
            :header="editing ? 'Edit Equipment' : 'Add Equipment'"
            class="w-full max-w-lg"
        >
            <form class="space-y-4" @submit.prevent="submit">
                <div class="space-y-2">
                    <label class="text-sm font-medium">Serial Number</label>
                    <InputText
                        v-model="form.serial_number"
                        class="w-full"
                        :invalid="!!form.errors.serial_number"
                    />
                    <small class="text-red-600">{{
                        form.errors.serial_number
                    }}</small>
                </div>

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

                <div class="space-y-2">
                    <label class="text-sm font-medium">Condition</label>
                    <Select
                        v-model="form.condition"
                        :options="conditions"
                        option-label="label"
                        option-value="value"
                        class="w-full"
                    />
                    <small class="text-red-600">{{
                        form.errors.condition
                    }}</small>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium">Site</label>
                    <Select
                        v-model="form.site_id"
                        :options="availableSites"
                        option-label="description"
                        option-value="id"
                        class="w-full"
                        show-clear
                        placeholder="No site registered"
                    />
                    <small class="text-slate-500">
                        Changing the Site updates the equipment registration.
                        Leave empty to keep equipment unregistered.
                    </small>
                    <small class="block text-red-600">{{
                        form.errors.site_id
                    }}</small>
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
