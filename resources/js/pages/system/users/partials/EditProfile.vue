<script>
import {Button} from "@/components/ui/button/index.js";
import Input from "@/components/ui/input/Input.vue";
import Label from "@/components/ui/label/Label.vue";
import {Select, SelectTrigger, SelectContent, SelectItem, SelectValue} from "@/components/ui/select/index.js";
import {useForm, Link } from "@inertiajs/vue3";
import ServerTableFacetedFilter from "@/components/tables/server/ServerTableFacetedFilter.vue";
import { Switch } from '@/components/ui/switch'
import { QuestionMarkCircledIcon, Pencil2Icon, PlusCircledIcon, CrossCircledIcon, PersonIcon } from '@radix-icons/vue';
import { TooltipProvider, Tooltip, TooltipTrigger, TooltipContent } from "@/components/ui/tooltip";
import InputError from "@/components/InputError.vue";
import {CirclePlusIcon, Loader2} from "lucide-vue-next";
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import SaveButton from "@/components/buttons/SaveButton.vue"
import ConfirmDialog from '@/components/ConfirmDialog.vue';

export default {
    name: "EditProfile",
    components: {
        PersonIcon,
        ConfirmDialog,
        AppLayout,
        PlusCircledIcon,
        Loader2,
        CirclePlusIcon,
        InputError,
        ServerTableFacetedFilter, Switch, Link,
        TooltipProvider, Tooltip, TooltipTrigger, TooltipContent,
        Button, Input, Label, Select, SelectTrigger, SelectContent, SelectItem, SelectValue,
        QuestionMarkCircledIcon, Pencil2Icon, CrossCircledIcon,
        Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter, SaveButton
    },
    props: {
        user : {
            type: Object,
            required: true
        },
    },
    data() {
        return {
            open: false,
            form: useForm({
                id: 0,
                name: '',
                email: '',
                enabled: true,
            })
        }
    },
    mounted() {
        this.form.id = this.user.id;
        this.form.name = this.user.name;
        this.form.email = this.user.email;
        this.form.enabled = this.user.enabled;
    },
    methods: {
        submit() {
            this.form.put(route('users.editProfile',this.form.id));
        },
        deleteForm() {
            this.form.delete(route('users.delete',this.form.id));
        },
        changeStatus(value) {
            this.form.enabled = value;
        },
    }
}
</script>

<template>
    <Card class="w-full pt-5">
        <CardContent>
            <div class="grid items-center w-full gap-4 z-10">
                <div class="flex flex-col space-y-1.5">
                    <Label for="name">Full Name</Label>
                    <Input id="name" placeholder="User full name" v-model="form.name" />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>
                <div class="flex flex-col space-y-1.5">
                    <Label for="email">E-mail address</Label>
                    <Input id="email" placeholder="user@example.com" v-model="form.email" disabled />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>
                <div class="flex items-center space-x-2">
                    <Label for="isActive">Account is active</Label>
                    <Switch id="isActive" :default-checked="form.enabled" :model-value="form.enabled" @update:modelValue="changeStatus" />
                    <InputError class="mt-2" :message="form.errors.enabled" />
                </div>
            </div>
        </CardContent>
        <CardFooter class="flex flex-row justify-between items-center w-full">
            <SaveButton @click="submit" :loading="form.processing">
                <span class="flex flex-row gap-x-2 justify-center items-center">
                    <Pencil2Icon class="w-6 h-6" />
                    Save Changes
                </span>
            </SaveButton>

            <ConfirmDialog confirm="Yes, Proceed" @confirm="deleteForm">
                <template #trigger>
                    <Button variant="destructive" :disabled="form.processing">
                        <Loader2 class="w-3 h-3 animate-spin" v-if="form.processing" />
                        <CrossCircledIcon class="h-3 w-3" />
                        Delete User
                    </Button>
                </template>
                <template #title>
                    Delete User
                </template>
                <template #description>
                    Once the account is deleted, all of its resources and data will also be permanently deleted. Are you sure you want to delete this user?
                </template>
                <template #confirm>
                    Yes, Proceed
                </template>
            </ConfirmDialog>

        </CardFooter>
    </Card>
</template>

<style scoped>

</style>