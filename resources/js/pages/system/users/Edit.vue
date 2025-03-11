<script lang="ts">
import { defineComponent } from 'vue';
import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/vue3';
import { Separator } from '@/components/ui/separator';
import EditProfile from './partials/EditProfile.vue';
import EditPassword from '@/pages/system/users/partials/EditPassword.vue';
import EditPermissions from './partials/EditPermissions.vue';

export default defineComponent({
    name: 'Edit',
    components: { Head, EditPassword, Separator, Link, Button, AppLayout, Heading, Tabs, TabsContent, TabsList, TabsTrigger, EditProfile, EditPermissions },
    props: {
        user: {
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
});
</script>

<template>
    <AppLayout :breadcrumbs="[{title: 'System', href: ''},{title: 'Users', href: route('users.index')}, {title: 'Edit User' + user.name, href:'#'}]">
        <Head title="Edit User" />
        <div class="px-4 py-6">
            <Heading title="Edit User" description="Update user details and permissions" />
            <div class="flex flex-col mx-auto w-full justify-center items-center">
                <Tabs
                    default-value="profile"
                    orientation="vertical"
                    class="w-full md:w-1/2 mx-auto"
                >
                    <TabsList aria-label="tabs example">
                        <TabsTrigger value="profile">
                            Update Profile
                        </TabsTrigger>
                        <TabsTrigger value="password">
                            Change Password
                        </TabsTrigger>
                        <TabsTrigger value="permissions">
                            Permissions
                        </TabsTrigger>
                    </TabsList>
                    <TabsContent value="profile">
                        <div class="w-full">
                        <EditProfile class="w-full" :user="user"/>
                        </div>
                    </TabsContent>
                    <TabsContent value="password">
                        <div class="w-full">
                            <EditPassword :user="user"/>
                        </div>
                    </TabsContent>
                    <TabsContent value="permissions">
                        <div class="w-full">
                            <EditPermissions :user="user" :roles="roles" :permissions="permissions"/>
                        </div>
                    </TabsContent>
                </Tabs>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>

</style>