<script>
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
    TableCaption,
} from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger, DropdownMenuCheckboxItem } from '@/components/ui/dropdown-menu'
import { Input } from '@/components/ui/input'
import { cn } from '@/lib/utils'
import {CaretSortIcon, ChevronDownIcon, ChevronUpIcon} from "@radix-icons/vue"
import LoadingMask from "@/components/LoadingMask.vue";
import Pagination from "@/components/Pagination.vue";


export default {
    name: "ServerTable" ,
    props: {
        columns: {
            type: Array,
            required: true
        },
        items: {
            type: Array,
            required: true
        },
        sort: {
            type: Object,
            required: false,
            default: () => ({
                column: "id",
                direction: "asc",
            })
        },
        loading: {
            type: Boolean,
            required: false,
            default: false
        },
        selectable: {
            type: Boolean,
            required: false,
            default: false
        },
        pagination: {
            type: Object,
            required: false,
            default: () => ({
                links: [],
                total: 0,
                perPage: 20,
                page: 1
            })
        }
    },
    components: {
        Pagination, LoadingMask, Table, TableBody, TableCell, TableHead, TableHeader, TableRow,TableCaption,Checkbox,
        CaretSortIcon, ChevronDownIcon,ChevronUpIcon,
        Button, DropdownMenu, DropdownMenuContent, DropdownMenuTrigger, DropdownMenuCheckboxItem, Input,
        cn,
    },
    data() {
        return {
            selected: [],
            selectAll: false,
        }
    },
    mounted() {
        console.log(this.columns)
    },
    methods: {
        cn,
        sortBy(column) {
            // First ensure the column is sortable
            const col = this.columns.find(col => col.id === column)
            if (!col.canSort) {
                return
            }
            if (this.sort.column === column) {
                if (this.sort.direction === 'asc') {
                    this.$emit('update:sort', column,'desc')
                    return
                } else {
                    this.sort.direction = 'asc'
                    this.$emit('update:sort', column,'asc')
                    return
                }
            }
            this.$emit('update:sort', column,'desc')
        },
        handleSelectAll(e) {
            if (e) {
                this.selected = this.items.map(item => item.id)
            } else {
                this.selected = []
            }
            this.$emit('update:selected', this.selected)
        },
        handleCheck() {
            this.$emit('update:selected', this.selected)
        }
    }
}
</script>

<template>
    <div class="w-full">
        <div class="relative rounded-md border">

            <LoadingMask :loading="loading" />
            <Table class="bg-sidebar">
                <TableHeader class="border-b border-sidebar-border">
                    <TableHead v-if="selectable" class="w-1 nowrap">
                        <!-- Render checkbox for select all -->
                        <Checkbox v-model="selectAll" @update:checked="handleSelectAll" />
                    </TableHead>
                    <TableHead
                        v-for="column in columns"
                        :id="column.id"
                        class="first:pl-5 last:pr-5"
                        :class="cn(column.class ? column.class : '', column.isVisible ? '': 'hidden')"
                    >
                        <div class="flex flex-row gap-2 text-xs font-medium text-muted-foreground"
                             :class="cn(column.canSort ? 'cursor-pointer':'', column.class ?? '','dark:hover:text-warning')"
                             @click="sortBy(column.id)"
                        >
                            {{ column.header }}
                            <template v-if="column.canSort">
                                <ChevronDownIcon class="h-3 w-3" v-if="sort.column === column.id && sort.direction === 'desc'"/>
                                <ChevronUpIcon class="h-3 w-3" v-else-if="sort.column === column.id && sort.direction === 'asc'"/>
                                <CaretSortIcon class="h-3 w-3" v-else />
                            </template>
                        </div>
                    </TableHead>
                </TableHeader>
                <TableBody>
                    <template v-if="items?.length > 0">
                        <TableRow v-for="item in items" :key="item.id">
                            <template v-if="selectable">
                                <TableCell class="w-1">
                                    <Checkbox v-model:checked="selected[String(item.id)]" :value="item.id" @changed="handleCheck" />
                                </TableCell>
                            </template>
                            <template v-for="column in columns" :key="column.id">
                                <slot :item="item" :column="column" v-if="column.isVisible" />
                            </template>
                        </TableRow>
                    </template>
                    <template v-else>
                        <TableRow>
                            <TableCell :colspan="columns.length">
                                <div class="flex justify-center items-center space-x-2">
                                    <span class="text-muted-foreground">No data available</span>
                                </div>
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>

        <div class="flex items-center justify-end space-x-2 p-4">
            <div class="flex-1 text-xs text-muted-foreground">
                Displaying {{ (pagination.page - 1) * pagination.perPage + 1 }} to {{ Math.min(pagination.page * pagination.perPage, pagination.total) }} of {{ pagination.total }} entries
            </div>
            <div class="space-x-2">
                <pagination
                    :links="pagination.links"
                    :total="pagination.total"
                    :per-page="pagination.perPage"
                    :current_page="parseInt(pagination.page)"
                    :cls="cn('text-xs','h-6','w-6')"
                    @update:page="$emit('update:page', $event)"
                />
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>