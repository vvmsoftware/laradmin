<script>
import {Button} from "@/components/ui/button/index.js";
import {CardContent, CardHeader, CardFooter, CardTitle, Card, CardDescription} from "@/components/ui/card/index.js";
import Input from "@/components/ui/input/Input.vue";
import Label from "@/components/ui/label/Label.vue";
import { useForm, Link, Head } from '@inertiajs/vue3';
import ServerTableFacetedFilter from "@/components/tables/server/ServerTableFacetedFilter.vue";
import InputError from "@/components/InputError.vue";
import {Loader2} from "lucide-vue-next";
import AppLayout from "@/layouts/app/AppSidebarLayout.vue";
import {Pencil2Icon, CrossCircledIcon, PersonIcon} from "@radix-icons/vue";
import ConfirmDialog from "@/components/ConfirmDialog.vue";

export default {
    name: "CreateRoleForm",
    components: {
        Head,
        ConfirmDialog,
        AppLayout,
        Loader2,
        InputError,
        ServerTableFacetedFilter, Link,
        CardContent, CardHeader, Button, Card, CardTitle, CardDescription, CardFooter,
        Input, Label,  Pencil2Icon, CrossCircledIcon, PersonIcon
    },
    props: {
        availablePermissions: {
            type: Array,
            required: true
        }
    },
    data() {
        return {
            form: useForm({
                name: '',
                permissions: [],
            })
        }
    },
    methods: {
        submit() {
            this.form.post(route('roles.store'), {
            });
        },
        onPermissionChange(selectedPermissions) {
            this.form.permissions = Array.from(selectedPermissions);
        }
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="[{title: 'System', href: ''},{title: 'Roles', href: route('roles.index')}, {title: 'Create Role', href:'#'}]">
        <Head title="Create Role" />
        <Card class="mt-5 min-w-lg mx-auto w-4/5">
            <CardContent>
                <div class="grid items-center w-full gap-4 mt-5">
                    <div class="flex flex-col space-y-1.5">
                        <Label for="name">Role Name</Label>
                        <Input id="name" placeholder="Role name" v-model="form.name" />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>
                    <div class="flex flex-col space-y-1.5">
                        <Label for="permissions" class="flex flex-row gap-2">Role Permissions</Label>
                        <ServerTableFacetedFilter
                            class="wrap text-left"
                            id="permissions"
                            title="Permissions"
                            :options="availablePermissions"
                            :selected="form.permissions"
                            clearText="Clear permissions"
                            :maxDisplay="4"
                            @update:selectedValues="onPermissionChange"
                        />
                        <InputError class="mt-2" :message="form.errors.permissions" />
                    </div>
                </div>
            </CardContent>
            <CardFooter class="flex justify-between px-6 pb-6">
                <Button @click="submit" :disabled="form.processing">
                    <Loader2 class="w-3 h-3 animate-spin" v-if="form.processing" />
                    <Pencil2Icon class="h-3 w-3" v-else />
                    Create Role
                </Button>
            </CardFooter>
        </Card>
    </AppLayout>
</template>

<style scoped>

</style>