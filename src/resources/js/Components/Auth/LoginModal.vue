<script setup>
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modals/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import PasswordInput from '@/Components/PasswordInput.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'show-forgot-password', 'show-verification-modal', 'clear-verification-error']);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        preserveScroll: true,
        onSuccess: (response) => {
            emit('clear-verification-error');
            form.reset('password');
            emit('close');
        },
        onError: (errors) => {
            // Handle errors
        },
    });
};

// Clear errors when modal is closed
const closeModal = () => {
    form.clearErrors();
    emit('close');
};

// Clear errors when navigating to forgot password
const showForgotPassword = () => {
    form.clearErrors();
    emit('show-forgot-password');
};
</script>

<template>
    <Modal :show="show" @close="closeModal">
        <div class="p-6">
            <h2 class="mb-4 text-xl font-semibold">Log in</h2>
            
            <form @submit.prevent="submit">
                <div>
                    <InputLabel for="email" value="Email" />
                    <TextInput
                        id="email"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.email"
                        autofocus
                        autocomplete="username"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div class="mt-4">
                    <InputLabel for="password" value="Password" />
                    <PasswordInput
                        id="password"
                        class="mt-1 block w-full"
                        v-model="form.password"
                        autocomplete="current-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div class="mt-4 block">
                    <label class="flex items-center">
                        <Checkbox name="remember" v-model:checked="form.remember" />
                        <span class="ms-2 text-sm text-gray-600">Remember me</span>
                    </label>
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <button
                        type="button"
                        @click="showForgotPassword"
                        class="text-sm text-gray-600 underline hover:text-gray-900"
                    >
                        Forgot your password?
                    </button>

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
                            Log in
                        </PrimaryButton>
                    </div>
                </div>
            </form>
        </div>
    </Modal>
</template>