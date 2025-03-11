<script>
import { Button } from '@/components/ui/button'
import {Loader2} from "lucide-vue-next";
import {cn} from "@/lib/utils.js";
export default {
    name: "SubmitButton",
    components: {
        Button, Loader2
    },
    props: {
        text: {
            type: String,
            default: 'Submit',
            required: false,
        },
        loadingText: {
            type: String,
            default: 'Processing'
        },
        loading: {
            type: Boolean,
            default: false
        },
        disabled: {
            type: Boolean,
            default: false
        },
        type: {
            type: String,
            default: 'button'
        },
        class: {
            type: String,
            default: ''
        }
    },
    methods: {
        cn,
        onClick() {
            this.$emit('click');
        }
    },
    emits: ['click']
}
</script>

<template>
    <Button :type="type" :disabled="disabled || loading"  :class="cn(this.class, disabled || loading ? 'opacity-75':'','bg-destructive text-destructive-foreground shadow-sm hover:bg-destructive/90')" @click="onClick">
        <span v-if="loading" class="flex flex-row gap-x-1 items-center">
            <Loader2 class="animate-spin" />
            {{ loadingText }}
        </span>

        <span class="" v-else>
            <slot>{{ text }}</slot>
        </span>
    </Button>
</template>

<style scoped>

</style>