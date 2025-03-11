<script>
import {Button} from "@/components/ui/button/index.js";
import Input from "@/components/ui/input/Input.vue";
import Label from "@/components/ui/label/Label.vue";
import {Select, SelectTrigger, SelectContent, SelectItem, SelectValue} from "@/components/ui/select/index.js";
import {useForm, Link } from "@inertiajs/vue3";
import ServerTableFacetedFilter from "@/components/tables/server/ServerTableFacetedFilter.vue";
import { Switch } from '@/components/ui/switch'
import { QuestionMarkCircledIcon, Pencil2Icon, PlusCircledIcon } from '@radix-icons/vue';
import { TooltipProvider, Tooltip, TooltipTrigger, TooltipContent } from "@/components/ui/tooltip";
import InputError from "@/components/InputError.vue";
import {CirclePlusIcon, Loader2} from "lucide-vue-next";
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import SaveButton from "@/components/buttons/SaveButton.vue"

export default {
    name: "EditPassword",
    components: {
        AppLayout,
        PlusCircledIcon,
        Loader2,
        CirclePlusIcon,
        InputError,
        ServerTableFacetedFilter, Switch, Link,
        TooltipProvider, Tooltip, TooltipTrigger, TooltipContent,
        Button, Input, Label, Select, SelectTrigger, SelectContent, SelectItem, SelectValue,
        QuestionMarkCircledIcon, Pencil2Icon,
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
            form: useForm({
                password: '',
                password_confirmation:'',
            })
        }
    },
    methods: {
        submit() {
            this.form.put(route('users.editPassword',this.user.id));
        }
    }
}
</script>

<template>
    <Card class="w-full pt-5">
        <CardContent>
            <div class="grid items-center w-full gap-4 z-10">
                <div class="flex flex-col space-y-1.5">
                    <Label for="name">New Password</Label>
                    <Input id="password" type="password" v-model="form.password" />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>
                <div class="flex flex-col space-y-1.5">
                    <Label for="password_confirmation">Confirm password</Label>
                    <Input id="password_confirmation" v-model="form.password_confirmation" type="password" />
                    <InputError class="mt-2" :message="form.errors.password_confirmation" />
                </div>
            </div>
        </CardContent>
        <CardFooter>
            <SaveButton @click="submit" :loading="form.processing">
                <span class="flex flex-row gap-x-2 justify-center items-center">
                    <Pencil2Icon class="w-6 h-6" />
                    Change Password
                </span>
            </SaveButton>
        </CardFooter>
    </Card>
</template>

<style scoped>

</style>