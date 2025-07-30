<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Link } from '@inertiajs/vue3';
import { FileText } from 'lucide-vue-next';
import { TaskStatus } from '../tasks/columns';

interface Task {
    id: number;
    title: string;
    status: TaskStatus;
    created_at: string;
}

interface Props {
    tasks: Task[];
}

defineProps<Props>();

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Card class="h-full">
        <CardHeader>
            <CardTitle class="flex items-center gap-2">
                <FileText class="h-5 w-5" />
                Recent Tasks
            </CardTitle>
        </CardHeader>
        <CardContent>
            <div v-if="tasks.length === 0" class="flex h-32 items-center justify-center text-muted-foreground">
                <div class="text-center">
                    <FileText class="mx-auto mb-2 h-8 w-8 opacity-50" />
                    <p class="text-sm">No tasks found</p>
                    <Link href="/tasks/create" class="text-sm text-primary hover:underline"> Create your first task </Link>
                </div>
            </div>

            <div v-else class="space-y-3">
                <div
                    v-for="task in tasks"
                    :key="task.id"
                    class="flex items-center justify-between rounded-lg border p-3 transition-colors hover:bg-accent/50"
                >
                    <div class="flex-1">
                        <Link :href="`/tasks/${task.id}`" class="hover:underline">
                            <h4 class="font-medium">{{ task.title }}</h4>
                        </Link>
                        <p class="text-sm text-muted-foreground">Created {{ formatDate(task.created_at) }}</p>
                    </div>
                </div>
            </div>

            <div v-if="tasks.length > 0" class="mt-4 text-center">
                <Link href="/tasks" class="text-sm text-primary hover:underline"> View all tasks → </Link>
            </div>
        </CardContent>
    </Card>
</template>
