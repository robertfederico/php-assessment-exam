<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { computed, ref, watch } from 'vue';
// global component
import AppLayout from '@/layouts/AppLayout.vue';
// shadcn components
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { Progress } from '@/components/ui/progress';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { CheckCircle, Circle, Clock, Edit, Eye, FileText, MoreHorizontal, Plus, Search, Trash2 } from 'lucide-vue-next';
// types and interfaces
import { PaginatedResponse } from '@/interfaces/global-interfaces';
import { StatusOption, Task } from '@/interfaces/task-intefaces';
import { type BreadcrumbItem } from '@/types';

interface Props {
    tasks: PaginatedResponse<Task>;
    filters: {
        search?: string;
        status?: string;
        is_published?: boolean;
    };
    statusOptions: StatusOption[];
    perPageOptions: number[];
}

const props = defineProps<Props>();

// Reactive filters
const searchQuery = ref<string>(props.filters.search || '');
const selectedStatus = ref<string>(props.filters.status || '');
const selectedPublished = ref<string>(props.filters.is_published !== undefined ? props.filters?.is_published?.toString() : '');
const perPage = ref<number>(props.tasks.meta.per_page);
const orderBy = ref<string>('created_at');
const orderDirection = ref<string>('desc');

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tasks',
        href: '/tasks',
    },
];

// Computed properties
const hasFilters = computed(() => {
    return searchQuery.value || selectedStatus.value || selectedPublished.value;
});

const publishedOptions = [
    { value: 'true', label: 'Published' },
    { value: 'false', label: 'Drafts' },
];

// Status badge variants
const getStatusVariant = (status: string) => {
    switch (status) {
        case 'done':
            return 'default';
        case 'in-progress':
            return 'secondary';
        case 'to-do':
            return 'outline';
        default:
            return 'default';
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

// Debounced search function
const debouncedSearch = debounce(() => {
    updateFilters();
}, 300);

// Watch for search changes
watch(searchQuery, () => {
    debouncedSearch();
});

// Update filters function
const updateFilters = () => {
    const params: Record<string, any> = {};

    if (searchQuery.value) params.search = searchQuery.value;
    if (selectedStatus.value) params.status = selectedStatus.value;
    if (selectedPublished.value) params.is_published = selectedPublished.value;
    if (perPage.value !== 10) params.per_page = perPage.value;
    if (orderBy.value !== 'created_at') params.order_by = orderBy.value;
    if (orderDirection.value !== 'desc') params.order_direction = orderDirection.value;

    router.get('/tasks', params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

// Clear all filters
const clearFilters = () => {
    searchQuery.value = '';
    selectedStatus.value = '';
    selectedPublished.value = '';
    perPage.value = 10;
    orderBy.value = 'created_at';
    orderDirection.value = 'desc';

    router.get(
        '/tasks',
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

// Handle filter changes
const handleStatusChange = (value: string) => {
    selectedStatus.value = value;
    updateFilters();
};

const handlePublishedChange = (value: string): any => {
    selectedPublished.value = value;
    updateFilters();
};

const handlePerPageChange = (value: string) => {
    perPage.value = parseInt(value);
    updateFilters();
};

// Handle sorting
const handleSort = (column: string) => {
    if (orderBy.value === column) {
        orderDirection.value = orderDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        orderBy.value = column;
        orderDirection.value = 'asc';
    }
    updateFilters();
};

// Pagination helpers
const goToPage = (page: number) => {
    if (page < 1 || page > props.tasks.meta.last_page || page === props.tasks.meta.current_page) {
        return;
    }

    const params = new URLSearchParams(window.location.search);
    params.set('page', page.toString());

    router.get(
        `/tasks?${params.toString()}`,
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

// Handle task actions
const handleStatusUpdate = (task: Task, newStatus: string) => {
    router.patch(
        `/tasks/${task.id}/status`,
        {
            status: newStatus,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const handleTogglePublished = (task: Task) => {
    router.patch(
        `/tasks/${task.id}/toggle-published`,
        {},
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const handleDelete = (task: Task) => {
    if (confirm('Are you sure you want to move this task to trash?')) {
        router.delete(`/tasks/${task.id}`, {
            preserveState: true,
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Tasks" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Tasks</h1>
                    <p class="text-muted-foreground">Manage your tasks and track progress</p>
                </div>
                <Link href="/tasks/create">
                    <Button class="cursor-pointer">
                        <Plus class="h-4 w-4" />
                        New Task
                    </Button>
                </Link>
            </div>
            <!-- Tasks Table -->
            <Card>
                <CardContent>
                    <div class="mb-3 flex items-center justify-between">
                        <!-- Search -->
                        <div class="relative w-100">
                            <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input id="search" v-model="searchQuery" placeholder="Search tasks..." class="pl-10" />
                        </div>
                        <!-- Filters -->
                        <div class="flex space-x-3">
                            <!-- Status Filter -->
                            <Select :model-value="selectedStatus" @update:model-value="handleStatusChange">
                                <SelectTrigger>
                                    <SelectValue placeholder="All Statuses" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem v-for="option in statusOptions" :key="option.value" :value="option.value">
                                            {{ option.label }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <!-- Published Filter -->
                            <Select :model-value="selectedPublished" @update:model-value="handlePublishedChange">
                                <SelectTrigger>
                                    <SelectValue placeholder="All Tasks" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="option in publishedOptions" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <!-- Clear filters -->
                            <div v-if="hasFilters" class="flex justify-end">
                                <Button variant="outline" class="h-9" size="sm" @click="clearFilters"> Clear Filters </Button>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="handleSort('title')"
                                            class="h-auto cursor-pointer p-0 font-semibold"
                                        >
                                            Title
                                            <span class="ml-1">
                                                {{ orderDirection === 'asc' ? '↑' : '↓' }}
                                            </span>
                                        </Button>
                                    </TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Publication</TableHead>
                                    <TableHead>Progress</TableHead>
                                    <TableHead>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="handleSort('created_at')"
                                            class="h-auto cursor-pointer p-0 font-semibold"
                                        >
                                            Created
                                            <span class="ml-1">
                                                {{ orderDirection === 'asc' ? '↑' : '↓' }}
                                            </span>
                                        </Button>
                                    </TableHead>
                                    <TableHead class="w-12"></TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="tasks.data.length === 0">
                                    <TableCell colspan="6" class="h-24 text-center">
                                        <div class="flex flex-col items-center gap-2">
                                            <FileText class="h-8 w-8 text-muted-foreground" />
                                            <div class="text-sm text-muted-foreground">
                                                {{ hasFilters ? 'No tasks match your filters' : 'No tasks found' }}
                                            </div>
                                            <div v-if="!hasFilters">
                                                <Link href="/tasks/create">
                                                    <Button size="sm" class="cursor-pointer">
                                                        <Plus class="mr-2 h-4 w-4" />
                                                        Create your first task
                                                    </Button>
                                                </Link>
                                            </div>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-for="task in tasks.data" :key="task.id">
                                    <TableCell>
                                        <div class="flex items-center gap-3">
                                            <div>
                                                <div class="w-70 truncate font-medium">{{ task.title }}</div>
                                                <div class="w-90 truncate text-sm text-muted-foreground">
                                                    {{ task.content }}
                                                </div>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="getStatusVariant(task.status.value)">
                                            <component :is="getStatusIcon(task.status.value)" class="mr-1 h-3 w-3" />
                                            {{ task.status.label }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="task.is_published ? 'default' : 'secondary'">
                                            {{ task.is_published ? 'Published' : 'Draft' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div v-if="task.has_subtasks" class="flex items-center gap-2">
                                            <div class="text-sm">{{ task.completed_subtasks_count }}/{{ task.total_subtasks_count }}</div>
                                            <Progress :model-value="task.subtasks_progress" />
                                        </div>
                                        <div v-else class="text-sm text-muted-foreground">-</div>
                                    </TableCell>
                                    <TableCell class="text-sm text-muted-foreground">
                                        {{ task.created_at }}
                                    </TableCell>
                                    <TableCell>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="sm">
                                                    <MoreHorizontal class="h-4 w-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <Link :href="`/tasks/${task.id}`">
                                                    <DropdownMenuItem>
                                                        <Eye class="mr-2 h-4 w-4" />
                                                        View
                                                    </DropdownMenuItem>
                                                </Link>
                                                <Link :href="`/tasks/${task.id}/edit`">
                                                    <DropdownMenuItem>
                                                        <Edit class="mr-2 h-4 w-4" />
                                                        Edit
                                                    </DropdownMenuItem>
                                                </Link>

                                                <DropdownMenuSeparator />

                                                <!-- Status submenu -->
                                                <DropdownMenuItem
                                                    v-for="status in statusOptions"
                                                    :key="status.value"
                                                    @click="handleStatusUpdate(task, status.value)"
                                                    :class="{ 'bg-accent': task.status.value === status.value }"
                                                >
                                                    <component :is="getStatusIcon(status.value)" class="mr-2 h-4 w-4" />
                                                    {{ status.label }}
                                                </DropdownMenuItem>

                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem @click="handleTogglePublished(task)">
                                                    {{ task.is_published ? 'Make Draft' : 'Publish' }}
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem @click="handleDelete(task)" class="text-destructive focus:text-destructive">
                                                    <Trash2 class="mr-2 h-4 w-4" />
                                                    Delete
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                    <div class="mt-6 flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm text-muted-foreground">
                            Showing {{ tasks.meta.from }}-{{ tasks.meta.to }} of {{ tasks.meta.total }}
                        </div>
                        <!-- Pagination -->
                        <div v-if="tasks.meta.last_page > 1" class="flex items-center justify-center">
                            <Pagination
                                v-slot="{ page }"
                                :items-per-page="tasks.meta.per_page"
                                :total="tasks.meta.total"
                                :default-page="tasks.meta.current_page"
                            >
                                <PaginationContent v-slot="{ items }">
                                    <PaginationPrevious
                                        @click="goToPage(tasks.meta.current_page - 1)"
                                        :class="{ 'pointer-events-none opacity-50': tasks.meta.current_page <= 1 }"
                                    />

                                    <template v-for="(item, index) in items" :key="index">
                                        <PaginationItem
                                            v-if="item.type === 'page'"
                                            :value="item.value"
                                            :is-active="item.value === page"
                                            @click="goToPage(item.value)"
                                            class="cursor-pointer"
                                        >
                                            {{ item.value }}
                                        </PaginationItem>
                                        <PaginationEllipsis v-else-if="item.type === 'ellipsis'" :key="`ellipsis-${index}`" />
                                    </template>

                                    <PaginationNext
                                        @click="goToPage(tasks.meta.current_page + 1)"
                                        :class="{ 'pointer-events-none opacity-50': tasks.meta.current_page >= tasks.meta.last_page }"
                                    />
                                </PaginationContent>
                            </Pagination>
                        </div>

                        <!-- Per Page -->
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-muted-foreground">Per page</label>
                            <Select :model-value="perPage.toString()" @update:model-value="handlePerPageChange">
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="option in perPageOptions" :key="option" :value="option.toString()">
                                        {{ option }} items
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
