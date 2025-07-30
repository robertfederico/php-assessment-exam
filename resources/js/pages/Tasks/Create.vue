<script setup lang="ts">
import { computed, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SubtaskRepeater from '@/components/tasks/SubtaskRepeater.vue';
// shadcn components
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { AlertCircle, ArrowLeft, FileText, Image as ImageIcon, Save, Upload, X } from 'lucide-vue-next';
// interface
import { StatusOption, Subtask } from '@/interfaces/task-intefaces';

interface Props {
    statusOptions: StatusOption[];
}

defineProps<Props>();

// Form data
const form = useForm({
    title: '',
    content: '',
    status: 'to-do',
    is_published: true,
    image: null as File | null,
    subtasks: [],
});

// Reactive refs
const imageInput = ref<HTMLInputElement>();
const imagePreview = ref<string | null>(null);

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tasks',
        href: '/tasks',
    },
    {
        title: 'Create Task',
        href: '/tasks/create',
    },
];

// Computed properties
const hasImage = computed(() => form.image !== null);
const hasSubtasks = computed(() => form.subtasks?.length > 0);

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

        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const removeImage = () => {
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
    form.post('/tasks', {
        preserveScroll: true,
        onSuccess: () => {},
        onError: (errors) => {
            console.error('Validation errors:', errors);
        },
    });
};

// Status change handler
const handleStatusChange = (value: string) => {
    form.status = value;
};
</script>

<template>
    <Head title="Create Task" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Create Task</h1>
                    <p class="text-sm text-muted-foreground">Add a new task to your list</p>
                </div>
                <!-- Submit Actions -->
                <div class="flex items-center justify-end gap-4">
                    <Link href="/tasks">
                        <Button variant="outline" class="cursor-pointer" :disabled="form.processing">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Back
                        </Button>
                    </Link>
                    <Button type="button" @click="submitForm" :disabled="form.processing" class="cursor-pointer">
                        <Save class="h-4 w-4" />
                        {{ form.processing ? 'Creating...' : 'Create Task' }}
                    </Button>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submitForm" class="space-y-6">
                <!-- Global Errors -->
                <Alert v-if="Object.keys(form.errors).length > 0" variant="destructive">
                    <AlertCircle class="h-4 w-4" />
                    <AlertDescription> Please fix the validation errors below before submitting. </AlertDescription>
                </Alert>

                <div class="grid gap-6 lg:grid-cols-3">
                    <!-- Main Content -->
                    <div class="space-y-6 lg:col-span-2">
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
                                    <Label for="title"> Title <span class="text-destructive">*</span> </Label>
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
                                        <span class="ml-auto"> {{ form.title.length }}/100 </span>
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="space-y-2">
                                    <Label for="content"> Description <span class="text-destructive">*</span> </Label>
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
                                    <Label> Status <span class="text-destructive">*</span> </Label>
                                    <Select :model-value="form.status" @update:model-value="handleStatusChange">
                                        <SelectTrigger :class="{ 'border-destructive': form.errors.status }">
                                            <SelectValue placeholder="Select status" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="option in statusOptions" :key="option.value" :value="option.value">
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
                                <CardTitle>Subtasks (Optional)</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <SubtaskRepeater :subtasks="form.subtasks" @update:subtasks="handleSubtasksUpdate" :errors="form.errors.subtasks" />
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Publication -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Publication</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="flex items-center justify-between">
                                    <div class="space-y-1">
                                        <Label>Publish immediately</Label>
                                        <p class="text-sm text-muted-foreground">Make this task visible in your list</p>
                                    </div>
                                    <Switch v-model="form.is_published" :class="{ 'border-destructive': form.errors.is_published }" />
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
                                    Attachment (Optional)
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-4">
                                    <!-- Upload Area -->
                                    <div
                                        v-if="!hasImage"
                                        @click="triggerImageUpload"
                                        class="cursor-pointer rounded-lg border-2 border-dashed border-muted-foreground/25 p-6 text-center transition-colors hover:border-muted-foreground/50"
                                        :class="{ 'border-destructive': form.errors.image }"
                                    >
                                        <Upload class="mx-auto h-8 w-8 text-muted-foreground" />
                                        <p class="mt-2 text-sm text-muted-foreground">Click to upload an image</p>
                                        <p class="text-xs text-muted-foreground">PNG, JPG, GIF up to 4MB</p>
                                    </div>

                                    <!-- Image Preview -->
                                    <div v-if="hasImage && imagePreview" class="relative">
                                        <img :src="imagePreview" alt="Preview" class="h-32 w-full rounded-lg object-cover" />
                                        <Button type="button" size="sm" variant="destructive" class="absolute top-2 right-2" @click="removeImage">
                                            <X class="h-4 w-4" />
                                        </Button>
                                    </div>

                                    <!-- Hidden file input -->
                                    <input ref="imageInput" type="file" accept="image/*" class="hidden" @change="handleImageSelect" />

                                    <span v-if="form.errors.image" class="text-sm text-destructive">
                                        {{ form.errors.image }}
                                    </span>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Summary -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Summary</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-muted-foreground">Status:</span>
                                    <span class="font-medium">
                                        {{ statusOptions.find((s) => s.value === form.status)?.label }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-muted-foreground">Publication:</span>
                                    <span class="font-medium">
                                        {{ form.is_published ? 'Published' : 'Draft' }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-muted-foreground">Subtasks:</span>
                                    <span class="font-medium">
                                        {{ hasSubtasks ? `${form.subtasks.length} subtasks` : 'None' }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-muted-foreground">Attachment:</span>
                                    <span class="font-medium">
                                        {{ hasImage ? 'Yes' : 'None' }}
                                    </span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
