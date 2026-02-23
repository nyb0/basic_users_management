<script setup>
import { ref } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import SecondaryButton from '@/Components/Buttons/SecondaryButton.vue';
import Datepicker from 'vue3-datepicker';
import { capitalizeFirstLetter } from '@/utils';

const props = defineProps({
    roles: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(['search']);

// ============================================================================
// Local Search State
// ============================================================================

const searchParams = ref({
    name: '',
    email: '',
    role: '',
    isVerified: '',
    createdAtFrom: null,
    createdAtTo: null,
});

// ============================================================================
// Actions
// ============================================================================

const handleSearch = () => {
    emit('search', { ...searchParams.value });
};

const handleClear = () => {
    searchParams.value = {
        name: '',
        email: '',
        role: '',
        isVerified: '',
        createdAtFrom: null,
        createdAtTo: null,
    };
    emit('search', {});
};

const formatDate = (date) => {
    if (!date) return '';
    return date.toISOString().split('T')[0];
};
</script>

<template>
    <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg mb-6">
        <!-- First Row: Name, Email, Role -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <InputLabel for="search_name" value="Name" />
                <TextInput
                    id="search_name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="searchParams.name"
                    placeholder="Search by name"
                />
            </div>

            <div>
                <InputLabel for="search_email" value="Email" />
                <TextInput
                    id="search_email"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="searchParams.email"
                    placeholder="Search by email"
                />
            </div>

            <div>
                <InputLabel for="search_role" value="Role" />
                <select
                    id="search_role"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                    v-model="searchParams.role"
                >
                    <option value="">All Roles</option>
                    <option v-for="role in roles" :key="role" :value="role">{{ capitalizeFirstLetter(role) }}</option>
                </select>
            </div>
        </div>

        <!-- Second Row: Verification Status, Created From, Created To -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <InputLabel for="search_verified" value="Verification Status" />
                <select
                    id="search_verified"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                    v-model="searchParams.isVerified"
                >
                    <option value="">All Users</option>
                    <option value="1">Verified</option>
                    <option value="0">Not Verified</option>
                </select>
            </div>

            <div class="relative">
                <InputLabel for="search_created_from" value="Created From" />
                <datepicker
                    id="search_created_from"
                    v-model="searchParams.createdAtFrom"
                    :format="formatDate"
                    :clearable="true"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                >
                    <template #clear="{ onClear }">
                        <button
                            type="button"
                            @click="onClear"
                            class="absolute top-1/2 transform w-6 h-6 rounded-full flex items-center justify-center"
                            style="background-color: #f87171 !important; border-radius: 9999px !important;"
                            title="Clear date"
                        >
                            <svg class="w-4 h-4 text-white" fill="currentColor" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </template>
                </datepicker>
            </div>

            <div class="relative">
                <InputLabel for="search_created_to" value="Created To" />
                <datepicker
                    id="search_created_to"
                    v-model="searchParams.createdAtTo"
                    :format="formatDate"
                    :clearable="true"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                >
                    <template #clear="{ onClear }">
                        <button
                            type="button"
                            @click="onClear"
                            class="absolute top-1/2 transform w-6 h-6 rounded-full flex items-center justify-center"
                            style="background-color: #f87171 !important; border-radius: 9999px !important;"
                            title="Clear date"
                        >
                            <svg class="w-4 h-4 text-white" fill="currentColor" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </template>
                </datepicker>
            </div>
        </div>

        <div class="mt-4 flex justify-end space-x-3">
            <SecondaryButton @click="handleClear">
                Clear
            </SecondaryButton>
            <PrimaryButton @click="handleSearch">
                Search
            </PrimaryButton>
        </div>
    </div>
</template>