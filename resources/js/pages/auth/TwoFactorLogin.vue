<script lang="ts">
import { defineComponent } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { PinInput, PinInputGroup, PinInputInput } from '@/components/ui/pin-input';
import SaveButton from '@/components/buttons/SaveButton.vue';
import LoadingMask from '@/components/LoadingMask.vue';
import InputError from '@/components/InputError.vue';

export default defineComponent({
    name: 'TwoFactorLogin',
    components: { InputError, LoadingMask, Head, Link, PinInput, PinInputGroup, PinInputInput, SaveButton },
    data() {
        return {
            value: [],
            form: useForm({
                otp: '',
            }),
        };
    },
    methods: {
        handleComplete(e) {
            this.form.otp = e.join('');
            this.form.post(route('two-factor.verify'), {
                preserveScroll: true,
                onSuccess: () => this.form.reset(),
                onError: () => {
                    this.value = [];
                },
            });
        },
    },
});
</script>

<template>
    <Head title="Two Factor Login" />
    <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] dark:bg-[#0a0a0a] lg:justify-center lg:p-8">
        <div class="duration-750 starting:opacity-0 flex w-full items-center justify-center opacity-100 transition-opacity lg:grow">
            <main class="flex w-full max-w-[335px] flex-col-reverse overflow-hidden rounded-lg lg:max-w-xl lg:flex-row">
                <div
                    class="flex-1 rounded-bl-lg rounded-br-lg bg-white p-6 pb-7 text-[13px] leading-[20px] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] lg:rounded-br-none lg:rounded-tl-lg "
                >
                    <LoadingMask :loading="form.processing" />
                    <h1 class="mb-1 font-medium text-lg">Two Factor Login</h1>
                    <p class="mb-2 text-[#706f6c] dark:text-[#A1A09A]">
                        Your account has two factor authentication enabled. Please, enter the code from your authenticator app.
                    </p>
                    <div class="flex flex-col gap-y-1.5 items-center justify-center text-[#706f6c] dark:text-[#A1A09A]">
                        <PinInput
                            id="pin-input"
                            v-model="value"
                            placeholder="○"
                            @complete="handleComplete"
                        >
                            <PinInputGroup>
                                <PinInputInput
                                    v-for="(id, index) in 6"
                                    :key="id"
                                    :index="index"
                                    :autofocus="index === 0"
                                />
                            </PinInputGroup>
                        </PinInput>
                        <InputError :message="form.errors.otp" />
                    </div>

                </div>
            </main>
        </div>
        <div class="h-14.5 hidden lg:block"></div>
    </div>
</template>

<style scoped>

</style>