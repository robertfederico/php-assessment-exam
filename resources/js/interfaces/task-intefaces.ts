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
    subtasks: Subtask[] | null;
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

/**
 * Subtasks Types
 */

export interface Subtask {
    id: string;
    title: string;
    completed: boolean;
    created_at?: string;
}