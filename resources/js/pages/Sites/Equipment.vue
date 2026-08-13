<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { index as equipmentIndex } from '@/routes/equipment';
import { index as sitesIndex } from '@/routes/sites';
import { Head, Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Tag from 'primevue/tag';

defineProps<{
    site: {
        id: number;
        description: string;
        active: boolean;
        owner: string;
    };
    equipment: Array<{
        id: number;
        serial_number: string;
        description: string;
        condition: string;
    }>;
}>();
</script>

<template>
    <Head :title="`Site Equipment - ${site.description}`" />

    <AppLayout :title="`Site Equipment: ${site.description}`">
        <template #actions>
            <Link :href="sitesIndex.url()">
                <Button
                    label="Back to Sites"
                    severity="secondary"
                    text
                    size="small"
                />
            </Link>
            <Link :href="equipmentIndex.url()">
                <Button
                    label="Edit Equipment"
                    icon="pi pi-wrench"
                    outlined
                    size="small"
                />
            </Link>
        </template>

        <div class="mb-6 grid gap-3 sm:grid-cols-3">
            <div class="rounded-lg bg-surface-50 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">
                    Site Name
                </p>
                <p class="mt-1 font-semibold">{{ site.description }}</p>
            </div>
            <div class="rounded-lg bg-surface-50 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">
                    Status
                </p>
                <div class="mt-1">
                    <Tag
                        :value="site.active ? 'Active' : 'Inactive'"
                        :severity="site.active ? 'success' : 'secondary'"
                    />
                </div>
            </div>
            <div class="rounded-lg bg-surface-50 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">
                    Owner
                </p>
                <p class="mt-1 font-semibold">{{ site.owner }}</p>
            </div>
        </div>

        <p class="mb-4 text-sm text-slate-600">
            Site changes for equipment are made from Equipment Maintenance →
            Edit Equipment. There is no separate Assign/Move workflow.
        </p>

        <DataTable
            :value="equipment"
            paginator
            :rows="10"
            data-key="id"
            empty-message="This site currently has no registered equipment."
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
            <Column header="Actions">
                <template #body>
                    <Link :href="equipmentIndex.url()">
                        <Button
                            label="Edit Equipment"
                            icon="pi pi-pencil"
                            size="small"
                            text
                        />
                    </Link>
                </template>
            </Column>
        </DataTable>
    </AppLayout>
</template>
