<script>
import {defineComponent} from 'vue'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { Head } from '@inertiajs/vue3';
import { onSort, onLimit, onSearch, onFilters, onPageChange, onApplyFilters} from "@/lib/tableUtils.js";
import ServerTable from "@/components/tables/server/ServerTable.vue";
import ServerTableSearch from "@/components/tables/server/ServerTableSearch.vue";
import ServerTableFacetedFilter from "@/components/tables/server/ServerTableFacetedFilter.vue";
import ServerTableViewOptions from "@/components/tables/server/ServerTableViewOptions.vue";
import TableCell from "@/components/ui/table/TableCell.vue";
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Link } from '@inertiajs/vue3'
import badge from '@/components/ui/badge/Badge.vue'
import { Button } from '@/components/ui/button/index.js';
import { PlusCircledIcon } from '@radix-icons/vue';

export default defineComponent({
    name: "UsersIndex",
    components: {
        Button,
        Head,
        AppLayout, PlaceholderPattern, ServerTable, ServerTableSearch, ServerTableFacetedFilter, ServerTableViewOptions,TableCell,
        onSort, onLimit, onSearch, onFilters, onPageChange, onApplyFilters, Avatar, AvatarFallback, AvatarImage, Link, badge, PlusCircledIcon
    },
    props: {
        users: {
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
            breadcrumbs: [
                {
                    title: 'System',
                    href: '/system',
                },
                {
                    title: 'Users',
                    href: '/system/users',
                },
            ],
            columns: [
                {
                    id: 'avatar',
                    header: 'Avatar',
                    cell: 'avatar',
                    canSort: false,
                    canFilter: false,
                    canHide: false,
                    isVisible: true,
                },
                {
                    id: 'email',
                    header: 'Email',
                    cell: 'email',
                    canSort: true,
                    canFilter: true,
                    canHide: true,
                    isVisible: true,
                },
                {
                    id: 'name',
                    header: 'Name',
                    cell: 'name',
                    canSort: true,
                    canFilter: true,
                    canHide: true,
                    isVisible: true,
                },
                {
                    id: 'roles',
                    header: 'Roles',
                    cell: 'roles',
                    canSort: false,
                    canFilter: true,
                    canHide: true,
                    isVisible: true,
                },
                {
                    id: 'enabled',
                    header: 'Status',
                    cell: 'enabled',
                    canSort: true,
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
                }
            ],
            loading: false,
            availableStatuses: ['Active','Inactive'],
            filters: {
                search: '',
                page: 1,
                perPage: "20",
                sortBy: 'id',
                sortDir: 'desc',
                statuses: [],
                roles: [],
            }
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
        // Get the statuses params, if present
        const statuses = [];
        params.forEach((value, key) => {
            // Check if the parameter is part of the statuses array
            if (key.startsWith('statuses[')) {
                // Extract the index from the key
                const index = parseInt(key.match(/\d+/)[0], 10);
                // Assign the value to the correct index in the statuses array
                statuses[index] = value;
            }
        });
        // Set the statuses filter
        this.filters.statuses = statuses;
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
    methods: {
        onSort,
        onLimit,
        onSearch,
        onFilters,
        onPageChange,
        onApplyFilters,
        onStatusChange(selectedValues) {
            this.filters.statuses = Array.from(selectedValues)
            this.filters.page = 1
            this.getData()
        },
        onRoleChange(selectedValues) {
            this.filters.roles = Array.from(selectedValues)
            this.filters.page = 1
            this.getData()
        },
        getData() {
            this.loading = true;
            this.$inertia.get(route('users.index'),this.filters,{
                preserveState: true,
                onFinish: () => {
                    this.loading = false;
                }
            })
        }
    },
    watch: {
        columns: {
            handler: function (val) {
                localStorage.setItem(this.$options.name + '-columns', JSON.stringify(val))
            },
            deep: true,
        }
    }
})
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Manage Users" />
        <template #actions>
            <Link :href="route('users.create')">
                <Button variant="default" >
                    <PlusCircledIcon class="w-5 h-5" />
                    Create User
                </Button>
            </Link>
        </template>
        <div class="m-5">
            <div class="flex flex-row justify-between items-center gap-1 mb-2">
                <ServerTableSearch
                    :search="filters.search"
                    :per-page="filters.perPage"
                    @filter="onSearch"
                    @limit="onLimit"
                    class="w-1/2"/>
                <ServerTableFacetedFilter
                    title="Status"
                    :options="availableStatuses"
                    :selected="filters.statuses"
                    @update:selectedValues="onStatusChange"
                />
                <ServerTableFacetedFilter
                    title="Roles"
                    :options="roles"
                    :selected="filters.statuses"
                    @update:selectedValues="onRoleChange"
                />
                <ServerTableViewOptions
                    :columns="columns"
                />
            </div>
            <ServerTable
                :items="users.data"
                :columns="columns"
                :sort="{ column: filters.sortBy, direction: filters.sortDir }"
                :loading="loading"
                :pagination="{
                            links: users.links,
                            total: users.total,
                            perPage: parseInt(filters.perPage),
                            page: users.current_page
                        }"
                v-slot="{item,column}"
                @update:sort="onSort"
                @update:page="onPageChange"
            >
                <TableCell v-if="column.id === 'avatar'">
                    <Avatar>
                        <AvatarImage :src="item?.avatar || ''" :alt="item.name" />
                        <AvatarFallback>N/A</AvatarFallback>
                    </Avatar>
                </TableCell>
                <TableCell v-else-if="column.id === 'enabled'">
                    <badge :variant="item.enabled ? 'success' : 'destructive'">
                        {{ item.enabled ? 'Active' : 'Inactive' }}
                    </badge>
                </TableCell>
                <TableCell v-else-if="column.id === 'created_at' || column.id === 'updated_at'">
                    {{ item[column.id] }}
                </TableCell>
                <TableCell v-else-if="column.id === 'roles'">
                    <span class="capitalize">{{ item[column.id] }}</span>
                </TableCell>
                <TableCell v-else-if="column.id === 'name'">
                    <span class="underline decoration-dotted cursor-pointer">
                        <Link :href="route('users.show', item.id)">{{ item[column.id] }}</Link>
                    </span>
                </TableCell>
                <TableCell v-else>
                    {{ item[column.id] }}
                </TableCell>
            </ServerTable>
        </div>
    </AppLayout>
</template>

<style scoped>

</style>