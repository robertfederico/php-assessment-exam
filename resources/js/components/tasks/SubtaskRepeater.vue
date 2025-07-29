<script setup lang="ts">
import { v4 as uuidv4 } from 'uuid';
import { computed, ref, watch } from 'vue';
// shadcn components
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { CheckCircle2, Plus, Trash2 } from 'lucide-vue-next';
// interface
import { Subtask } from '@/interfaces/task-intefaces';

interface Props {
    subtasks: Subtask[];
    errors?: any;
}

interface Emits {
    (e: 'update:subtasks', subtasks: Subtask[]): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// Local subtasks state
const localSubtasks = ref<Subtask[]>([...props.subtasks]);

// Watch for changes from parent
watch(
    () => props.subtasks,
    (newSubtasks) => {
        localSubtasks.value = [...newSubtasks];
    },
    { deep: true },
);

// Computed
const hasSubtasks = computed(() => localSubtasks.value.length > 0);
const completedCount = computed(() => localSubtasks.value.filter((s) => s.completed).length);

// Emit changes
const emitUpdate = () => {
    emit('update:subtasks', [...localSubtasks.value]);
};

// Add new subtask
const addSubtask = () => {
    const newSubtask: Subtask = {
        id: uuidv4(),
        title: '',
        completed: false,
        created_at: new Date().toISOString(),
    };

    localSubtasks.value.push(newSubtask);
    emitUpdate();
    setTimeout(() => {
        const inputs = document.querySelectorAll('[data-subtask-input]');
        const lastInput = inputs[inputs.length - 1] as HTMLInputElement;
        if (lastInput) {
            lastInput.focus();
        }
    }, 10);
};

// Remove subtask
const removeSubtask = (index: number) => {
    localSubtasks.value.splice(index, 1);
    emitUpdate();
};

// Toggle subtask completion
const toggleSubtaskCompleted = (index: number, completed: boolean): void => {
    localSubtasks.value[index].completed = completed;
    emitUpdate();
};
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <p v-if="hasSubtasks" class="text-sm text-muted-foreground">{{ completedCount }} of {{ localSubtasks.length }} completed</p>
                <p v-else class="text-sm text-muted-foreground">No subtasks added yet</p>
            </div>
            <Button type="button" variant="outline" size="sm" @click="addSubtask">
                <Plus class="mr-2 h-4 w-4" />
                Add Subtask
            </Button>
        </div>

        <!-- Subtasks List -->
        <div v-if="hasSubtasks" class="space-y-2">
            <Card v-for="(subtask, index) in localSubtasks" :key="subtask.id" class="relative p-3">
                <CardContent>
                    <div class="flex items-center gap-3">
                        <!-- Completion Checkbox -->
                        <Checkbox
                            :id="`subtask-${subtask.id}`"
                            :model-value="subtask.completed"
                            @update:model-value="(checked: any) => toggleSubtaskCompleted(index, checked as boolean)"
                        />
                        <!-- Title Input -->
                        <div class="flex-1">
                            <Input
                                :data-subtask-input="true"
                                v-model="localSubtasks[index].title"
                                @input="emitUpdate"
                                placeholder="Enter subtask title..."
                                :class="{
                                    'text-muted-foreground line-through': subtask.completed,
                                    'border-destructive': errors && errors[index] && errors[index].title,
                                }"
                                maxlength="50"
                            />
                            <span v-if="errors && errors[index] && errors[index].title" class="mt-1 block text-xs text-destructive">
                                {{ errors[index].title }}
                            </span>
                        </div>

                        <!-- Remove Button -->
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            @click="removeSubtask(index)"
                            class="text-destructive hover:bg-destructive/10 hover:text-destructive"
                            title="Remove subtask"
                        >
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Empty State -->
        <div v-else class="rounded-lg border-2 border-dashed border-muted-foreground/25 p-8 text-center">
            <CheckCircle2 class="mx-auto mb-2 h-8 w-8 text-muted-foreground" />
            <p class="mb-3 text-sm text-muted-foreground">Break your task into smaller steps</p>
            <Button type="button" variant="outline" size="sm" @click="addSubtask">
                <Plus class="mr-2 h-4 w-4" />
                Add First Subtask
            </Button>
        </div>

        <!-- Progress Indicator -->
        <div v-if="hasSubtasks && completedCount > 0" class="mt-4">
            <div class="mb-2 flex items-center justify-between text-sm">
                <span class="text-muted-foreground">Progress</span>
                <span class="font-medium">{{ Math.round((completedCount / localSubtasks.length) * 100) }}%</span>
            </div>
            <div class="h-2 w-full overflow-hidden rounded-full bg-secondary">
                <div
                    class="h-full bg-primary transition-all duration-300"
                    :style="{ width: `${(completedCount / localSubtasks.length) * 100}%` }"
                ></div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.line-through {
    text-decoration: line-through;
}
</style>
