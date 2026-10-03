<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-gray-950 px-4 py-8 text-white"
    >
        <div>
            <Link href="/">
                <ApplicationLogo class="h-16 w-16 fill-current text-indigo-500" />
            </Link>
        </div>

        <div
            class="mt-6 w-full max-w-md overflow-hidden rounded-2xl border border-gray-800 bg-gray-900 px-6 py-6 shadow-xl sm:px-8"
        >
            <slot />
        </div>
    </div>
</template>
