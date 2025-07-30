<script setup lang="ts">
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
// shadcn components
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Progress } from '@/components/ui/progress';
import { Separator } from '@/components/ui/separator';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Calendar, CheckCircle, Circle, Clock, Edit, Eye, EyeOff, FileText, Image as ImageIcon, Trash2 } from 'lucide-vue-next';
// types and interface
import { type BreadcrumbItem } from '@/types';
import { Task } from '@/interfaces/task-intefaces';
// utils
import { formatDate, formatDateShort } from '@/utils/date-utils';

interface Props {
    task: Task;
}

const props = defineProps<Props>();

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tasks',
        href: '/tasks',
    },
    {
        title: props.task.title,
        href: `/tasks/${props.task.id}`,
    },
];

// Computed properties
const hasImage = computed(() => props.task.image_url !== null);
const hasSubtasks = computed(() => props.task.has_subtasks && props.task.subtasks && props.task.subtasks.length > 0);

// Status badge variants and icons
const getStatusVariant = (status: string) => {
    switch (status) {
        case 'done':
            return 'default';
        case 'in-progress':
            return 'secondary';
        case 'to-do':
            return 'outline';
        default:
            return 'outline';
    }
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'done':
            return CheckCircle;
        case 'in-progress':
            return Clock;
        case 'to-do':
            return Circle;
        default:
            return Circle;
    }
};

// Handle actions
const handleEdit = () => {
    router.visit(`/tasks/${props.task.id}/edit`);
};

const handleDelete = () => {
    if (confirm('Are you sure you want to move this task to trash?')) {
        router.delete(`/tasks/${props.task.id}`, {
            preserveState: false,
        });
    }
};
</script>

<template>
    <Head :title="task.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="mb-2 flex items-center gap-3">
                        <h1 class="text-2xl font-bold tracking-tight">{{ task.title }}</h1>
                    </div>
                    <div class="flex items-center gap-4 text-sm text-muted-foreground">
                        <div class="flex items-center gap-1">
                            <Calendar class="h-4 w-4" />
                            Created {{ formatDateShort(task.created_at) }}
                        </div>
                        <div class="flex items-center gap-1">
                            <Clock class="h-4 w-4" />
                            Updated {{ formatDateShort(task.updated_at) }}
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Link href="/tasks">
                        <Button variant="outline" class="cursor-pointer">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Back
                        </Button>
                    </Link>
                    <Button @click="handleEdit" class="cursor-pointer">
                        <Edit class="mr-2 h-4 w-4" />
                        Edit Task
                    </Button>
                    <Button variant="destructive" @click="handleDelete" class="cursor-pointer">
                        <Trash2 class="h-4 w-4" />
                        Archive
                    </Button>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Content -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Task Content -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <FileText class="h-5 w-5" />
                                Description
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="prose prose-sm max-w-none">
                                <p class="leading-relaxed whitespace-pre-wrap text-foreground">
                                    {{ task.content }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Subtasks -->
                    <Card v-if="hasSubtasks">
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <CardTitle>Subtasks</CardTitle>
                                <div class="text-sm text-muted-foreground">
                                    {{ task.completed_subtasks_count }} of {{ task.total_subtasks_count }} completed
                                </div>
                            </div>
                            <!-- Progress Bar -->
                            <div class="space-y-2">
                                <Progress :value="task.subtasks_progress" class="h-2" />
                                <div class="text-center text-xs text-muted-foreground">{{ Math.round(task.subtasks_progress) }}% Complete</div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div v-for="subtask in task.subtasks" :key="subtask.id" class="flex items-center gap-3 rounded-lg border bg-card p-3">
                                    <Checkbox :id="`subtask-${subtask.id}`" :model-value="subtask.completed" :checked="subtask.completed" disabled />
                                    <div class="flex-1">
                                        <span
                                            :class="{
                                                'text-muted-foreground line-through': subtask.completed,
                                                'text-foreground': !subtask.completed,
                                            }"
                                        >
                                            {{ subtask.title }}
                                        </span>
                                    </div>
                                    <div v-if="subtask.created_at" class="text-xs text-muted-foreground">
                                        {{ formatDateShort(subtask.created_at) }}
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Image -->
                    <Card v-if="hasImage">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <ImageIcon class="h-5 w-5" />
                                Attachment
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="relative">
                                <img :src="task.image_url" :alt="task.title" class="max-h-96 w-full rounded-lg object-cover" />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Status Details -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Status</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Current Status</span>
                                <Badge :variant="getStatusVariant(task.status?.value)" class="ml-auto">
                                    <component :is="getStatusIcon(task.status?.value)" class="mr-1 h-3 w-3" />
                                    {{ task.status?.label }}
                                </Badge>
                            </div>
                            <Separator />
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Visibility</span>
                                <Badge :variant="task.is_published ? 'default' : 'secondary'" class="ml-auto">
                                    <component :is="task.is_published ? Eye : EyeOff" class="mr-1 h-3 w-3" />
                                    {{ task.is_published ? 'Published' : 'Draft' }}
                                </Badge>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Progress Summary -->
                    <Card v-if="hasSubtasks">
                        <CardHeader>
                            <CardTitle>Progress Summary</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-muted-foreground">Total Subtasks</span>
                                    <span class="font-medium">{{ task.total_subtasks_count }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-muted-foreground">Completed</span>
                                    <span class="font-medium text-green-600">{{ task.completed_subtasks_count }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-muted-foreground">Remaining</span>
                                    <span class="font-medium text-orange-600">
                                        {{ task.total_subtasks_count - task.completed_subtasks_count }}
                                    </span>
                                </div>
                            </div>
                            <Separator />
                            <div class="text-center">
                                <div class="text-2xl font-bold text-primary">{{ Math.round(task.subtasks_progress) }}%</div>
                                <div class="text-xs text-muted-foreground">Overall Progress</div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Timeline -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Timeline</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-3">
                                <div class="flex items-start gap-3">
                                    <div class="mt-2 h-2 w-2 rounded-full bg-green-500"></div>
                                    <div class="flex-1">
                                        <div class="text-sm font-medium">Task Created</div>
                                        <div class="text-xs text-muted-foreground">
                                            {{ formatDate(task.created_at) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <div class="mt-2 h-2 w-2 rounded-full bg-blue-500"></div>
                                    <div class="flex-1">
                                        <div class="text-sm font-medium">Last Updated</div>
                                        <div class="text-xs text-muted-foreground">
                                            {{ formatDate(task.updated_at) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.line-through {
    text-decoration: line-through;
}

.prose p {
    margin-bottom: 1rem;
}

.prose p:last-child {
    margin-bottom: 0;
}
</style>
