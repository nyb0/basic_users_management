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
});

const emit = defineEmits(['close', 'show-login', 'registration-success']);

const form = useForm({
    name: '',
    email: '',
    phone_number: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        preserveScroll: true,
        onSuccess: (response) => {
            form.reset('password', 'password_confirmation');
            emit('close');
            // The parent component should handle the redirect to verification notice
            emit('registration-success', response.props.flash?.message || 'Successfully registered. Please check your email to verify your account.');
        },
    });
};

// Clear errors when modal is closed
const closeModal = () => {
    form.clearErrors();
    emit('close');
};

// Clear errors when navigating to login
const showLogin = () => {
    form.clearErrors();
    emit('show-login');
};
</script>

<template>
    <Modal :show="show" @close="closeModal">
        <div class="p-6">
            <h2 class="mb-4 text-xl font-semibold">Register</h2>
            
            <form @submit.prevent="submit">
                <div>
                    <InputLabel for="name" value="Name" />
                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.name"
                        autofocus
                        autocomplete="name"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div class="mt-4">
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
                    <InputLabel for="phone_number" value="Phone Number (Optional)" />
                    <TextInput
                        id="phone_number"
                        type="tel"
                        class="mt-1 block w-full"
                        v-model="form.phone_number"
                        autocomplete="tel"
                        placeholder="+1234567890"
                    />
                    <InputError class="mt-2" :message="form.errors.phone_number" />
                </div>

                <div class="mt-4">
                    <InputLabel for="password" value="Password" />
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
                    <button
                        type="button"
                        @click="showLogin"
                        class="text-sm text-gray-600 underline hover:text-gray-900"
                    >
                        Already registered?
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
                            Register
                        </PrimaryButton>
                    </div>
                </div>
            </form>
        </div>
    </Modal>
</template>