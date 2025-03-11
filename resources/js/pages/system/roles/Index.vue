<script>
import AppLayout from "@/layouts/app/AppSidebarLayout.vue";
import ServerTable from "@/components/tables/server/ServerTable.vue";
import {Avatar} from "@/components/ui/avatar/index.js";
import ServerTableSearch from "@/components/tables/server/ServerTableSearch.vue";
import { Head, Link } from '@inertiajs/vue3';
import ServerTableViewOptions from "@/components/tables/server/ServerTableViewOptions.vue";
import ServerTableFacetedFilter from "@/components/tables/server/ServerTableFacetedFilter.vue";
import {Badge} from "@/components/ui/badge/index.js";
import {TableCell} from "@/components/ui/table/index.js";
import { onSort, onLimit, onSearch, onFilters, onPageChange, onApplyFilters} from "@/lib/tableUtils.js";
import { CardStackMinusIcon, TrashIcon, DotsHorizontalIcon, Pencil2Icon, Pencil1Icon, PlusCircledIcon, PersonIcon } from '@radix-icons/vue';
import { Button } from "@/components/ui/button";
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuLabel } from "@/components/ui/dropdown-menu";
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { Loader2 } from 'lucide-vue-next';
//import CreateRoleForm from "@/Pages/Roles/Partials/CreateRoleForm.vue";

export default {
    name: "RoleIndex",
    components: {
        Loader2,
        PersonIcon, ConfirmDialog,
        PlusCircledIcon,
        Head,
        TableCell, Badge, CardStackMinusIcon, TrashIcon, DotsHorizontalIcon, Pencil2Icon, Pencil1Icon,
        ServerTableFacetedFilter, ServerTableViewOptions, Link, ServerTableSearch, Avatar,
        Button, DropdownMenu, DropdownMenuContent, DropdownMenuTrigger, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuLabel,
        ServerTable, AppLayout},
    props: {
        roles: {
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
            columns: [
                {
                    id: 'id',
                    header: 'ID',
                    cell: 'id',
                    canSort: true,
                    canFilter: true,
                    canHide: true,
                    isVisible: true,
                },
                {
                    id: 'name',
                    header: 'Role name',
                    cell: 'name',
                    canSort: true,
                    canFilter: true,
                    canHide: true,
                    isVisible: true,
                },
                {
                    id: 'users_count',
                    header: 'User Count',
                    cell: 'users_count',
                    canSort: false,
                    canFilter: false,
                    canHide: true,
                    isVisible: true,
                },
                {
                    id: 'created_at',
                    header: 'Created At',
                    cell: 'created_at',
                    canSort: true,
                    canFilter: true,
                    canHide: true,
                    isVisible: true,
                },
                {
                    id: 'updated_at',
                    header: 'Updated At',
                    cell: 'updated_at',
                    canSort: true,
                    canFilter: true,
                    canHide: true,
                    isVisible: true,
                },
                {
                    id: 'actions',
                    header: 'Actions',
                    cell: 'actions',
                    canSort: false,
                    canFilter: false,
                    canHide: false,
                    isVisible: true,
                    class: ""
                }
            ],
            filters: {
                search: '',
                page: 1,
                perPage: "20",
                sortBy: 'id',
                sortDir: 'desc',
            },
            loading: false,
            isOpen: false,
        }
    },
    methods: {
        onSort, onLimit, onSearch, onFilters, onPageChange, onApplyFilters,
        getData() {
            this.loading = true;
            this.$inertia.get(route('roles.index'),this.filters,{
                preserveState: true,
                onFinish: () => {
                    this.loading = false;
                }
            })
        },
        onEmptyConfirm(id) {
            this.$inertia.patch(route('roles.empty', id));
        },
        onDeleteConfirm(id) {
            this.$inertia.delete(route('roles.delete', id));
        }
    },
    created() {
        // Get the query params from the URL
        const params = new URLSearchParams(window.location.search)
        // Set the filters based on the query params
        // Grab the per-page setting from the browser storage, if available
        let perPage = localStorage.getItem('table-per-page')
        if (!perPage) {
            perPage = "20";
        }
        this.filters = {
            search: params.get('search') || '',
            page: params.get('page') || "1",
            perPage: params.get('perPage') || perPage,
            sortBy: params.get('sortBy') || 'id',
            sortDir: params.get('sortDir') || 'desc',
        }
        // Restore columns from local storage, if available
        // Use component name as key to avoid conflicts
        const columns = JSON.parse(localStorage.getItem(this.$options.name + '-columns'))
        if (columns) {
            // Count the number of columns and compare it to the default columns count
            // If the counts are the same, restore the columns
            if (columns.length === this.columns.length) {
                console.log("Restoring columns from browser storage", columns)
                this.columns = columns
            }
        }
    },
    mounted() {
        console.log(this.roles);
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="[{title: 'System', href: ''},{title: 'Roles', href: route('roles.index')}]">
        <Head title="System Roles" />
        <template #actions>
            <Link :href="route('roles.create')">
                <Button variant="default" >
                    <PlusCircledIcon class="w-5 h-5" />
                    Create Role
                </Button>
            </Link>
        </template>
        <div class="py-5">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col gap-2">
                <div class="flex flex-row justify-between items-center gap-1">
                    <ServerTableSearch
                        :search="filters.search"
                        :per-page="filters.perPage"
                        @filter="onSearch"
                        @limit="onLimit"
                        class="w-1/2"/>
                    <ServerTableViewOptions
                        :columns="columns"
                    />
                </div>
                <ServerTable
                    :items="roles.data"
                    :columns="columns"
                    :sort="{ column: filters.sortBy, direction: filters.sortDir }"
                    :loading="loading"
                    :pagination="{
                        links: roles.links,
                        total: roles.total,
                        perPage: parseInt(filters.perPage),
                        page: roles.current_page
                    }"
                    v-slot="{item,column}"
                    @update:sort="onSort"
                    @update:page="onPageChange"
                >
                    <TableCell v-if="column.id === 'name'">
                        <span class="underline decoration-dotted cursor-pointer w-full capitalize">
                            <Link :href="route('roles.show', item.id)">{{ item[column.id] }}</Link>
                        </span>
                    </TableCell>
                    <TableCell v-else-if="column.id === 'actions'">
                        <div class="flex flex-row gap-1 justify-center items-center">
                            <Link :href="route('roles.show', item.id)"><Pencil2Icon class="hover:stroke-warning hover:stroke-[.5]"/></Link>
                        <ConfirmDialog confirm="Yes, Proceed" @confirm="onEmptyConfirm(item.id)" as-child>
                            <template #trigger>
                                <CardStackMinusIcon class="ml-1 hover:stroke-warning hover:stroke-[.5]"/>
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
                        <ConfirmDialog confirm="Yes, Proceed" @confirm="onDeleteConfirm(item.id)" as-child>
                            <template #trigger>
                                <TrashIcon class="hover:stroke-destructive hover:stroke-[.5]"/>
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
                        </div>
                    </TableCell>
                    <TableCell v-else>
                        {{ item[column.id] }}
                    </TableCell>
                </ServerTable>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>

</style>