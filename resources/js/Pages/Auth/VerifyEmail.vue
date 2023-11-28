<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Button from '@/Components/UI/Button.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification" />

        <div class="mb-4 text-sm text-gray-600">
            Vielen Dank für die Anmeldung! Bevor Sie beginnen, könnten Sie Ihre E-Mail-Adresse verifizieren, indem Sie auf den Link
            klicken, den wir Ihnen gerade gemailt haben? Wenn Sie die E-Mail nicht erhalten haben, schicken wir Ihnen gerne eine neue zu.
        </div>

        <div class="mb-4 font-medium text-sm text-green-600" v-if="verificationLinkSent">
            Ein neuer Verifizierungslink wurde an die E-Mail-Adresse geschickt, die Sie bei der Registrierung angegeben haben.
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between">
                <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing" :style="'primary'" type="submit">
                    Bestätigungsemail erneut senden
                </Button>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >Log Out</Link
                >
            </div>
        </form>
    </GuestLayout>
</template>
