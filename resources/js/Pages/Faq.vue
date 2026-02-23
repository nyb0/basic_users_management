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
    faqs: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="FAQ" />
    
    <GuestLayout :can-login="canLogin" :can-register="canRegister">
        <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
            <div class="relative flex min-h-screen flex-col items-center selection:bg-[#FF2D20] selection:text-white">
                <div class="relative w-full max-w-4xl px-6 py-12 lg:max-w-7xl">
                    <header class="text-center mb-12">
                        <h1 class="text-4xl font-bold text-gray-900 dark:text-white">Frequently Asked Questions</h1>
                        <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">Everything you need to know about our application</p>
                    </header>

                    <main class="space-y-8">
                        <template v-if="faqs && faqs.length > 0">
                            <div 
                                v-for="faq in faqs" 
                                :key="faq.id" 
                                class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-lg"
                            >
                                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">{{ faq.question }}</h2>
                                <p class="text-gray-600 dark:text-gray-300 whitespace-pre-wrap">{{ faq.answer }}</p>
                            </div>
                        </template>

                        <template v-else>
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-lg text-center">
                                <p class="text-gray-500 dark:text-gray-400 text-lg">
                                    No frequently asked questions are available yet. Please check back later.
                                </p>
                            </div>
                        </template>

                        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-lg">
                            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">Need more help?</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                If you have any other questions or need assistance, please don't hesitate to reach out. 
                                You can also check out the Laravel documentation for more information.
                            </p>
                            <div class="flex gap-4">
                                <a 
                                    href="https://laravel.com/docs" 
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold transition-colors"
                                >
                                    View Documentation
                                </a>
                                <a 
                                    href="https://laracasts.com" 
                                    class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg font-semibold transition-colors"
                                >
                                    Watch Tutorials
                                </a>
                            </div>
                        </div>
                    </main>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>