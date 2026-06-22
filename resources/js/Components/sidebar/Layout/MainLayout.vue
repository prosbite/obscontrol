<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { ref } from 'vue'

const sidebarOpen = ref(true)
</script>

<template>
  <div class="flex h-screen bg-gray-950 text-white">
    <aside :class="['bg-gray-900 border-r border-gray-800 flex flex-col transition-all duration-300', sidebarOpen ? 'w-64' : 'w-16']">
      <div class="p-4 border-b border-gray-800 flex items-center gap-3">
        <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center text-sm font-bold">GS</div>
        <span v-show="sidebarOpen" class="font-semibold text-sm">GraceStream</span>
      </div>
      <nav class="flex-1 p-2 space-y-1">
        <Link href="/control" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:bg-gray-800 hover:text-white transition-colors">
          <span class="w-5 h-5">📺</span>
          <span v-show="sidebarOpen">Dashboard</span>
        </Link>
        <Link href="/dashboard" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:bg-gray-800 hover:text-white transition-colors">
          <span class="w-5 h-5">⚙️</span>
          <span v-show="sidebarOpen">Settings</span>
        </Link>
      </nav>
      <div class="p-2 border-t border-gray-800">
        <Link href="/profile" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:bg-gray-800 transition-colors">
          <span class="w-5 h-5">👤</span>
          <span v-show="sidebarOpen">Profile</span>
        </Link>
      </div>
    </aside>
    <main class="flex-1 overflow-auto">
      <slot />
    </main>
  </div>
</template>
