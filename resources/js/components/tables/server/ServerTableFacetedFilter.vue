<script>
import { CheckIcon, PlusCircledIcon } from "@radix-icons/vue";
import { Separator } from "@/components/ui/separator";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Popover,PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList, CommandSeparator } from "@/components/ui/command";
import { cn } from '@/lib/utils'

export default {
    name: "ServerTableFacetedFilter",

    components: {
        CheckIcon,
        PlusCircledIcon,
        Badge,
        Button,
        Popover,
        PopoverContent,
        PopoverTrigger,
        Separator,
        Command,
        CommandEmpty,
        CommandGroup,
        CommandInput,
        CommandItem,
        CommandList,
        CommandSeparator,
    },
    props: {
        title: {
            type: String,
            required: true,
        },
        options: {
            type: Array,
            required: true,
        },
        column: {
            type: Object,
            required: false,
            default: null,
        },
        selected: {
            type: Array,
            required: false,
            default: () => [],
        },
        clearText: {
            type: String,
            required: false,
            default: 'Clear filters',
        },
        maxDisplay: {
            type: Number,
            required: false,
            default: 2,
        },
        reset: {
            type: Number,
            required: false,
            default: 0,
        },
    },
    data() {
        return {
            selectedValues: new Set(this.selected || []),
        }
    },
    watch: {
        selectedValues: {
            handler: function (newVal) {
                this.$emit('update:selectedValues', Array.from(newVal))
            },
            deep: true
        },
        reset() {
            this.selectedValues.clear();
        }
    },
    emits: ['update:selectedValues'],
    methods: {
        cn,
    }
}
</script>

<template>
    <Popover>
        <PopoverTrigger as-child>
            <Button variant="outline" size="sm" class="h-8 border-dashed justify-start">
                <PlusCircledIcon class="h-4 w-4" />
                {{ title }}
                <template v-if="selectedValues.size > 0">
                    <Separator orientation="vertical" class="mx-2 h-4" />
                    <Badge
                        variant="secondary"
                        class="rounded-sm px-1 font-normal lg:hidden"
                    >
                        {{ selectedValues.size }}
                    </Badge>
                    <div class="hidden space-x-1 lg:flex">
                        <Badge
                            v-if="selectedValues.size > maxDisplay"
                            variant="secondary"
                            class="rounded-sm px-1 font-normal"
                        >
                            {{ selectedValues.size }} selected
                        </Badge>

                        <template v-else>
                            <Badge
                                v-for="option in options
                  .filter((option) => selectedValues.has(option))"
                                :key="option"
                                variant="secondary"
                                class="rounded-sm px-1 font-normal"
                            >
                                {{ option }}
                            </Badge>
                        </template>
                    </div>
                </template>
            </Button>
        </PopoverTrigger>
        <PopoverContent class=" p-0" align="start">
            <Command>
                <CommandInput :placeholder="title" />
                <CommandList>
                    <CommandEmpty>No results found.</CommandEmpty>
                    <CommandGroup>
                        <CommandItem
                            v-for="option in options"
                            :key="option.value"
                            :value="option"
                            @select="(e) => {

                const isSelected = selectedValues.has(option)
                console.log(option)
                if (isSelected) {
                  selectedValues.delete(option)
                }
                else {
                  selectedValues.add(option)
                }
                const filterValues = Array.from(selectedValues)
                column?.setFilterValue(
                  filterValues.length ? filterValues : undefined,
                )
              }"
                        >
                            <div
                                :class="cn(
                  'mr-2 flex h-4 w-4 items-center justify-center rounded-sm border border-primary',
                  selectedValues.has(option)
                    ? 'bg-primary text-primary-foreground'
                    : 'opacity-50 [&_svg]:invisible',
                )"
                            >
                                <CheckIcon :class="cn('h-4 w-4')" />
                            </div>
                            <component :is="option.icon" v-if="option.icon" class="mr-2 h-4 w-4 text-muted-foreground" />
                            <span>{{ option }}</span>

                        </CommandItem>
                    </CommandGroup>

                    <template v-if="selectedValues.size > 0">
                        <CommandSeparator />
                        <CommandGroup>
                            <CommandItem
                                :value="{ label: clearText }"
                                class="justify-center text-center"
                                @select="selectedValues.clear()"
                            >
                                {{ clearText }}
                            </CommandItem>
                        </CommandGroup>
                    </template>
                </CommandList>
            </Command>
        </PopoverContent>
    </Popover>
</template>

<style scoped>

</style>