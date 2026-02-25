<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    fetchUrl: {
        type: String,
        required: true,
    },
    initialFilters: {
        type: Object,
        default: () => ({}),
    },
    tableClass: {
        type: String,
        default: '',
    },
    headerClass: {
        type: String,
        default: '',
    },
    theadClass: {
        type: String,
        default: '',
    },
    tbodyClass: {
        type: String,
        default: '',
    },
    paginationClass: {
        type: String,
        default: '',
    },
    /**
     * Column definitions for table headers and mobile card layout.
     * Each column: { name: string, label: string, thClass?: string, mobileHidden?: boolean }
     * - name: key to match slot data attributes (data-column="name")
     * - label: displayed label in table headers and mobile cards
     * - thClass: custom CSS classes for the <th> element (desktop only)
     * - mobileHidden: if true, column is hidden on mobile
     */
    columns: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['loaded']);

// Internal state — nothing touches the URL
const items = ref([]);
const meta = ref({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
    from: 0,
    to: 0,
});
const filters = ref({ ...props.initialFilters });
const loading = ref(false);

// Pagination is only visible when there is more than 1 page
const showPagination = computed(() => meta.value.last_page > 1);

// Visible page numbers (up to 5, centered around current page)
const visiblePages = computed(() => {
    const current = meta.value.current_page;
    const last = meta.value.last_page;
    const target = 5;
    let start = Math.max(1, current - Math.floor(target / 2));
    let end = Math.min(last, start + target - 1);
    if (end - start + 1 < target) {
        start = Math.max(1, end - target + 1);
    }
    const pages = [];
    for (let i = start; i <= end; i++) {
        pages.push(i);
    }
    return pages;
});

const fetchData = async (page = 1) => {
    loading.value = true;
    try {
        const params = { page, ...filters.value };
        // Remove empty/null values
        Object.keys(params).forEach((key) => {
            if (params[key] === null || params[key] === undefined || params[key] === '') {
                delete params[key];
            }
        });

        const response = await axios.get(props.fetchUrl, { params });
        const data = response.data;

        items.value = data.data ?? [];
        meta.value = {
            current_page: data.current_page ?? 1,
            last_page: data.last_page ?? 1,
            per_page: data.per_page ?? 10,
            total: data.total ?? 0,
            from: data.from ?? 0,
            to: data.to ?? 0,
        };

        emit('loaded', { items: items.value, meta: meta.value });
    } catch (error) {
        console.error('DataTable fetch error:', error);
    } finally {
        loading.value = false;
    }
};

const goToPage = (page) => {
    if (page < 1 || page > meta.value.last_page) return;
    fetchData(page);
};

// Expose search method so parent (via slot) can trigger a search
const search = (newFilters = {}) => {
    filters.value = { ...newFilters };
    fetchData(1);
};

const refresh = () => {
    fetchData(meta.value.current_page);
};

// Fetch initial data on mount
onMounted(() => {
    fetchData(1);
});

// Expose to parent via defineExpose
defineExpose({ search, refresh, filters });
</script>

<template>
    <div>
        <!-- Slot 1: Header (title, search button, add new button, etc.) -->
        <div :class="headerClass">
            <slot
                name="header"
                :filters="filters"
                :search="search"
                :loading="loading"
            />
        </div>

        <!-- Desktop Table View (hidden on mobile) -->
        <div class="hidden md:block overflow-x-auto">
            <table
                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
                :class="tableClass"
            >
                <!-- Slot 2: Column headings (<th> elements) -->
                <thead :class="theadClass">
                    <tr>
                        <slot name="columns">
                            <!-- Auto-generate headers from columns prop if no slot provided -->
                            <th
                                v-for="column in columns"
                                :key="column.name"
                                :class="column.thClass || 'px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider'"
                            >
                                {{ column.label }}
                            </th>
                        </slot>
                    </tr>
                </thead>

                <!-- Slot 3: Row data — scoped slot receives { item } -->
                <tbody
                    class="divide-y divide-gray-200 dark:divide-gray-700"
                    :class="[tbodyClass, { 'opacity-50': loading }]"
                >
                    <tr v-for="item in items" :key="item.id">
                        <slot name="row" :item="item" />
                    </tr>
                    <tr v-if="items.length === 0 && !loading">
                        <td
                            colspan="100"
                            class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400"
                        >
                            No records found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View (hidden on desktop) -->
        <div class="md:hidden space-y-4" :class="{ 'opacity-50': loading }">
            <!-- Card for each item -->
            <div
                v-for="item in items"
                :key="item.id"
                class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 space-y-3"
            >
                <!-- Use mobileRow slot if provided, otherwise auto-generate from columns -->
                <slot
                    name="mobileRow"
                    :item="item"
                    :columns="columns"
                >
                    <!-- Default mobile card layout using columns prop -->
                    <template v-if="columns.length > 0">
                        <div
                            v-for="column in columns.filter(c => !c.mobileHidden)"
                            :key="column.name"
                            class="flex flex-col xs:flex-row xs:items-center"
                        >
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider xs:w-1/3 xs:pr-2">
                                {{ column.label }}
                            </span>
                            <span class="text-sm text-gray-900 dark:text-gray-100 xs:w-2/3">
                                <slot
                                    name="mobileCell"
                                    :item="item"
                                    :column="column"
                                >
                                    {{ item[column.name] ?? '-' }}
                                </slot>
                            </span>
                        </div>
                    </template>
                    <!-- Fallback: show item data directly if no columns defined -->
                    <template v-else>
                        <div
                            v-for="(value, key) in item"
                            :key="key"
                            class="flex flex-col"
                        >
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ key }}
                            </span>
                            <span class="text-sm text-gray-900 dark:text-gray-100">
                                {{ value }}
                            </span>
                        </div>
                    </template>
                </slot>
            </div>

            <!-- Empty state for mobile -->
            <div
                v-if="items.length === 0 && !loading"
                class="bg-white dark:bg-gray-800 rounded-lg shadow p-8 text-center text-sm text-gray-500 dark:text-gray-400"
            >
                No records found.
            </div>
        </div>

        <!-- Pagination — hidden when only 1 page -->
        <div
            v-if="showPagination"
            class="flex justify-between items-center mt-4"
            :class="paginationClass"
        >
            <div class="text-sm text-gray-700 dark:text-gray-300">
                Showing {{ meta.current_page }} of {{ meta.last_page }} pages
            </div>

            <nav class="flex space-x-1">
                <!-- First page -->
                <button
                    :disabled="meta.current_page === 1"
                    :class="[
                        'px-3 py-2 rounded-md text-sm font-medium',
                        meta.current_page === 1
                            ? 'text-gray-400 cursor-not-allowed'
                            : 'text-gray-500 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700',
                    ]"
                    @click="goToPage(1)"
                    v-html="'&laquo;'"
                />

                <!-- Previous page -->
                <button
                    :disabled="meta.current_page === 1"
                    :class="[
                        'px-3 py-2 rounded-md text-sm font-medium',
                        meta.current_page === 1
                            ? 'text-gray-400 cursor-not-allowed'
                            : 'text-gray-500 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700',
                    ]"
                    @click="goToPage(meta.current_page - 1)"
                    v-html="'&#60;'"
                />

                <!-- Page numbers -->
                <button
                    v-for="page in visiblePages"
                    :key="page"
                    :class="[
                        'px-3 py-2 rounded-md text-sm font-medium',
                        page === meta.current_page
                            ? 'bg-indigo-500 text-white'
                            : 'text-gray-500 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700',
                    ]"
                    @click="goToPage(page)"
                >
                    {{ page }}
                </button>

                <!-- Next page -->
                <button
                    :disabled="meta.current_page === meta.last_page"
                    :class="[
                        'px-3 py-2 rounded-md text-sm font-medium',
                        meta.current_page === meta.last_page
                            ? 'text-gray-400 cursor-not-allowed'
                            : 'text-gray-500 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700',
                    ]"
                    @click="goToPage(meta.current_page + 1)"
                    v-html="'&#62;'"
                />

                <!-- Last page -->
                <button
                    :disabled="meta.current_page === meta.last_page"
                    :class="[
                        'px-3 py-2 rounded-md text-sm font-medium',
                        meta.current_page === meta.last_page
                            ? 'text-gray-400 cursor-not-allowed'
                            : 'text-gray-500 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700',
                    ]"
                    @click="goToPage(meta.last_page)"
                    v-html="'&raquo;'"
                />
            </nav>
        </div>
    </div>
</template>
