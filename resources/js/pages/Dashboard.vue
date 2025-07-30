<script setup lang="ts">
import RecentTasksCard from '@/components/dashboard/RecentTasksCard.vue';
import StatsCard from '@/components/dashboard/StatsCard.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { CheckCircle2, Clock, FileText } from 'lucide-vue-next';
import { TaskStatus } from '@/interfaces/task-intefaces';

interface Task {
    id: number;
    title: string;
    status: TaskStatus;
    created_at: string;
}

interface Stats {
    total_tasks: number;
    completed_tasks: number;
    in_progress_tasks: number;
    pending_tasks: number;
}

interface Props {
    stats: Stats;
    recent_tasks: Task[];
    completion_rate: number;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Stats Cards -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <StatsCard title="Total Tasks" :value="stats.total_tasks" :icon="FileText" color="text-blue-600" />
                <StatsCard title="Completed" :value="stats.completed_tasks" :icon="CheckCircle2" color="text-green-600" />
                <StatsCard title="In Progress" :value="stats.in_progress_tasks" :icon="Clock" color="text-yellow-600" />
            </div>

            <!-- Recent Tasks -->
            <div class="relative min-h-[100vh] flex-1 rounded-xl md:min-h-min">
                <RecentTasksCard :tasks="recent_tasks" />
            </div>
        </div>
    </AppLayout>
</template>
