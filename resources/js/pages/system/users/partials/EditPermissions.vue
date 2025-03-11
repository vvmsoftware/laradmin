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
        roles: {
            type: Array,
            required: true
        },
        permissions: {
            type: Array,
            required: true
        }
    },
    data() {
        return {
            form: useForm({
                roles: this?.user?.roles || [],
                permissions: this?.user?.permissions || [],
            })
        }
    },
    methods: {
        submit() {
            this.form.put(route('users.editPermissions',this.user.id));
        },
        onRoleChange(selectedRoles) {
            this.form.roles = Array.from(selectedRoles);
        },
        onPermissionChange(selectedPermissions) {
            this.form.permissions = Array.from(selectedPermissions);
        }
    }
}
</script>

<template>
    <Card class="w-full pt-5">
        <CardContent>
            <div class="flex flex-col space-y-1.5">
                <Label for="roles">Assigned roles</Label>
                <ServerTableFacetedFilter
                    class="w-full text-left z-20"
                    id="roles"
                    title="Roles"
                    :options="roles"
                    :selected="form.roles"
                    clearText="Clear roles"
                    :maxDisplay="4"
                    @update:selectedValues="onRoleChange"
                />
                <InputError class="mt-2" :message="form.errors.roles" />
            </div>
            <div class="flex flex-col space-y-1.5 mt-3">
                <Label for="permissions" class="flex flex-row gap-2">Individual Permissions
                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger as-child>
                                <QuestionMarkCircledIcon class="h-4 w-4 cursor-help" />
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>You should probably use roles instead.
                                    Add permissions if you want to exclusively give them to this user only.</p>
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                </Label>
                <ServerTableFacetedFilter
                    class="wrap text-left z-20"
                    id="permissions"
                    title="Permissions"
                    :options="permissions"
                    :selected="form.permissions"
                    clearText="Clear permissions"
                    :maxDisplay="4"
                    @update:selectedValues="onPermissionChange"
                />
                <InputError class="mt-2" :message="form.errors.permissions" />
            </div>
        </CardContent>
        <CardFooter>
            <SaveButton @click="submit" :loading="form.processing">
                <span class="flex flex-row gap-x-2 justify-center items-center">
                    <Pencil2Icon class="w-6 h-6" />
                    Save Changes
                </span>
            </SaveButton>
        </CardFooter>
    </Card>
</template>

<style scoped>

</style>