<script setup>
import { computed, ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import DangerButton from '@/Components/Buttons/DangerButton.vue';
import SecondaryButton from '@/Components/Buttons/SecondaryButton.vue';
import Modal from '@/Components/Modals/Modal.vue';
import ConfirmPasswordModal from '@/Components/Modals/ConfirmPasswordModal.vue';
import UserSearchForm from './SearchForm.vue';
import { capitalizeFirstLetter } from '@/utils';

// ============================================================================
// Props & Page State
// ============================================================================

const props = defineProps({
    searchRoles: Array,
    editRoles: Array,
    verifyOnMailChanged: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();
const authUser = page.props.auth.user;

// Permission checks
const isAdmin = authUser.isAdmin === true;
const canManageUsers = authUser.isAdminOrModerator === true;

// ============================================================================
// Component State
// ============================================================================

const usersTableRef = ref(null);
const showCreateForm = ref(false);
const showSearchForm = ref(false);
const editingUser = ref(null);
const userToDelete = ref(null);
const confirmingUserDeletion = ref(false);
const confirmingDeleteUser = ref(false);
const passwordInput = ref(null);

// ============================================================================
// Forms
// ============================================================================

const createForm = useForm({
    name: '',
    email: '',
    phone_number: '',
    password: '',
    password_confirmation: '',
    role: 'user',
});

const editForm = useForm({
    name: '',
    email: '',
    phone_number: '',
    password: '',
    password_confirmation: '',
    role: '',
});

const deleteForm = useForm({
    password: '',
});

const deleteUserForm = useForm({});

// ============================================================================
// Computed Permissions
// ============================================================================

// Can edit email if:
// - Editing own account
// - Admin can edit anyone's email
// - Moderator can edit regular users' emails only
const canEditEmail = computed(() => {
    if (!editingUser.value) return false;
    
    // Own account
    if (editingUser.value.id === authUser.id) {
        return true;
    }
    
    // Admin can edit anyone
    if (isAdmin) {
        return true;
    }
    
    // Moderator can edit regular users only
    if (editingUser.value.role === 'user') {
        return true;
    }
    
    return false;
});

const isEditingOwnAccount = computed(() => {
    return editingUser.value?.id === authUser.id;
});

const emailChanged = computed(() => {
    return editForm.email !== editingUser.value?.email;
});

// ============================================================================
// Email Change Modal
// ============================================================================

const showEmailChangeModal = ref(false);

const submitUpdateUser = () => {
    // If editing own account, email changed, and verification setting is enabled - show modal
    if (isEditingOwnAccount.value && emailChanged.value && props.verifyOnMailChanged) {
        showEmailChangeModal.value = true;
    } else {
        updateUser();
    }
};

const confirmEmailChange = () => {
    showEmailChangeModal.value = false;
    updateUser();
};

const cancelEmailChange = () => {
    showEmailChangeModal.value = false;
    editForm.email = editingUser.value?.email; // Reset email
};

// ============================================================================
// Helper Functions
// ============================================================================

const isOwnAccount = (userId) => userId === authUser.id;

const formatDate = (date) => new Date(date).toLocaleDateString();

// ============================================================================
// Search Operations
// ============================================================================

const performSearch = (params) => {
    usersTableRef.value?.search(params);
};

// ============================================================================
// Create User
// ============================================================================

const toggleCreateForm = () => {
    showCreateForm.value = !showCreateForm.value;
    if (showCreateForm.value) {
        editingUser.value = null;
        editForm.reset();
    } else {
        createForm.reset();
    }
};

const createUser = () => {
    createForm.post(route('users.store'), {
        onSuccess: () => {
            showCreateForm.value = false;
            createForm.reset();
            usersTableRef.value?.refresh();
        },
    });
};

// ============================================================================
// Edit User
// ============================================================================

const startEdit = (user) => {
    showCreateForm.value = false;
    createForm.reset();

    editingUser.value = user;
    editForm.name = user.name;
    editForm.email = user.email;
    editForm.phone_number = user.phone_number || '';
    editForm.role = user.role;
    editForm.password = '';
    editForm.password_confirmation = '';
};

const cancelEdit = () => {
    editingUser.value = null;
    editForm.reset();
};

const updateUser = () => {
    editForm.put(route('users.update', editingUser.value.id), {
        onSuccess: () => {
            editingUser.value = null;
            editForm.reset();
            usersTableRef.value?.refresh();
        },
    });
};

// ============================================================================
// Delete User
// ============================================================================

const handleDeleteClick = (user) => {
    userToDelete.value = user;
    if (isOwnAccount(user.id)) {
        confirmingUserDeletion.value = true;
        setTimeout(() => passwordInput.value?.focus(), 100);
    } else {
        confirmingDeleteUser.value = true;
    }
};

const deleteOwnAccount = (password) => {
    deleteForm.password = password;
    deleteForm.delete(route('users.destroy', userToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            usersTableRef.value?.refresh();
        },
        onFinish: () => deleteForm.reset(),
    });
};

const deleteUser = () => {
    deleteUserForm.delete(route('users.destroy', userToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeDeleteUserModal();
            usersTableRef.value?.refresh();
        },
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    userToDelete.value = null;
    deleteForm.clearErrors();
    deleteForm.reset();
};

const closeDeleteUserModal = () => {
    confirmingDeleteUser.value = false;
    userToDelete.value = null;
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                User Management
            </h2>
        </template>

        <div class="pb-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">

                        <DataTable
                            ref="usersTableRef"
                            fetch-url="/users/search"
                            :initial-filters="{}"
                            :columns="[
                                { name: 'name', label: 'Name' },
                                { name: 'email', label: 'Email' },
                                { name: 'role', label: 'Role' },
                                { name: 'email_verified_at', label: 'Verified' },
                                { name: 'created_at', label: 'Created' },
                                { name: 'actions', label: 'Actions' },
                            ]"
                            thead-class="bg-gray-50 dark:bg-gray-700"
                            tbody-class="bg-white dark:bg-gray-800"
                            pagination-class="border-t border-gray-200 dark:border-gray-700 pt-4"
                        >
                            <template #header>
                                <div class="flex justify-between items-center mb-4">
                                    <div class="flex space-x-4">
                                        <h3 class="text-lg font-semibold self-center">Users</h3>
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
                                    <div class="flex space-x-3">
                                        <button
                                            v-if="!showCreateForm && canManageUsers"
                                            @click="toggleCreateForm"
                                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-nowrap"
                                        >
                                            Add New User
                                        </button>
                                    </div>
                                </div>

                                <div v-if="showSearchForm" class="mb-6">
                                    <UserSearchForm
                                        :roles="searchRoles"
                                        @search="performSearch"
                                    />
                                </div>
                            </template>

                            <template #row="{ item }">
                                <td class="px-6 py-4 whitespace-nowrap">{{ item.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ item.email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        {{ capitalizeFirstLetter(item.role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        v-if="item.email_verified_at"
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200"
                                    >
                                        Verified
                                    </span>
                                    <span
                                        v-else
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200"
                                    >
                                        Not Verified
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(item.created_at) }}</td>
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

                            <!-- Mobile card cell customization -->
                            <template #mobileCell="{ item, column }">
                                <!-- Role column with badge -->
                                <template v-if="column.name === 'role'">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        {{ capitalizeFirstLetter(item.role) }}
                                    </span>
                                </template>

                                <!-- Verified column with badge -->
                                <template v-else-if="column.name === 'email_verified_at'">
                                    <span
                                        v-if="item.email_verified_at"
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200"
                                    >
                                        Verified
                                    </span>
                                    <span
                                        v-else
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200"
                                    >
                                        Not Verified
                                    </span>
                                </template>

                                <!-- Created date formatted -->
                                <template v-else-if="column.name === 'created_at'">
                                    {{ formatDate(item.created_at) }}
                                </template>

                                <!-- Actions column with buttons -->
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

                                <!-- Default: show raw value -->
                                <template v-else>
                                    {{ item[column.name] ?? '-' }}
                                </template>
                            </template>
                        </DataTable>

                    </div>
                </div>
            </div>

            <!-- Create User Form -->
            <div v-if="showCreateForm" class="pt-0 mt-6">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <div class="mb-6 bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                                <h3 class="text-lg font-semibold mb-4">Add New User</h3>
                                <form @submit.prevent="createUser" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <InputLabel for="create_name" value="Name" />
                                        <TextInput
                                            id="create_name"
                                            type="text"
                                            class="mt-1 block w-full"
                                            v-model="createForm.name"
                                            autofocus
                                            autocomplete="name"
                                        />
                                        <InputError class="mt-2" :message="createForm.errors.name" />
                                    </div>

                                    <div>
                                        <InputLabel for="create_email" value="Email" />
                                        <TextInput
                                            id="create_email"
                                            type="text"
                                            class="mt-1 block w-full"
                                            v-model="createForm.email"
                                            autocomplete="username"
                                        />
                                        <InputError class="mt-2" :message="createForm.errors.email" />
                                    </div>

                                    <div>
                                        <InputLabel for="create_phone_number" value="Phone Number (Optional)" />
                                        <TextInput
                                            id="create_phone_number"
                                            type="tel"
                                            class="mt-1 block w-full"
                                            v-model="createForm.phone_number"
                                            autocomplete="tel"
                                            placeholder="+1234567890"
                                        />
                                        <InputError class="mt-2" :message="createForm.errors.phone_number" />
                                    </div>

                                    <div v-if="isAdmin">
                                        <InputLabel for="create_role" value="Role" />
                                        <select
                                            id="create_role"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                            v-model="createForm.role"
                                            required
                                        >
                                            <option v-for="role in editRoles" :key="role" :value="role">{{ capitalizeFirstLetter(role) }}</option>
                                        </select>
                                        <InputError class="mt-2" :message="createForm.errors.role" />
                                    </div>
                                    <div v-else></div>

                                    <div>
                                        <InputLabel for="create_password" value="Password" />
                                        <PasswordInput
                                            id="create_password"
                                            class="mt-1 block w-full"
                                            v-model="createForm.password"
                                            autocomplete="new-password"
                                        />
                                        <InputError class="mt-2" :message="createForm.errors.password" />
                                    </div>

                                    <div>
                                        <InputLabel for="create_password_confirmation" value="Confirm Password" />
                                        <PasswordInput
                                            id="create_password_confirmation"
                                            class="mt-1 block w-full"
                                            v-model="createForm.password_confirmation"
                                            autocomplete="new-password"
                                        />
                                        <InputError class="mt-2" :message="createForm.errors.password_confirmation" />
                                    </div>

                                    <div class="flex items-end justify-end md:col-span-2 space-x-4">
                                        <button
                                            type="button"
                                            @click="showCreateForm = false"
                                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-nowrap"
                                        >
                                            Cancel
                                        </button>
                                        <PrimaryButton :class="{ 'opacity-25': createForm.processing }" :disabled="createForm.processing">
                                            Create User
                                        </PrimaryButton>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit User Form -->
            <div v-if="editingUser" class="pt-0 mt-6">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <div class="mb-6 bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                                <h3 class="text-lg font-semibold mb-4">Edit User: {{ editingUser.name }}</h3>
                                <form @submit.prevent="submitUpdateUser" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <InputLabel for="edit_name" value="Name" />
                                        <TextInput
                                            id="edit_name"
                                            type="text"
                                            class="mt-1 block w-full"
                                            v-model="editForm.name"
                                            autocomplete="name"
                                        />
                                        <InputError class="mt-2" :message="editForm.errors.name" />
                                    </div>

                                    <div>
                                        <InputLabel for="edit_email" value="Email" />
                                        <TextInput
                                            id="edit_email"
                                            type="text"
                                            class="mt-1 block w-full"
                                            v-model="editForm.email"
                                            autocomplete="username"
                                            :disabled="!canEditEmail"
                                        />
                                        <p v-if="!canEditEmail" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            Email cannot be changed
                                        </p>
                                        <InputError class="mt-2" :message="editForm.errors.email" />
                                    </div>

                                    <div>
                                        <InputLabel for="edit_phone_number" value="Phone Number (Optional)" />
                                        <TextInput
                                            id="edit_phone_number"
                                            type="tel"
                                            class="mt-1 block w-full"
                                            v-model="editForm.phone_number"
                                            autocomplete="tel"
                                            placeholder="+1234567890"
                                        />
                                        <InputError class="mt-2" :message="editForm.errors.phone_number" />
                                    </div>

                                    <div v-if="isAdmin">
                                        <InputLabel for="edit_role" value="Role" />
                                        <select
                                            id="edit_role"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                            v-model="editForm.role"
                                            required
                                        >
                                            <option v-for="role in editRoles" :key="role" :value="role">{{ capitalizeFirstLetter(role) }}</option>
                                        </select>
                                        <InputError class="mt-2" :message="editForm.errors.role" />
                                    </div>
                                    <div v-else></div>

                                    <div class="flex items-end justify-end md:col-span-2 space-x-4">
                                        <button
                                            type="button"
                                            @click="cancelEdit"
                                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-nowrap"
                                        >
                                            Cancel
                                        </button>
                                        <PrimaryButton :class="{ 'opacity-25': editForm.processing }" :disabled="editForm.processing">
                                            Update User
                                        </PrimaryButton>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Password Confirmation Modal for Self-Deletion -->
        <ConfirmPasswordModal
            :show="confirmingUserDeletion"
            title="Are you sure you want to delete your account?"
            description="Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account."
            submit-button-text="Delete Account"
            :processing="deleteForm.processing"
            :errors="deleteForm.errors"
            @close="closeModal"
            @confirm="deleteOwnAccount"
        />

        <!-- Delete User Confirmation Modal -->
        <Modal :show="confirmingDeleteUser" @close="closeDeleteUserModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Are you sure you want to delete this user - {{ userToDelete?.name }} ({{ userToDelete?.email }})?
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    Once this user is deleted, all of their resources and data will be permanently deleted.
                </p>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeDeleteUserModal">
                        Cancel
                    </SecondaryButton>

                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': deleteUserForm.processing }"
                        :disabled="deleteUserForm.processing"
                        @click="deleteUser"
                    >
                        Delete User
                    </DangerButton>
                </div>
            </div>
        </Modal>

        <!-- Email Change Confirmation Modal (for own account) -->
        <Modal :show="showEmailChangeModal" @close="cancelEmailChange">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Email Change Verification Required
                </h2>

                <p class="mt-3 text-sm text-gray-600">
                    You are changing your email to <strong>{{ editForm.email }}</strong>.
                    You will need to verify your new email address before you can continue using all features.
                </p>

                <p class="mt-2 text-sm text-gray-600">
                    A verification email will be sent to your new email address.
                </p>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="cancelEmailChange">
                        Cancel
                    </SecondaryButton>

                    <PrimaryButton
                        class="ms-3"
                        :class="{ 'opacity-25': editForm.processing }"
                        :disabled="editForm.processing"
                        @click="confirmEmailChange"
                    >
                        Continue
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
