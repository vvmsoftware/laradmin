<script>
import { defineComponent } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Head } from '@inertiajs/vue3';
import { Label } from '@/components/ui/label/index.js';
import { Input } from '@/components/ui/input/index.js';
import InputError from '@/components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import SaveButton from '@/components/buttons/SaveButton.vue';
import { Button } from '@/components/ui/button/index.js';
import { PinInput, PinInputGroup, PinInputSeparator, PinInputInput } from '@/components/ui/pin-input/index.js';
import LoadingMask from '@/components/LoadingMask.vue';

export default defineComponent({
    name: 'TwoFactor',
    components: { LoadingMask, PinInput, PinInputGroup, SaveButton,PinInputInput,PinInputSeparator, InputError, Input, Label, Head, HeadingSmall, AppLayout, SettingsLayout, Button },
    props: {
        twoFactorCode: {
            type: String,
            required: true,
        },
        'status': {
            type: Boolean,
            required: true,
        },
    },
    data() {
        return {
            breadcrumbItems: [
                {
                    title: 'Two-factor authentication',
                    href: '/settings/two-factor',
                },
            ],
            form: useForm({
                otp: '',
            }),
            value: [],
        };
    },
    methods: {
        submitForm() {
            this.form.post(route('two-factor.verify'), {
                preserveScroll: true,
                onSuccess: () => this.form.reset(),
            });
        },
        handleComplete(){
            this.form.otp = this.value.join('');
            this.form.delete(route('two-factor.disable'), {
                preserveScroll: true,
                onSuccess: () => this.form.reset(),
                onError: () => {
                    this.value = [];
                },
            });
        },
        disable2fa() {
            this.form.delete(route('two-factor.disable'));
        }
    },
    computed: {
        description() {
            return this.status ? 'Disable two-factor authentication by entering your code below' : 'Enable and manage your two-factor authentication';
        }
    }
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Two Factor Authentication" :description="description" />
                <template v-if="!status">
                <div class="w-full flex flex-row">
                    <div class="border border-gray-200 rounded-lg p-2">
                        <img :src="route('two-factor.qrcode')"  alt="QR Code scan"/>
                    </div>
                    <div class="ml-5 text-sm">
                        <p>Scan the QR code below with your phone's authenticator app.
                            Can't scan the code? Enter the following code manually: </p>
                        <p class="text-center mt-5"><code class="border border-red-500 p-2">{{ twoFactorCode }}</code></p>
                    </div>
                </div>
                <div class="flex flex-col gap-y-3 mt-5">
                    <Label for="otp">Once done, please input the generated one time password below</Label>
                    <Input
                        id="otp"
                        ref="otp"
                        v-model="form.otp"
                        type="text"
                        class="mt-1 block w-full"
                        autocomplete="none"
                        placeholder="One time password"
                    />
                    <InputError :message="form.errors.otp" />
                </div>
                <SaveButton :disabled="form.processing" @click="submitForm">Enable 2FA-Auth</SaveButton>
                </template>
                <template v-else>
                    <LoadingMask :loading="form.processing" />
                    <div class="flex flex-col gap-y-1.5 text-slate-900">
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
                                />
                            </PinInputGroup>
                        </PinInput>
                        <InputError :message="form.errors.otp" />
                    </div>
                    <Button variant="destructive" :disabled="form.processing" @click="disable2fa">Disable 2FA-Auth</Button>
                </template>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>

<style scoped>

</style>