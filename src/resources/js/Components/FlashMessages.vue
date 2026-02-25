<script setup>
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const flash = computed(() => page.props.flash || {});

// Get flash messages from props or accept them as prop
const props = defineProps({
    flash: {
        type: Object,
        default: null
    }
});

// Use passed prop or fallback to page props
const messages = computed(() => props.flash || flash.value);

// Track dismissed messages locally in Vue state
const dismissedMessages = ref(new Set());

// Define message types and their configurations
const messageTypes = {
    success: {
        bgColor: 'bg-green-50',
        iconColor: 'text-green-400',
        textColor: 'text-green-800',
        hoverColor: 'hover:text-green-500',
        icon: 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z'
    },
    status: {
        bgColor: 'bg-green-50',
        iconColor: 'text-green-400',
        textColor: 'text-green-800',
        hoverColor: 'hover:text-green-500',
        icon: 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z'
    },
    error: {
        bgColor: 'bg-red-50',
        iconColor: 'text-red-400',
        textColor: 'text-red-800',
        hoverColor: 'hover:text-red-500',
        icon: 'M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z'
    },
    warning: {
        bgColor: 'bg-yellow-50',
        iconColor: 'text-yellow-400',
        textColor: 'text-yellow-800',
        hoverColor: 'hover:text-yellow-500',
        icon: 'M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z'
    },
    info: {
        bgColor: 'bg-blue-50',
        iconColor: 'text-blue-400',
        textColor: 'text-blue-800',
        hoverColor: 'hover:text-blue-500',
        icon: 'M10 18a8 8 0 100-16 8 8 0 000 16zM4.332 8.027a6.012 6.012 0 011.912-2.706C6.512 5.73 6.974 6 7.5 6A1.5 1.5 0 019 7.5V8a2 2 0 004 0 2 2 0 011.523-1.943A5.977 5.977 0 0116 10c0 .34-.028.675-.083 1H15a2 2 0 00-2 2v2.197A5.973 5.973 0 0110 16v-2a2 2 0 00-2-2 2 2 0 01-2-2 2 2 0 00-1.668-1.973z'
    }
};

// Watch for changes in flash messages and reset dismissed state when new messages arrive
watch(messages, (newMessages) => {
    // Check if any new messages have appeared
    const hasNewMessages = Object.keys(messageTypes).some(type => newMessages[type]);
    
    if (hasNewMessages) {
        // Reset dismissed messages when new flash messages are received
        dismissedMessages.value.clear();
    }
}, { immediate: true });

// Get available messages (filter out dismissed ones)
const availableMessages = computed(() => {
    const result = [];
    for (const [type, config] of Object.entries(messageTypes)) {
        if (messages.value[type] && !dismissedMessages.value.has(type)) {
            result.push({
                type,
                message: messages.value[type],
                ...config
            });
        }
    }
    return result;
});

// Dismiss message function - adds to local Vue state to hide the message
function dismissMessage(type) {
    dismissedMessages.value.add(type);
}

</script>

<template>
    <div v-if="availableMessages.length > 0" class="mb-4 mt-4 space-y-4">
        <div
            v-for="msg in availableMessages"
            :key="msg.type"
            class="relative rounded-md p-4"
            :class="msg.bgColor"
        >
            <!-- Dismiss button at top right -->
            <button
                @click="dismissMessage(msg.type)"
                class="absolute top-2 right-2 p-1 rounded-md transition-colors hover:bg-opacity-20"
                :class="[msg.textColor.replace('800', '600'), msg.hoverColor]"
                aria-label="Dismiss"
            >
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                </svg>
            </button>
            
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg
                        class="h-5 w-5"
                        :class="msg.iconColor"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        aria-hidden="true"
                    >
                        <path fill-rule="evenodd" :d="msg.icon" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium" :class="msg.textColor">
                        {{ msg.message }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
