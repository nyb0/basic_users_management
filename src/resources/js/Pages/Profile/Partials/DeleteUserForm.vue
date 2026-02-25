<script setup>
import DangerButton from '@/Components/Buttons/DangerButton.vue';
import ConfirmPasswordModal from '@/Components/Modals/ConfirmPasswordModal.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const confirmingUserDeletion = ref(false);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
};

const deleteUser = (password) => {
    form.password = password;    
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => {
            // Password validation error will be handled by the modal
        },
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Delete Account
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Once your account is deleted, all of its resources and data will
                be permanently deleted. Before deleting your account, please
                download any data or information that you wish to retain.
            </p>
        </header>

        <DangerButton @click="confirmUserDeletion">Delete Account</DangerButton>

        <ConfirmPasswordModal
            :show="confirmingUserDeletion"
            title="Are you sure you want to delete your account?"
            description="Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account."
            submit-button-text="Delete Account"
            :processing="form.processing"
            :errors="form.errors"
            @close="closeModal"
            @confirm="deleteUser"
        />
    </section>
</template>
