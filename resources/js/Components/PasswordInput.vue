<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    id: {
        type: String,
        default: null,
    },
    name: {
        type: String,
        default: null,
    },
    type: {
        type: String,
        default: 'password',
    },
    class: {
        type: String,
        default: '',
    },
    autofocus: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    required: {
        type: Boolean,
        default: false,
    },
    autocomplete: {
        type: String,
        default: null,
    },
    placeholder: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['update:modelValue']);

const input = ref(null);

const isVisible = ref(false);

const inputClass = props.class;

const inputType = computed(() => {
    if (props.type === 'password' || isVisible.value) {
        return isVisible.value ? 'text' : 'password';
    }
    return props.type;
});

const toggleVisibility = () => {
    isVisible.value = !isVisible.value;
};

const updateValue = (event) => {
    emit('update:modelValue', event.target.value);
};

const focus = () => {
    input.value?.focus();
};

defineExpose({ focus });
</script>

<template>
    <div class="relative">
        <input
            :id="id"
            :name="name"
            :type="inputType"
            :value="modelValue"
            :autofocus="autofocus"
            :disabled="disabled"
            :required="required"
            :autocomplete="autocomplete"
            :placeholder="placeholder"
            :class="[
                'rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pr-10',
                inputClass
            ]"
            ref="input"
            @input="updateValue"
        />
        <button
            type="button"
            @click="toggleVisibility"
            class="absolute inset-y-0 right-0 pr-3 flex items-center"
            :title="isVisible ? 'Hide password' : 'Show password'"
            tabindex="-1"
        >
            <svg
                v-if="isVisible"
                class="h-5 w-5 text-gray-500 hover:text-gray-700"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"
                />
            </svg>
            <svg
                v-else
                class="h-5 w-5 text-gray-500 hover:text-gray-700"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                />
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                />
            </svg>
        </button>
    </div>
</template>
