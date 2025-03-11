<script lang="ts">
import { defineComponent } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

export default defineComponent({
    name: 'NoTwoFactor',
    components: { Head, Link }
});
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] dark:bg-[#0a0a0a] lg:justify-center lg:p-8">
       <div class="duration-750 starting:opacity-0 flex w-full items-center justify-center opacity-100 transition-opacity lg:grow">
            <main class="flex w-full max-w-[335px] flex-col-reverse overflow-hidden rounded-lg lg:max-w-xl lg:flex-row">
                <div
                    class="flex-1 rounded-bl-lg rounded-br-lg bg-white p-6 pb-12 text-[13px] leading-[20px] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] lg:rounded-br-none lg:rounded-tl-lg lg:p-10"
                >
                    <h1 class="mb-1 font-medium text-lg">No two factor authentication</h1>
                    <p class="mb-2 text-[#706f6c] dark:text-[#A1A09A]">
                        Some pages require two factor authentication to be enabled. Please, enable two factor authentication to access these pages and secure your account.
                    </p>

                    <div class="flex justify-center items-center mt-5">
                        <Link
                            :href="route('two-factor.index')"
                            target="_blank"
                            class="inline-block rounded-sm border border-black bg-[#1b1b18] px-5 py-1.5 text-sm leading-normal text-white hover:border-black hover:bg-black dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white"
                        >
                            Enable Now
                        </Link>
                    </div>
                </div>
            </main>
        </div>
        <div class="h-14.5 hidden lg:block"></div>
    </div>
</template>

<style scoped>

</style>