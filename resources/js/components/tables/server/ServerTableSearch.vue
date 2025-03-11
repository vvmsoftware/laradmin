<script>
import { Input } from "@/components/ui/input";
import { useDebounceFn } from "@vueuse/core"
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";

export default {
    name: "ServerTableSearch",
    methods: {
        handleChange(e) {
            this.localSearch = e.target.value;
        },
        debounceFilter: useDebounceFn(function (e) {
            // We don't want to emit the filter event if the search is empty or null
            // because this would cause double requests to the server on initial page load
            if (e === null || (e === '' && (this.search === null || this.search === ''))){ return; }
            this.$emit('filter', this.localSearch);
        }, 500),
    },
    components: {
        Input, Select, SelectContent, SelectGroup, SelectItem,
        SelectLabel, SelectTrigger, SelectValue
    },
    data() {
        return {
            localSearch: null,
            limit: 10,
        }
    },
    props: {
        search: {
            type: String,
            required: false,
            default: null,
        },
        perPage: {
            type: String,
            required: false,
            default: "20",
        },
        showSearch: {
            type: Boolean,
            required: false,
            default: true,
        },
        showPerPage: {
            type: Boolean,
            required: false,
            default: true,
        },
    },
    mounted() {
        this.limit = this.perPage.toString();
    },
    watch: {
        localSearch: {
            handler: 'debounceFilter',
            immediate: true,
        },
        search: {
            handler: function (val) {
                this.localSearch = val;
            },
            immediate: true,
        },
        perPage: {
            handler: function (val) {
                this.limit = ""+val;
            },
            immediate: true,
        },
        limit: {
            handler: function (val) {
                // we don't want to emit if the limit is null or the same as perPage
                if (val === null || val === this.perPage) { return; }
                this.$emit('limit', val);
            },
            immediate: true,
        },
    },

}
</script>

<template>
    <div class="flex flex-row gap-x-1">
        <div v-if="showSearch" :class="[showPerPage ? 'w-3/4':'w-full']">
            <Input
                placeholder="Search records"
                :model-value="localSearch"
                class="h-8 primaryInput w-full"
                @input="handleChange"
            />
        </div>
        <div v-if="showPerPage" :class="[showSearch ? 'w-1/4':'w-full']">
            <Select v-model="limit">
                <SelectTrigger class="h-8 bg-sidebar">
                    <SelectValue placeholder="Items per page" />
                </SelectTrigger>
                <SelectContent class="bg-sidebar">
                    <SelectGroup>
                        <SelectItem value="1">1 item</SelectItem>
                        <SelectItem value="5">5 items</SelectItem>
                        <SelectItem value="10">10 items</SelectItem>
                        <SelectItem value="20">20 items</SelectItem>
                        <SelectItem value="50">50 items</SelectItem>
                        <SelectItem value="100">100 items</SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>
        </div>
    </div>
</template>

<style scoped>

</style>