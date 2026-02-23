<script setup>
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    user: {
        type: Object,
        default: null,
    },
    signatureError: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['close']);

const verificationForm = useForm({
    verification_email: '',
});

const submitVerificationResend = () => {
    verificationForm.post(route('verification.send'), {
        preserveScroll: true,
        onSuccess: (response) => {
            emit('close');
        },
        onError: (errors) => {
            // Handle Errors
        },
    });
};

// Clear errors when modal is closed
const closeModal = () => {
    verificationForm.clearErrors();
    emit('close');
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4 shadow-xl">
            <div class="text-center">
                <div class="text-6xl mb-4">📧</div>
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Verify Your Email</h2>
                <p v-if="!user" class="text-gray-600 mb-6">
                    {{ signatureError ? signatureError + ' Please authorize and request a new validation link for your account. Or provide your email address, we will gladly send you another.'
                     : 'Thanks for signing up! Please check your email to verify your account before accessing the dashboard. If you didn\'t receive the email, we will gladly send you another.'
                    }}
                </p>
                <p v-else class="text-gray-600 mb-6">
                    {{ signatureError ? signatureError + ' We will gladly send you another.'
                     : 'Thanks for signing up! Please check your email to verify your account before accessing the dashboard. If you didn\'t receive the email, we will gladly send you another.'
                    }}
                </p>
                <form @submit.prevent="submitVerificationResend">
                    <div v-if="!user">
                        <InputLabel for="verification_email" value="Email Address" />
                        <TextInput
                            id="verification_email"
                            type="text"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            v-model="verificationForm.verification_email"
                            placeholder="Enter your email address"
                            autofocus
                        />
                        <InputError class="mt-2 px-2 text-start" :message="verificationForm.errors.verification_email" />
                    </div>

                    <div class="mt-6 space-y-3">
                        <button
                            class="w-full bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold transition-colors"
                            :class="{ 'opacity-25': verificationForm.processing }"
                            :disabled="verificationForm.processing"
                        >
                            Resend Verification Email
                        </button>
                        <button
                            type="button"
                            @click="closeModal"
                            class="w-full text-gray-600 hover:text-gray-800 font-semibold py-3"
                        >
                            Close
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
