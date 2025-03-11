<script>
import {Loader2} from "lucide-vue-next";

export default {
    name: "LoadingMask",
    props: {
        loading: {
            type: Boolean,
            required: true,
            default: false,
        },
    },
    components: {Loader2}
}
</script>

<template>
    <Transition name="fade" appear>
        <div v-show="loading" class="absolute inset-0 bg-gray-100 dark:bg-background/90 opacity-75 z-10">
            <div class="w-full h-full mx-auto" style="position: absolute; top: 50%; left: 50%; width: 50%; height: 50%;">
                <Loader2 class="w-4 h-4 mr-2 animate-spin" />
                <slot/>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>