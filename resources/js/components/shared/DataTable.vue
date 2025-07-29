<script setup lang="ts" generic="TData, TValue">
import type {
    ColumnDef,
    ColumnFiltersState,
    SortingState,
} from '@tanstack/vue-table'
import {
    FlexRender,
    getCoreRowModel,
    getFilteredRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    useVueTable,
} from '@tanstack/vue-table'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination'
import { Card, CardContent } from '@/components/ui/card'
import { Search, FileText, Plus } from 'lucide-vue-next'
import { Link } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { valueUpdater } from '@/lib/utils'

interface Props {
    columns: ColumnDef<TData, TValue>[]
    data: TData[]
    meta: {
        current_page: number
        last_page: number
        per_page: number
        total: number
        from: number | null
        to: number | null
    }
    statusOptions: Array<{ value: string; label: string }>
    perPageOptions: number[]
    filters: {
        search?: string
        status?: string
        is_published?: boolean
    }
    onFilterChange: (filters: any) => void
    onPageChange: (page: number) => void
    onPerPageChange: (perPage: number) => void
    onSort: (column: string) => void
    orderBy: string
    orderDirection: string
}

const props = defineProps<Props>()

// Local reactive state for filters
const searchQuery = ref(props.filters.search || '')
const selectedStatus = ref(props.filters.status || '')
const selectedPublished = ref(
    props.filters.is_published !== undefined 
        ? props.filters.is_published?.toString() 
        : ''
)

// Computed properties
const hasFilters = computed(() => {
    return searchQuery.value || selectedStatus.value || selectedPublished.value
})

const publishedOptions = [
    { value: 'all', label: 'All Tasks' },
    { value: 'true', label: 'Published' },
    { value: 'false', label: 'Drafts' },
]

// TanStack Table setup
const sorting = ref<SortingState>([])
const columnFilters = ref<ColumnFiltersState>([])

const table = useVueTable({
    get data() { return props.data },
    get columns() { return props.columns },
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    onSortingChange: updaterOrValue => valueUpdater(updaterOrValue, sorting),
    onColumnFiltersChange: updaterOrValue => valueUpdater(updaterOrValue, columnFilters),
    state: {
        get sorting() { return sorting.value },
        get columnFilters() { return columnFilters.value },
    },
    manualPagination: true,
    manualSorting: true,
    manualFiltering: true,
    pageCount: props.meta.last_page,
})

// Watch for search changes and emit to parent
watch(searchQuery, (newValue) => {
    props.onFilterChange({
        search: newValue,
        status: selectedStatus.value,
        is_published: selectedPublished.value ? selectedPublished.value === 'true' : undefined
    })
})

// Handle filter changes
const handleStatusChange = (value: string) => {
    selectedStatus.value = value
    props.onFilterChange({
        search: searchQuery.value,
        status: value,
        is_published: selectedPublished.value ? selectedPublished.value === 'true' : undefined
    })
}

const handlePublishedChange = (value: string) => {
    selectedPublished.value = value
    props.onFilterChange({
        search: searchQuery.value,
        status: selectedStatus.value,
        is_published: value ? value === 'true' : undefined
    })
}

const handlePerPageChange = (value: string) => {
    props.onPerPageChange(parseInt(value))
}

const clearFilters = () => {
    searchQuery.value = ''
    selectedStatus.value = ''
    selectedPublished.value = ''
    props.onFilterChange({
        search: '',
        status: '',
        is_published: undefined
    })
}

// Pagination helpers
const goToPage = (page: number) => {
    if (page < 1 || page > props.meta.last_page || page === props.meta.current_page) {
        return
    }
    props.onPageChange(page)
}
</script>

<template>
    <Card>
        <CardContent>
            <!-- Filters -->
            <div class="mb-3 flex items-center justify-between">
                <!-- Search -->
                <div class="relative w-100">
                    <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input 
                        id="search" 
                        v-model="searchQuery" 
                        placeholder="Search tasks..." 
                        class="pl-10" 
                    />
                </div>
                
                <!-- Filter Controls -->
                <div class="flex space-x-3">
                    <!-- Status Filter -->
                    <Select :model-value="selectedStatus" @update:model-value="handleStatusChange">
                        <SelectTrigger>
                            <SelectValue placeholder="All Statuses" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="">All Statuses</SelectItem>
                            <SelectItem 
                                v-for="option in statusOptions" 
                                :key="option.value" 
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    
                    <!-- Published Filter -->
                    <Select :model-value="selectedPublished" @update:model-value="handlePublishedChange">
                        <SelectTrigger>
                            <SelectValue placeholder="All Tasks" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem 
                                v-for="option in publishedOptions" 
                                :key="option.value" 
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    
                    <!-- Clear filters -->
                    <div v-if="hasFilters" class="flex justify-end">
                        <Button variant="outline" class="h-9" size="sm" @click="clearFilters"> 
                            Clear Filters 
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                            <TableHead v-for="header in headerGroup.headers" :key="header.id">
                                <FlexRender
                                    v-if="!header.isPlaceholder"
                                    :render="header.column.columnDef.header"
                                    :props="header.getContext()"
                                />
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <template v-if="table.getRowModel().rows?.length">
                            <TableRow
                                v-for="row in table.getRowModel().rows"
                                :key="row.id"
                                :data-state="row.getIsSelected() ? 'selected' : undefined"
                            >
                                <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                                    <FlexRender 
                                        :render="cell.column.columnDef.cell" 
                                        :props="cell.getContext()" 
                                    />
                                </TableCell>
                            </TableRow>
                        </template>
                        <template v-else>
                            <TableRow>
                                <TableCell :colspan="columns.length" class="h-24 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <FileText class="h-8 w-8 text-muted-foreground" />
                                        <div class="text-sm text-muted-foreground">
                                            {{ hasFilters ? 'No tasks match your filters' : 'No tasks found' }}
                                        </div>
                                        <div v-if="!hasFilters">
                                            <Link href="/tasks/create">
                                                <Button size="sm">
                                                    <Plus class="mr-2 h-4 w-4" />
                                                    Create your first task
                                                </Button>
                                            </Link>
                                        </div>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </template>
                    </TableBody>
                </Table>
            </div>

            <!-- Footer with pagination and per-page selector -->
            <div class="mt-6 flex items-center justify-between">
                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                    <span v-if="meta.from && meta.to">
                        Showing {{ meta.from }}-{{ meta.to }} of {{ meta.total }}
                    </span>
                    <span v-else-if="meta.total > 0">
                        Showing {{ meta.total }} of {{ meta.total }}
                    </span>
                    <span v-else>
                        No results
                    </span>
                </div>
                
                <!-- Pagination -->
                <div v-if="meta.last_page > 1" class="flex items-center justify-center">
                    <Pagination 
                        v-slot="{ page }" 
                        :items-per-page="meta.per_page" 
                        :total="meta.total" 
                        :default-page="meta.current_page"
                    >
                        <PaginationContent v-slot="{ items }">
                            <PaginationPrevious 
                                @click="goToPage(meta.current_page - 1)"
                                :class="{ 'pointer-events-none opacity-50': meta.current_page <= 1 }"
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
                                @click="goToPage(meta.current_page + 1)"
                                :class="{ 'pointer-events-none opacity-50': meta.current_page >= meta.last_page }"
                            />
                        </PaginationContent>
                    </Pagination>
                </div>

                <!-- Per Page Selector -->
                <div class="flex items-center gap-2">
                    <Label class="text-sm text-muted-foreground">Per page</Label>
                    <Select :model-value="meta.per_page?.toString()" @update:model-value="handlePerPageChange">
                        <SelectTrigger class="w-20">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem 
                                v-for="option in perPageOptions" 
                                :key="option" 
                                :value="option.toString()"
                            >
                                {{ option }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>
        </CardContent>
    </Card>
</template>

<style scoped>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>