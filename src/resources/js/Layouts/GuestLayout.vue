<script setup>
import PrimaryNavigation from '@/Components/PrimaryNavigation.vue';
import LoginModal from '@/Components/Auth/LoginModal.vue';
import RegisterModal from '@/Components/Auth/RegisterModal.vue';
import ForgotPasswordModal from '@/Components/Auth/ForgotPasswordModal.vue';
import ResetPasswordModal from '@/Components/Auth/ResetPasswordModal.vue';
import VerificationModal from '@/Components/Modals/VerificationModal.vue';
import FlashMessages from '@/Components/FlashMessages.vue';
import { usePage } from '@inertiajs/vue3';
import { reactive } from 'vue';

const page = usePage();

// Modal state management
const state = reactive({
    showLoginModal: page.props.flash.showLoginModal || false,
    showRegisterModal: false,
    showForgotPasswordModal: false,
    showResetPasswordModal: page.props.flash.showResetPasswordModal || false,
    showVerificationModal: page.props.auth.user ? !page.props.auth.user.isVerified : (page.props.flash.showVerificationModal || false),
    signatureError: page.props.flash.signatureError || null,
    resetToken: page.props.flash.resetToken || null,
    resetEmail: page.props.flash.resetEmail || null,
});

// Event handlers for navigation
const openLoginModal = () => {
    state.showLoginModal = true;
};

const openRegisterModal = () => {
    state.showRegisterModal = true;
};

const closeLoginModal = () => {
    state.showLoginModal = false;
};

const closeRegisterModal = () => {
    state.showRegisterModal = false;
};

const closeForgotPasswordModal = () => {
    state.showForgotPasswordModal = false;
};

const closeResetPasswordModal = () => {
    state.showResetPasswordModal = false;
    state.resetToken = null;
    state.resetEmail = null;
};

const showForgotPasswordModal = () => {
    state.showForgotPasswordModal = true;
    state.showLoginModal = false;
};

const showLoginModal = () => {
    state.showLoginModal = true;
    state.showRegisterModal = false;
};

const handleRegistrationSuccess = (message) => {
    // Close register modal and show success message
    state.showRegisterModal = false;
    // User is now logged in but needs to verify email - show verification modal
    state.showVerificationModal = true;
};

const showVerificationModal = () => {
    state.showVerificationModal = true;
};

const closeVerificationModal = () => {
    state.showVerificationModal = false;
};

const cleanVerificationError = () => {
    state.signatureError = null;
};
</script>

<template>
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
        <!-- Primary Navigation -->
        <PrimaryNavigation 
            :can-login="page.props.canLogin"
            :can-register="page.props.canRegister"
            :user="page.props.auth.user"
            @open-login-modal="openLoginModal"
            @open-register-modal="openRegisterModal"
        />

        <div
            class="flex min-h-screen flex-col items-center pt-6 sm:justify-center sm:pt-0"
        >
            <!-- Flash Messages -->
            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                <FlashMessages />
            </div>

            <slot />

        </div>

        <!-- Verification Modal -->
        <VerificationModal
            :show="state.showVerificationModal"
            :user="page.props.auth.user"
            :signature-error="state.signatureError"
            @close="closeVerificationModal"
        />

        <!-- Auth Modals -->
        <LoginModal
            :show="state.showLoginModal"
            @close="closeLoginModal"
            @show-forgot-password="showForgotPasswordModal"
            @show-verification-modal="showVerificationModal"
            @clear-verification-error="cleanVerificationError"
        />

        <RegisterModal
            :show="state.showRegisterModal"
            @close="closeRegisterModal"
            @show-login="showLoginModal"
            @registration-success="handleRegistrationSuccess"
        />

        <ForgotPasswordModal
            :show="state.showForgotPasswordModal"
            @close="closeForgotPasswordModal"
        />

        <ResetPasswordModal
            :show="state.showResetPasswordModal"
            :token="state.resetToken"
            :email="state.resetEmail"
            @close="closeResetPasswordModal"
        />
    </div>
</template>
