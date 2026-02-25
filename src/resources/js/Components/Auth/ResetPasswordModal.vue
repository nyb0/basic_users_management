<script setup>
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modals/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import PasswordInput from '@/Components/PasswordInput.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    token: {
        type: String,
        default: null,
    },
    email: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['close']);

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('password', 'password_confirmation');
            closeModal();
        },
    });
};

// Clear errors when modal is closed
const closeModal = () => {
    form.clearErrors();
    emit('close');
};
</script>

<template>
    <Modal :show="show" @close="closeModal">
        <div class="p-6">
            <h2 class="mb-4 text-xl font-semibold">Reset Password</h2>
            
            <p class="mb-4 text-sm text-gray-600">
                Please enter your new password below.
            </p>

            <form @submit.prevent="submit">
                <div>
                    <InputLabel for="email" value="Email" />
                    <TextInput
                        id="email"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.email"
                        autocomplete="username"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div class="mt-4">
                    <InputLabel for="password" value="New Password" />
                    <PasswordInput
                        id="password"
                        class="mt-1 block w-full"
                        v-model="form.password"
                        autocomplete="new-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div class="mt-4">
                    <InputLabel for="password_confirmation" value="Confirm Password" />
                    <PasswordInput
                        id="password_confirmation"
                        class="mt-1 block w-full"
                        v-model="form.password_confirmation"
                        autocomplete="new-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password_confirmation" />
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <div class="flex gap-4">
                        <PrimaryButton
                            type="button"
                            @click="closeModal"
                            class="bg-gray-200 text-gray-800 hover:bg-gray-300"
                        >
                            Cancel
                        </PrimaryButton>
                        <PrimaryButton
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Reset Password
                        </PrimaryButton>
                    </div>
                </div>
            </form>
        </div>
    </Modal>
</template>