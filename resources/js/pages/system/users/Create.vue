<script lang="ts">
import { defineComponent } from 'vue';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import CreateUser from '@/pages/system/users/partials/CreateUser.vue';
import { Head } from '@inertiajs/vue3';

export default defineComponent({
    name: 'Create',
    components: {
        Head,
        CreateUser,AppLayout
    },
    props: {
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
    <AppLayout :breadcrumbs="[{title: 'System', href: ''},{title: 'Users', href: route('users.index')}, {title: 'Create User', href:'#'}]">
        <Head title="Create User" />
        <CreateUser :roles="roles" :availablePermissions="permissions" />
    </AppLayout>
</template>

<style scoped>

</style>