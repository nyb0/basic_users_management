<script setup>
import { ref, computed } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import SecondaryButton from '@/Components/Buttons/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modals/Modal.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    verifyNotificationStatus: {
        type: String,
    },
    verifyOnMailChanged: {
        type: Boolean,
        default: false,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
    phone_number: user.phone_number || '',
});

const showEmailChangeModal = ref(false);
const pendingEmail = ref('');

const emailChanged = computed(() => {
    return form.email !== user.email;
});

const submitForm = () => {
    // If email changed and verification setting is enabled, show modal
    if (emailChanged.value && props.verifyOnMailChanged) {
        pendingEmail.value = form.email;
        showEmailChangeModal.value = true;
    } else {
        form.patch(route('profile.update'));
    }
};

const confirmEmailChange = () => {
    showEmailChangeModal.value = false;
    form.patch(route('profile.update'));
};

const cancelEmailChange = () => {
    showEmailChangeModal.value = false;
    form.email = user.email; // Reset email to original
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Profile Information
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Update your account's profile information and email address.
            </p>
        </header>

        <form
            @submit.prevent="submitForm"
            class="mt-6 space-y-6"
        >
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

            <div>
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

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-800">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="verifyNotificationStatus === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600"
                    >
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>

        <!-- Email Change Confirmation Modal -->
        <Modal :show="showEmailChangeModal" @close="cancelEmailChange">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Email Change Verification Required
                </h2>

                <p class="mt-3 text-sm text-gray-600">
                    You are changing your email to <strong>{{ pendingEmail }}</strong>.
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
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="confirmEmailChange"
                    >
                        Continue
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
