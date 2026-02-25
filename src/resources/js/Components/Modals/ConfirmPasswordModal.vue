<script setup>
import InputLabel from '@/Components/InputLabel.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
import InputError from '@/Components/InputError.vue';
import DangerButton from '@/Components/Buttons/DangerButton.vue';
import SecondaryButton from '@/Components/Buttons/SecondaryButton.vue';
import Modal from '@/Components/Modals/Modal.vue';
import { nextTick, ref } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    title: {
        type: String,
        default: 'Are you sure you want to do this action?'
    },
    description: {
        type: String,
        default: 'Please enter your password to confirm.'
    },
    submitButtonText: {
        type: String,
        default: 'Confirm'
    },
    processing: {
        type: Boolean,
        default: false
    },
    errors: {
        type: Object,
        default: () => ({})
    }
});

const emit = defineEmits(['close', 'confirm']);

const passwordInput = ref(null);
const password = ref('');

const closeModal = () => {
    emit('close');
    password.value = '';
};

const confirmAction = () => {
    emit('confirm', password.value);
};

// Focus password input when modal opens
const onModalOpen = () => {
    nextTick(() => {
        passwordInput.value?.focus();
    });
};
</script>

<template>
    <Modal :show="show" @close="closeModal" @modal-open="onModalOpen">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                {{ title }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ description }}
            </p>

            <div class="mt-6">
                <InputLabel for="password" value="Password" class="sr-only" />
                <PasswordInput
                    id="password"
                    ref="passwordInput"
                    v-model="password"
                    class="mt-1 block w-full"
                    placeholder="Password"
                    @keyup.enter="confirmAction"
                />
                <InputError :message="props.errors?.password" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <SecondaryButton @click="closeModal">
                    Cancel
                </SecondaryButton>

                <DangerButton
                    :class="{ 'opacity-25': processing }"
                    :disabled="processing"
                    @click="confirmAction"
                >
                    {{ submitButtonText }}
                </DangerButton>
            </div>
        </div>
    </Modal>
</template>