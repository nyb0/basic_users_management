<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import DangerButton from '@/Components/Buttons/DangerButton.vue';
import SecondaryButton from '@/Components/Buttons/SecondaryButton.vue';
import Modal from '@/Components/Modals/Modal.vue';
import FaqSearchForm from './FaqSearchForm.vue';

// ============================================================================
// Props
// ============================================================================

const props = defineProps({
    aboutUsText: {
        type: String,
        default: '',
    },
    verifyOnMailChanged: {
        type: Boolean,
        default: false,
    },
});

// ============================================================================
// Component State
// ============================================================================

const activeTab = ref('authentication');
const faqsTableRef = ref(null);
const showCreateForm = ref(false);
const showSearchForm = ref(false);
const editingFaq = ref(null);
const faqToDelete = ref(null);
const confirmingDeleteFaq = ref(false);

// ============================================================================
// Forms
// ============================================================================

const createFaqForm = useForm({
    question: '',
    answer: '',
    priority: 0,
});

const editFaqForm = useForm({
    question: '',
    answer: '',
    priority: 0,
});

const deleteFaqForm = useForm({});

const aboutUsForm = useForm({
    about_us_text: props.aboutUsText,
});

const authenticationForm = useForm({
    verify_on_mail_changed: props.verifyOnMailChanged,
});

// ============================================================================
// FAQ CRUD Operations
// ============================================================================

const toggleCreateForm = () => {
    showCreateForm.value = !showCreateForm.value;
    if (showCreateForm.value) {
        editingFaq.value = null;
        editFaqForm.reset();
    } else {
        createFaqForm.reset();
    }
};

const createFaq = () => {
    createFaqForm.post(route('faqs.store'), {
        onSuccess: () => {
            showCreateForm.value = false;
            createFaqForm.reset();
            faqsTableRef.value?.refresh();
        },
    });
};

const startEdit = (faq) => {
    showCreateForm.value = false;
    createFaqForm.reset();

    editingFaq.value = faq;
    editFaqForm.question = faq.question;
    editFaqForm.answer = faq.answer;
    editFaqForm.priority = faq.priority;
};

const cancelEdit = () => {
    editingFaq.value = null;
    editFaqForm.reset();
};

const updateFaq = () => {
    editFaqForm.put(route('faqs.update', editingFaq.value.id), {
        onSuccess: () => {
            editingFaq.value = null;
            editFaqForm.reset();
            faqsTableRef.value?.refresh();
        },
    });
};

const handleDeleteClick = (faq) => {
    faqToDelete.value = faq;
    confirmingDeleteFaq.value = true;
};

const deleteFaq = () => {
    deleteFaqForm.delete(route('faqs.destroy', faqToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeDeleteFaqModal();
            faqsTableRef.value?.refresh();
        },
    });
};

const closeDeleteFaqModal = () => {
    confirmingDeleteFaq.value = false;
    faqToDelete.value = null;
};

// ============================================================================
// Search Operations
// ============================================================================

const performSearch = (params) => {
    faqsTableRef.value?.search(params);
};

// ============================================================================
// About Us Operations
// ============================================================================

const saveAboutUs = () => {
    aboutUsForm.put(route('site-settings.about-us.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Form will be reset with new data from server
        },
    });
};

// ============================================================================
// Authentication Operations
// ============================================================================

const saveAuthentication = () => {
    authenticationForm.put(route('site-settings.authentication.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Form will be reset with new data from server
        },
    });
};

// ============================================================================
// Tab Management
// ============================================================================

const switchTab = (tab) => {
    activeTab.value = tab;
    if (tab === 'faq') {
        cancelEdit();
        showCreateForm.value = false;
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Site Settings
            </h2>
        </template>

        <div class="pb-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">

                        <!-- Tabs -->
                        <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
                            <nav class="-mb-px flex space-x-8">
                                <button
                                    @click="switchTab('authentication')"
                                    :class="[
                                        activeTab === 'authentication'
                                            ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                                        'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors'
                                    ]"
                                >
                                    Authentication
                                </button>
                                <button
                                    @click="switchTab('faq')"
                                    :class="[
                                        activeTab === 'faq'
                                            ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                                        'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors'
                                    ]"
                                >
                                    FAQ
                                </button>
                                <button
                                    @click="switchTab('about-us')"
                                    :class="[
                                        activeTab === 'about-us'
                                            ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                                        'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors'
                                    ]"
                                >
                                    About Us
                                </button>
                            </nav>
                        </div>

                        <!-- Authentication Tab -->
                        <div v-show="activeTab === 'authentication'">
                            <div class="space-y-6">
                                <h3 class="text-lg font-semibold">Authentication Settings</h3>
                                <form @submit.prevent="saveAuthentication">
                                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <div>
                                            <InputLabel for="verify_on_mail_changed" value="Resend verification message on email changed" />
                                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                When enabled, users will need to verify their email again after changing it.
                                            </p>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input
                                                type="checkbox"
                                                id="verify_on_mail_changed"
                                                class="sr-only peer"
                                                v-model="authenticationForm.verify_on_mail_changed"
                                            />
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-500 peer-checked:bg-indigo-600"></div>
                                        </label>
                                    </div>

                                    <InputError class="mt-2" :message="authenticationForm.errors.verify_on_mail_changed" />

                                    <div class="flex items-end justify-end mt-6">
                                        <PrimaryButton :class="{ 'opacity-25': authenticationForm.processing }" :disabled="authenticationForm.processing">
                                            Save Authentication Settings
                                        </PrimaryButton>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- FAQ Tab -->
                        <div v-show="activeTab === 'faq'">
                            <DataTable
                                ref="faqsTableRef"
                                fetch-url="/faqs/search"
                                :initial-filters="{}"
                                :columns="[
                                    { name: 'priority', label: 'Priority' },
                                    { name: 'question', label: 'Question' },
                                    { name: 'answer', label: 'Answer' },
                                    { name: 'actions', label: 'Actions' },
                                ]"
                                thead-class="bg-gray-50 dark:bg-gray-700"
                                tbody-class="bg-white dark:bg-gray-800"
                                pagination-class="border-t border-gray-200 dark:border-gray-700 pt-4"
                            >
                                <template #header>
                                    <div class="flex justify-between items-center mb-4">
                                        <div class="flex space-x-4">
                                            <h3 class="text-lg font-semibold self-center">FAQ Management</h3>
                                            <button
                                                @click="showSearchForm = !showSearchForm"
                                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-nowrap"
                                                :title="showSearchForm ? 'Hide Search' : 'Open Search'"
                                            >
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                                    />
                                                </svg>
                                            </button>
                                        </div>
                                        <button
                                            v-if="!showCreateForm"
                                            @click="toggleCreateForm"
                                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-nowrap"
                                        >
                                            Add New FAQ
                                        </button>
                                    </div>

                                    <div v-if="showSearchForm" class="mb-6">
                                        <FaqSearchForm @search="performSearch" />
                                    </div>
                                </template>

                                <template #row="{ item }">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ item.priority }}</td>
                                    <td class="px-6 py-4">
                                        <div class="max-w-xs truncate">{{ item.question }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="max-w-xs truncate">{{ item.answer }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button
                                            @click="startEdit(item)"
                                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-4 text-nowrap"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            @click="handleDeleteClick(item)"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 text-nowrap"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </template>

                                <template #mobileCell="{ item, column }">
                                    <template v-if="column.name === 'question'">
                                        <div class="max-w-full truncate">{{ item.question }}</div>
                                    </template>
                                    <template v-else-if="column.name === 'answer'">
                                        <div class="max-w-full truncate">{{ item.answer }}</div>
                                    </template>
                                    <template v-else-if="column.name === 'actions'">
                                        <div class="flex space-x-3">
                                            <button
                                                @click="startEdit(item)"
                                                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium text-nowrap"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                @click="handleDeleteClick(item)"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 font-medium text-nowrap"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </template>
                                    <template v-else>
                                        {{ item[column.name] ?? '-' }}
                                    </template>
                                </template>
                            </DataTable>

                            <!-- Create FAQ Form -->
                            <div v-if="showCreateForm" class="mt-6">
                                <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                                    <h3 class="text-lg font-semibold mb-4">Add New FAQ</h3>
                                    <form @submit.prevent="createFaq" class="space-y-6">
                                        <div>
                                            <InputLabel for="create_question" value="Question" />
                                            <TextInput
                                                id="create_question"
                                                type="text"
                                                class="mt-1 block w-full"
                                                v-model="createFaqForm.question"
                                                autofocus
                                            />
                                            <InputError class="mt-2" :message="createFaqForm.errors.question" />
                                        </div>

                                        <div>
                                            <InputLabel for="create_answer" value="Answer" />
                                            <textarea
                                                id="create_answer"
                                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                                rows="4"
                                                v-model="createFaqForm.answer"
                                            ></textarea>
                                            <InputError class="mt-2" :message="createFaqForm.errors.answer" />
                                        </div>

                                        <div>
                                            <InputLabel for="create_priority" value="Priority (lower number = higher priority)" />
                                            <TextInput
                                                id="create_priority"
                                                type="number"
                                                class="mt-1 block w-full"
                                                v-model="createFaqForm.priority"
                                                min="0"
                                                max="9999"
                                            />
                                            <InputError class="mt-2" :message="createFaqForm.errors.priority" />
                                        </div>

                                        <div class="flex items-end justify-end space-x-4">
                                            <button
                                                type="button"
                                                @click="showCreateForm = false"
                                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-nowrap"
                                            >
                                                Cancel
                                            </button>
                                            <PrimaryButton :class="{ 'opacity-25': createFaqForm.processing }" :disabled="createFaqForm.processing">
                                                Create FAQ
                                            </PrimaryButton>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Edit FAQ Form -->
                            <div v-if="editingFaq" class="mt-6">
                                <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                                    <h3 class="text-lg font-semibold mb-4">Edit FAQ</h3>
                                    <form @submit.prevent="updateFaq" class="space-y-6">
                                        <div>
                                            <InputLabel for="edit_question" value="Question" />
                                            <TextInput
                                                id="edit_question"
                                                type="text"
                                                class="mt-1 block w-full"
                                                v-model="editFaqForm.question"
                                            />
                                            <InputError class="mt-2" :message="editFaqForm.errors.question" />
                                        </div>

                                        <div>
                                            <InputLabel for="edit_answer" value="Answer" />
                                            <textarea
                                                id="edit_answer"
                                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                                rows="4"
                                                v-model="editFaqForm.answer"
                                            ></textarea>
                                            <InputError class="mt-2" :message="editFaqForm.errors.answer" />
                                        </div>

                                        <div>
                                            <InputLabel for="edit_priority" value="Priority (lower number = higher priority)" />
                                            <TextInput
                                                id="edit_priority"
                                                type="number"
                                                class="mt-1 block w-full"
                                                v-model="editFaqForm.priority"
                                                min="0"
                                                max="9999"
                                            />
                                            <InputError class="mt-2" :message="editFaqForm.errors.priority" />
                                        </div>

                                        <div class="flex items-end justify-end space-x-4">
                                            <button
                                                type="button"
                                                @click="cancelEdit"
                                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-nowrap"
                                            >
                                                Cancel
                                            </button>
                                            <PrimaryButton :class="{ 'opacity-25': editFaqForm.processing }" :disabled="editFaqForm.processing">
                                                Update FAQ
                                            </PrimaryButton>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- About Us Tab -->
                        <div v-show="activeTab === 'about-us'">
                            <div class="space-y-6">
                                <h3 class="text-lg font-semibold">About Us Content</h3>
                                <form @submit.prevent="saveAboutUs">
                                    <div>
                                        <InputLabel for="about_us_text" value="About Us Text" />
                                        <textarea
                                            id="about_us_text"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                            rows="10"
                                            v-model="aboutUsForm.about_us_text"
                                            placeholder="Enter the About Us content here..."
                                        ></textarea>
                                        <InputError class="mt-2" :message="aboutUsForm.errors.about_us_text" />
                                    </div>

                                    <div class="flex items-end justify-end mt-6">
                                        <PrimaryButton :class="{ 'opacity-25': aboutUsForm.processing }" :disabled="aboutUsForm.processing">
                                            Save About Us
                                        </PrimaryButton>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Delete FAQ Confirmation Modal -->
        <Modal :show="confirmingDeleteFaq" @close="closeDeleteFaqModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Are you sure you want to delete this FAQ?
                </h2>

                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    This action cannot be undone.
                </p>

                <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ faqToDelete?.question }}</p>
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeDeleteFaqModal">
                        Cancel
                    </SecondaryButton>

                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': deleteFaqForm.processing }"
                        :disabled="deleteFaqForm.processing"
                        @click="deleteFaq"
                    >
                        Delete FAQ
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>