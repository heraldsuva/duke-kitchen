<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { index as equipmentIndex } from '@/routes/equipment';
import { index as sitesIndex } from '@/routes/sites';
import { Head, Link } from '@inertiajs/vue3';
import Card from 'primevue/card';
import Tag from 'primevue/tag';

type StatCard = {
    label: string;
    value: number;
};

defineProps<{
    stats: {
        total_sites: number;
        total_equipment: number;
        working_equipment: number;
        not_working_equipment: number;
    };
    recent_equipment: Array<{
        id: number;
        serial_number: string;
        description: string;
        condition: string;
        site: string | null;
    }>;
}>();

const conditionSeverity = (condition: string) =>
    condition === 'Working' ? 'success' : 'danger';
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout title="Dashboard">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <Card
                v-for="card in [
                    { label: 'Total Sites', value: stats.total_sites },
                    { label: 'Total Equipment', value: stats.total_equipment },
                    {
                        label: 'Working Equipment',
                        value: stats.working_equipment,
                    },
                    {
                        label: 'Not Working Equipment',
                        value: stats.not_working_equipment,
                    },
                ] as StatCard[]"
                :key="card.label"
            >
                <template #title>
                    <span class="text-sm font-medium text-slate-500">{{
                        card.label
                    }}</span>
                </template>
                <template #content>
                    <p class="text-3xl font-semibold text-brand-700">
                        {{ card.value }}
                    </p>
                </template>
            </Card>
        </div>

        <div class="mt-8">
            <div class="mb-3 flex items-center justify-between gap-3">
                <h3 class="text-lg font-semibold">Recent Equipment</h3>
                <div class="flex gap-2 text-sm">
                    <Link
                        :href="sitesIndex.url()"
                        class="text-brand-600 hover:underline"
                        >Manage Sites</Link
                    >
                    <span class="text-slate-300">|</span>
                    <Link
                        :href="equipmentIndex.url()"
                        class="text-brand-600 hover:underline"
                        >Manage Equipment</Link
                    >
                </div>
            </div>

            <div
                v-if="recent_equipment.length === 0"
                class="rounded-lg border border-dashed border-slate-200 px-4 py-10 text-center text-slate-500"
            >
                No equipment yet. Create your first equipment record to get
                started.
            </div>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-slate-200 text-slate-500">
                        <tr>
                            <th class="px-2 py-2 font-medium">Serial</th>
                            <th class="px-2 py-2 font-medium">Description</th>
                            <th class="px-2 py-2 font-medium">Condition</th>
                            <th class="px-2 py-2 font-medium">Site</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in recent_equipment"
                            :key="item.id"
                            class="border-b border-slate-100"
                        >
                            <td class="px-2 py-3 font-medium">
                                {{ item.serial_number }}
                            </td>
                            <td class="px-2 py-3">{{ item.description }}</td>
                            <td class="px-2 py-3">
                                <Tag
                                    :value="item.condition"
                                    :severity="
                                        conditionSeverity(item.condition)
                                    "
                                />
                            </td>
                            <td class="px-2 py-3">
                                {{ item.site ?? 'No Site Registered' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
