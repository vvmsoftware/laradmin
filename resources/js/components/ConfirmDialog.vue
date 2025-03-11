<script>
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog/index.js'
export default {
    name: "ConfirmDialog",
    components: {
        AlertDialog,
        AlertDialogAction,
        AlertDialogCancel,
        AlertDialogContent,
        AlertDialogDescription,
        AlertDialogFooter,
        AlertDialogHeader,
        AlertDialogTitle,
        AlertDialogTrigger,
    },
    emits: ['confirm'],
    props: {
        cancel: {
            type: String,
            default: 'Cancel'
        },
        confirm: {
            type: String,
            default: 'Continue'
        }
    }
}
</script>

<template>
    <AlertDialog>
        <AlertDialogTrigger><slot name="trigger"/></AlertDialogTrigger>
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>
                    <slot name="title">
                        Are you absolutely sure?
                    </slot>
                </AlertDialogTitle>
                <AlertDialogDescription>
                    <slot name="description">
                        This action cannot be undone. Please confirm that you would like to proceed.
                    </slot>
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>{{ cancel }}</AlertDialogCancel>
                <AlertDialogAction @click="$emit('confirm', true)">{{ confirm }}</AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>

<style scoped>

</style>