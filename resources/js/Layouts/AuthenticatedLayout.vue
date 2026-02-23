<script setup>
import { reactive } from 'vue';
import PrimaryNavigation from '@/Components/PrimaryNavigation.vue';
import VerificationModal from '@/Components/Modals/VerificationModal.vue';
import FlashMessages from '@/Components/FlashMessages.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth.user;

// Verification Modal state management
const state = reactive({
    showVerificationModal: page.props.auth.user ? !page.props.auth.user.isVerified : (page.props.flash.showVerificationModal || false),
    signatureError: page.props.flash.signatureError || null,
    verificationError: page.props.errors.verification_email || null,
});

// Close Verification Modal handler
const closeVerificationModal = () => {
    state.showVerificationModal = false;
};
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <!-- Primary Navigation -->
            <PrimaryNavigation 
                :user="user"
            />

            <!-- Page Heading -->
            <header
                class="bg-white shadow"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Flash Messages -->
            <div class="mx-auto sm:px-6 lg:px-8 relative w-full mx-auto max-w-7xl lg:max-w-7xl">
                <FlashMessages />
            </div>
            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>

        <!-- Verification Modal -->
        <VerificationModal
            :show="state.showVerificationModal"
            :user="user"
            :signature-error="state.signatureError"
            @close="closeVerificationModal"
        />
    </div>
</template>
