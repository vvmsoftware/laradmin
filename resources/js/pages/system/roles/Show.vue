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
    name: "RolesEditForm",
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
        role: {
            type: Object,
            required: true
        },
        availablePermissions: {
            type: Array,
            required: true
        }
    },
    data() {
        return {
            form: useForm({
                id: this.role.id || null,
                name: this.role?.name || '',
                permissions: this.role?.permissions || [],
            })
        }
    },
    methods: {
        submit() {
            this.form.put(route('roles.update', this.form.id), {
            });
        },
        onPermissionChange(selectedPermissions) {
            this.form.permissions = Array.from(selectedPermissions);
        },
        onEmptyConfirm() {
            this.form.patch(route('roles.empty', this.form.id), {
            });
        },
        onDeleteConfirm() {
            this.form.delete(route('roles.delete', this.form.id), {
            });
        }
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="[{title: 'System', href: ''},{title: 'Roles', href: route('roles.index')}, {title: 'Edit Role', href:'#'}]">
        <Head title="Edit Role" />
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
                <div class="flex justify-start gap-x-2">
                    <ConfirmDialog confirm="Yes, Proceed" @confirm="onEmptyConfirm">
                        <template #trigger>
                            <Button variant="outline" :disabled="form.processing">
                                <Loader2 class="w-3 h-3 animate-spin" v-if="form.processing" />
                                <PersonIcon class="h-3 w-3" />
                                Empty Users
                            </Button>
                        </template>
                        <template #title>
                            Empty Users
                        </template>
                        <template #description>
                            Are you sure you want to empty all users from this role?
                        </template>
                        <template #confirm>
                            Empty Users
                        </template>
                    </ConfirmDialog>
                    <ConfirmDialog confirm="Yes, Delete" @confirm="onDeleteConfirm">
                        <template #trigger>
                            <Button variant="destructive" :disabled="form.processing">
                                <Loader2 class="w-3 h-3 animate-spin" v-if="form.processing" />
                                <CrossCircledIcon class="h-3 w-3" />
                                Delete Role
                            </Button>
                        </template>
                        <template #title>
                            Delete Role
                        </template>
                        <template #description>
                            Are you sure you want to delete this role?
                        </template>
                        <template #confirm>
                            Delete Role
                        </template>
                    </ConfirmDialog>
                </div>
                <Button @click="submit" :disabled="form.processing">
                    <Loader2 class="w-3 h-3 animate-spin" v-if="form.processing" />
                    <Pencil2Icon class="h-3 w-3" v-else />
                    Save Changes
                </Button>
            </CardFooter>
        </Card>
    </AppLayout>
</template>

<style scoped>

</style>