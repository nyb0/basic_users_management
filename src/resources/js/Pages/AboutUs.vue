<script setup>
import { Head } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const props = defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    aboutUsText: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <Head title="About Us" />
    
    <GuestLayout :can-login="canLogin" :can-register="canRegister">
        <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
            <div class="relative flex min-h-screen flex-col items-center selection:bg-[#FF2D20] selection:text-white">
                <div class="relative w-full max-w-4xl px-6 py-12 lg:max-w-7xl">
                    <header class="text-center mb-12">
                        <h1 class="text-4xl font-bold text-gray-900 dark:text-white">About Us</h1>
                        <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">Learn more about our application</p>
                    </header>

                    <main>
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-lg">
                            <div v-if="aboutUsText" class="prose dark:prose-invert max-w-none">
                                <p class="text-gray-600 dark:text-gray-300 whitespace-pre-wrap">{{ aboutUsText }}</p>
                            </div>
                            <div v-else class="text-center py-12">
                                <p class="text-gray-500 dark:text-gray-400 text-lg">
                                    About Us content is not available yet. Please check back later.
                                </p>
                            </div>
                        </div>
                    </main>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>