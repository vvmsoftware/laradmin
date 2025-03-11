<script>
import {
    Pagination,
    PaginationEllipsis,
    PaginationFirst,
    PaginationLast,
    PaginationList,
    PaginationListItem,
    PaginationNext,
    PaginationPrev,
} from '@/components/ui/pagination'
import {
    Button,
} from '@/components/ui/button'

export default {
    name: "PaginationLinks",
    components: {
        Button,
        Pagination,
        PaginationEllipsis,
        PaginationFirst,
        PaginationLast,
        PaginationList,
        PaginationListItem,
        PaginationNext,
        PaginationPrev,
    },
    emits: ['update:page'],
    props: {
        links: {
            type: Array,
            required: true,
        },
        total: {
            type: Number,
            required: true,
        },
        perPage: {
            type: Number,
            default: 10,
        },
        current_page: {
            type: Number,
            default: 1,
        },
        cls: {
            type: String,
            default: '',
        }
    },
    methods: {
        pageChanged(page) {
            this.$emit('update:page', {
                page: page,
                link: this?.links[page]?.url || null,
            });
        },
    },
}
</script>

<template>
    <Pagination @update:page="pageChanged" :items-per-page="perPage" v-slot="{ page }" :total="total" :sibling-count="parseInt('1')" show-edges :default-page="current_page">
        <PaginationList v-slot="items" class="flex items-center gap-1">
            <PaginationFirst :class="cls" />
            <PaginationPrev :class="cls" />

            <template v-for="(item, index) in items.items">
                <PaginationListItem v-if="item.type === 'page'" :key="index" :value="item.value" as-child>
                    <Button :class="cls" :variant="item.value === page ? 'default' : 'outline'">
                        {{ item.value }}
                    </Button>
                </PaginationListItem>
                <PaginationEllipsis :class="cls" v-else :key="item.type" :index="index" />

            </template>

            <PaginationNext :class="cls" />
            <PaginationLast :class="cls" />
        </PaginationList>
    </Pagination>
</template>

<style scoped>

</style>