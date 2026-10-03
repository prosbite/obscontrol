<script setup lang="ts">
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const page = usePage()

const appName = import.meta.env.VITE_APP_NAME || 'Control'

const userName = computed(() => (page.props.auth as any)?.user?.name ?? '')
</script>

<template>
  <div class="flex h-screen flex-col bg-gray-950 text-white">
    <header
      class="flex items-center justify-between border-b border-gray-800 bg-gray-900 px-6 py-3"
    >
      <span class="text-sm font-semibold tracking-wide">{{ appName }}</span>

      <div class="flex items-center gap-4 text-sm">
        <span v-if="userName" class="text-gray-400">{{ userName }}</span>
        <Link
          :href="route('profile.edit')"
          class="text-gray-400 hover:text-white"
        >
          Profile
        </Link>
        <Link
          :href="route('logout')"
          method="post"
          as="button"
          class="text-gray-400 hover:text-white"
        >
          Log Out
        </Link>
      </div>
    </header>

    <main class="flex-1 overflow-auto">
      <slot />
    </main>
  </div>
</template>
