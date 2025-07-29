<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Alert, AlertDescription } from '@/components/ui/alert';
import SubtaskRepeater from '@/components/tasks/SubtaskRepeater.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    ArrowLeft,
    Save,
    Upload,
    X,
    AlertCircle,
    FileText,
    Image as ImageIcon,
    Eye,
    EyeOff
} from 'lucide-vue-next';

// Types
interface TaskStatus {
    value: string;
    label: string;
}

interface StatusOption {
    value: string;
    label: string;
}

interface Subtask {
    id: string;
    title: string;
    completed: boolean;
    created_at?: string;
}

interface Task {
    id: number;
    title: string;
    content: string;
    status: TaskStatus;
    is_published: boolean;
    image_url: string | null;
    image_path: string | null;
    subtasks: Subtask[] | null;
    subtasks_progress: number;
    total_subtasks_count: number;
    completed_subtasks_count: number;
    has_subtasks: boolean;
    created_at: string;
    updated_at: string;
}

interface Props {
    task: Task;
    statusOptions: StatusOption[];
}

const props = defineProps<Props>();

// Form data - pre-populated with existing task data
const form = useForm({
    title: props.task.title,
    content: props.task.content,
    status: props.task.status.value,
    is_published: props.task.is_published,
    image: null as File | null,
    subtasks: (props.task.subtasks || []) as Subtask[],
});

// Reactive refs
const imageInput = ref<HTMLInputElement>();
const imagePreview = ref<string | null>(null);
const removeExistingImage = ref(false);

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
    {
        title: 'Edit',
        href: `/tasks/${props.task.id}/edit`,
    },
];

// Computed properties
const hasNewImage = computed(() => form.image !== null);
const hasExistingImage = computed(() => props.task.image_url !== null && !removeExistingImage.value);
const hasAnyImage = computed(() => hasNewImage.value || hasExistingImage.value);
const hasSubtasks = computed(() => form.subtasks.length > 0);

// Get current status info
const currentStatusOption = computed(() => 
    props.statusOptions.find(option => option.value === form.status)
);

// Image handling
const handleImageSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    
    if (file) {
        // Validate file size (4MB)
        if (file.size > 4 * 1024 * 1024) {
            alert('Image must be less than 4MB');
            return;
        }
        
        // Validate file type
        if (!file.type.startsWith('image/')) {
            alert('Please select an image file');
            return;
        }
        
        form.image = file;
        removeExistingImage.value = false;
        
        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const removeNewImage = () => {
    form.image = null;
    imagePreview.value = null;
    if (imageInput.value) {
        imageInput.value.value = '';
    }
};

const removeCurrentImage = () => {
    removeExistingImage.value = true;
    form.image = null;
    imagePreview.value = null;
    if (imageInput.value) {
        imageInput.value.value = '';
    }
};

const triggerImageUpload = () => {
    imageInput.value?.click();
};

// Subtask handling
const handleSubtasksUpdate = (subtasks: Subtask[]) => {
    form.subtasks = subtasks;
};

// Form submission
const submitForm = () => {
    // Handle image removal
    const formData = {
        ...form.data(),
        remove_image: removeExistingImage.value,
    };

    form.transform((data) => ({
        ...data,
        remove_image: removeExistingImage.value,
        _method: 'PUT',
    })).post(`/tasks/${props.task.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            // Form will redirect on success
        },
        onError: (errors) => {
            console.error('Validation errors:', errors);
        }
    });
};

// Status change handler
const handleStatusChange = (value: string) => {
    form.status = value;
};
</script>

<template>
    <Head :title="`Edit ${task.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Edit Task</h1>
                    <p class="text-muted-foreground">
                        Update your task details
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="`/tasks/${task.id}`">
                        <Button variant="outline" class="cursor-pointer">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Back to Task
                        </Button>
                    </Link>
                    <!-- Submit Actions -->
                    <Button 
                        @click="submitForm" 
                        :disabled="form.processing"
                        class="cursor-pointer"
                    >
                        <Save class="mr-2 h-4 w-4" />
                        {{ form.processing ? 'Updating...' : 'Update Task' }}
                    </Button>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submitForm" class="space-y-6">
                <div class="grid gap-6 lg:grid-cols-3">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Basic Information -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <FileText class="h-5 w-5" />
                                    Task Details
                                </CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <!-- Title -->
                                <div class="space-y-2">
                                    <Label for="title">
                                        Title <span class="text-destructive">*</span>
                                    </Label>
                                    <Input
                                        id="title"
                                        v-model="form.title"
                                        placeholder="Enter task title..."
                                        maxlength="100"
                                        :class="{ 'border-destructive': form.errors.title }"
                                        required
                                    />
                                    <div class="flex justify-between text-sm text-muted-foreground">
                                        <span v-if="form.errors.title" class="text-destructive">
                                            {{ form.errors.title }}
                                        </span>
                                        <span class="ml-auto">
                                            {{ form.title.length }}/100
                                        </span>
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="space-y-2">
                                    <Label for="content">
                                        Description <span class="text-destructive">*</span>
                                    </Label>
                                    <Textarea
                                        id="content"
                                        v-model="form.content"
                                        placeholder="Describe your task..."
                                        rows="4"
                                        :class="{ 'border-destructive': form.errors.content }"
                                        required
                                    />
                                    <span v-if="form.errors.content" class="text-sm text-destructive">
                                        {{ form.errors.content }}
                                    </span>
                                </div>

                                <!-- Status -->
                                <div class="space-y-2">
                                    <Label>
                                        Status <span class="text-destructive">*</span>
                                    </Label>
                                    <Select :model-value="form.status" @update:model-value="handleStatusChange">
                                        <SelectTrigger :class="{ 'border-destructive': form.errors.status }">
                                            <SelectValue placeholder="Select status" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem 
                                                v-for="option in statusOptions" 
                                                :key="option.value" 
                                                :value="option.value"
                                            >
                                                {{ option.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <span v-if="form.errors.status" class="text-sm text-destructive">
                                        {{ form.errors.status }}
                                    </span>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Subtasks -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Subtasks</CardTitle>
                                <p class="text-sm text-muted-foreground">
                                    Update or add subtasks to break down your task.
                                </p>
                            </CardHeader>
                            <CardContent>
                                <SubtaskRepeater 
                                    :subtasks="form.subtasks"
                                    @update:subtasks="handleSubtasksUpdate"
                                    :errors="form.errors.subtasks"
                                />
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Publication Settings -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Publication</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="flex items-center justify-between">
                                    <div class="space-y-1">
                                        <Label>Published status</Label>
                                        <p class="text-sm text-muted-foreground">
                                            Control task visibility
                                        </p>
                                    </div>
                                    <Switch
                                        v-model:checked="form.is_published"
                                        :class="{ 'border-destructive': form.errors.is_published }"
                                    />
                                </div>
                                <span v-if="form.errors.is_published" class="text-sm text-destructive">
                                    {{ form.errors.is_published }}
                                </span>
                            </CardContent>
                        </Card>

                        <!-- Image Upload -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <ImageIcon class="h-5 w-5" />
                                    Attachment
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-4">
                                    <!-- Current Image -->
                                    <div v-if="hasExistingImage && !hasNewImage" class="relative">
                                        <img 
                                            :src="task.image_url" 
                                            alt="Current attachment" 
                                            class="w-full h-32 object-cover rounded-lg"
                                        />
                                        <div class="absolute inset-0 bg-black/40 opacity-0 hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center gap-2">
                                            <Button
                                                type="button"
                                                size="sm"
                                                variant="secondary"
                                                @click="triggerImageUpload"
                                            >
                                                Replace
                                            </Button>
                                            <Button
                                                type="button"
                                                size="sm"
                                                variant="destructive"
                                                @click="removeCurrentImage"
                                            >
                                                <X class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>

                                    <!-- New Image Preview -->
                                    <div v-if="hasNewImage && imagePreview" class="relative">
                                        <img 
                                            :src="imagePreview" 
                                            alt="New preview" 
                                            class="w-full h-32 object-cover rounded-lg"
                                        />
                                        <Button
                                            type="button"
                                            size="sm"
                                            variant="destructive"
                                            class="absolute top-2 right-2"
                                            @click="removeNewImage"
                                        >
                                            <X class="h-4 w-4" />
                                        </Button>
                                        <div class="absolute bottom-2 left-2 bg-green-600 text-white text-xs px-2 py-1 rounded">
                                            New Image
                                        </div>
                                    </div>

                                    <!-- Upload Area -->
                                    <div 
                                        v-if="!hasAnyImage"
                                        @click="triggerImageUpload"
                                        class="border-2 border-dashed border-muted-foreground/25 rounded-lg p-6 text-center cursor-pointer hover:border-muted-foreground/50 transition-colors"
                                        :class="{ 'border-destructive': form.errors.image }"
                                    >
                                        <Upload class="mx-auto h-8 w-8 text-muted-foreground" />
                                        <p class="mt-2 text-sm text-muted-foreground">
                                            Click to upload an image
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            PNG, JPG, GIF up to 4MB
                                        </p>
                                    </div>

                                    <!-- Upload Button for existing image -->
                                    <div v-if="hasExistingImage && !hasNewImage" class="text-center">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            @click="triggerImageUpload"
                                        >
                                            <Upload class="mr-2 h-4 w-4" />
                                            Replace Image
                                        </Button>
                                    </div>

                                    <!-- Hidden file input -->
                                    <input
                                        ref="imageInput"
                                        type="file"
                                        accept="image/*"
                                        class="hidden"
                                        @change="handleImageSelect"
                                    />

                                    <span v-if="form.errors.image" class="text-sm text-destructive">
                                        {{ form.errors.image }}
                                    </span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <!-- Global Errors -->
                <Alert v-if="Object.keys(form.errors).length > 0" variant="destructive">
                    <AlertCircle class="h-4 w-4" />
                    <AlertDescription>
                        Please fix the validation errors above before submitting.
                    </AlertDescription>
                </Alert>
            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
.line-through {
    text-decoration: line-through;
}
</style>