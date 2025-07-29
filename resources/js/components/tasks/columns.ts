// components/tasks/columns.ts
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Link, router } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import { ArrowUpDown, CheckCircle, Circle, Clock, Edit, Eye, MoreHorizontal, Trash2 } from 'lucide-vue-next';
import { h } from 'vue';

// Types
export interface TaskStatus {
    value: string;
    label: string;
}

export interface Task {
    id: number;
    title: string;
    content: string;
    status: TaskStatus;
    is_published: boolean;
    image_url: string | null;
    subtasks_progress: number;
    total_subtasks_count: number;
    completed_subtasks_count: number;
    has_subtasks: boolean;
    created_at: string;
    updated_at: string;
}

export interface StatusOption {
    value: string;
    label: string;
}

// Helper functions
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

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
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

export const createColumns = (statusOptions: StatusOption[]): ColumnDef<Task>[] => [
    {
        accessorKey: 'title',
        header: ({ column }) => {
            return h(
                Button,
                {
                    variant: 'ghost',
                    size: 'sm',
                    class: 'h-auto p-0 font-semibold',
                    onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
                },
                () => ['Title', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })],
            );
        },
        cell: ({ row }) => {
            const task = row.original;
            return h('div', { class: 'flex items-center gap-3' }, [
                h('div', {}, [
                    h('div', { class: 'w-80 truncate font-medium' }, task.title),
                    h('div', { class: 'w-100 truncate text-sm text-muted-foreground' }, task.content),
                ]),
            ]);
        },
    },
    {
        accessorKey: 'status',
        header: 'Status',
        cell: ({ row }) => {
            const task = row.original;
            const StatusIcon = getStatusIcon(task.status.value);

            return h(
                Badge,
                {
                    variant: getStatusVariant(task.status.value),
                },
                () => [h(StatusIcon, { class: 'mr-1 h-3 w-3' }), task.status.label],
            );
        },
    },
    {
        accessorKey: 'is_published',
        header: 'Publication',
        cell: ({ row }) => {
            const task = row.original;
            return h(
                Badge,
                {
                    variant: task.is_published ? 'default' : 'secondary',
                },
                () => (task.is_published ? 'Published' : 'Draft'),
            );
        },
    },
    {
        id: 'progress',
        header: 'Progress',
        cell: ({ row }) => {
            const task = row.original;

            if (task.has_subtasks) {
                return h('div', { class: 'flex items-center gap-2' }, [
                    h('div', { class: 'text-sm' }, `${task.completed_subtasks_count}/${task.total_subtasks_count}`),
                    h('div', { class: 'h-2 w-16 overflow-hidden rounded-full bg-secondary' }, [
                        h('div', {
                            class: 'h-full bg-primary transition-all duration-300',
                            style: { width: `${task.subtasks_progress}%` },
                        }),
                    ]),
                    h('div', { class: 'text-xs text-muted-foreground' }, `${Math.round(task.subtasks_progress)}%`),
                ]);
            }

            return h('div', { class: 'text-sm text-muted-foreground' }, 'No subtasks');
        },
    },
    {
        accessorKey: 'created_at',
        header: ({ column }) => {
            return h(
                Button,
                {
                    variant: 'ghost',
                    size: 'sm',
                    class: 'h-auto p-0 font-semibold',
                    onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
                },
                () => ['Created', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })],
            );
        },
        cell: ({ row }) => {
            return h('div', { class: 'text-sm text-muted-foreground' }, formatDate(row.getValue('created_at')));
        },
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
            const task = row.original;

            return h(
                'div',
                { class: 'relative' },
                h(
                    DropdownMenu,
                    {},
                    {
                        default: () => [
                            h(
                                DropdownMenuTrigger,
                                { asChild: true },
                                {
                                    default: () =>
                                        h(
                                            Button,
                                            {
                                                variant: 'ghost',
                                                size: 'sm',
                                            },
                                            {
                                                default: () => h(MoreHorizontal, { class: 'h-4 w-4' }),
                                            },
                                        ),
                                },
                            ),
                            h(
                                DropdownMenuContent,
                                { align: 'end' },
                                {
                                    default: () => [
                                        h(
                                            Link,
                                            { href: `/tasks/${task.id}` },
                                            {
                                                default: () =>
                                                    h(
                                                        DropdownMenuItem,
                                                        {},
                                                        {
                                                            default: () => [h(Eye, { class: 'mr-2 h-4 w-4' }), 'View'],
                                                        },
                                                    ),
                                            },
                                        ),
                                        h(
                                            Link,
                                            { href: `/tasks/${task.id}/edit` },
                                            {
                                                default: () =>
                                                    h(
                                                        DropdownMenuItem,
                                                        {},
                                                        {
                                                            default: () => [h(Edit, { class: 'mr-2 h-4 w-4' }), 'Edit'],
                                                        },
                                                    ),
                                            },
                                        ),
                                        h(DropdownMenuSeparator),

                                        // Status options
                                        ...statusOptions.map((status) =>
                                            h(
                                                DropdownMenuItem,
                                                {
                                                    onClick: () => handleStatusUpdate(task, status.value),
                                                    class: task.status.value === status.value ? 'bg-accent' : '',
                                                },
                                                {
                                                    default: () => [h(getStatusIcon(status.value), { class: 'mr-2 h-4 w-4' }), status.label],
                                                },
                                            ),
                                        ),

                                        h(DropdownMenuSeparator),
                                        h(
                                            DropdownMenuItem,
                                            {
                                                onClick: () => handleTogglePublished(task),
                                            },
                                            {
                                                default: () => (task.is_published ? 'Make Draft' : 'Publish'),
                                            },
                                        ),
                                        h(DropdownMenuSeparator),
                                        h(
                                            DropdownMenuItem,
                                            {
                                                onClick: () => handleDelete(task),
                                                class: 'text-destructive focus:text-destructive',
                                            },
                                            {
                                                default: () => [h(Trash2, { class: 'mr-2 h-4 w-4' }), 'Delete'],
                                            },
                                        ),
                                    ],
                                },
                            ),
                        ],
                    },
                ),
            );
        },
    },
];
