<script setup>
import { ref } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import SecondaryButton from '@/Components/Buttons/SecondaryButton.vue';

const emit = defineEmits(['search']);

// ============================================================================
// Local Search State
// ============================================================================

const searchParams = ref({
    search: '',
});

// ============================================================================
// Actions
// ============================================================================

const handleSearch = () => {
    emit('search', { ...searchParams.value });
};

const handleClear = () => {
    searchParams.value = {
        search: '',
    };
    emit('search', {});
};
</script>

<template>
    <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <InputLabel for="search_question" value="Search Question" />
                <TextInput
                    id="search_question"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="searchParams.search"
                    placeholder="Search by question or answer..."
                    @keyup.enter="handleSearch"
                />
            </div>

            <div class="flex items-end space-x-3">
                <SecondaryButton @click="handleClear">
                    Clear
                </SecondaryButton>
                <PrimaryButton @click="handleSearch">
                    Search
                </PrimaryButton>
            </div>
        </div>
    </div>
</template>