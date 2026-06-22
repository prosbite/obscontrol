<script setup lang="ts">
import { computed } from 'vue'
import { useGraphicsStore } from '@/Stores/graphics'

const store = useGraphicsStore()
const visible = computed(() => store.state.scriptureVisible)
const scripture = computed(() => store.state.activeScripture)
</script>

<template>
  <Transition name="scripture">
    <div v-if="visible && scripture" class="scripture-overlay">
      <div class="scripture-content">
        <p class="scripture-text">{{ scripture.text }}</p>
        <p class="scripture-ref">— {{ scripture.reference }}</p>
        <p v-if="scripture.translation" class="scripture-trans">{{ scripture.translation }}</p>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
.scripture-overlay {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  color: white;
  text-shadow: 0 2px 12px rgba(0,0,0,0.7);
  max-width: 1200px;
  width: 85%;
}
.scripture-text { font-size: 36px; font-weight: 500; line-height: 1.5; margin: 0 0 20px; }
.scripture-ref { font-size: 22px; font-weight: 600; opacity: 0.85; margin: 0; }
.scripture-trans { font-size: 16px; opacity: 0.6; margin: 4px 0 0; }

.scripture-enter-active { transition: all 0.6s ease-out; }
.scripture-leave-active { transition: all 0.3s ease-in; }
.scripture-enter-from { opacity: 0; transform: translate(-50%, -50%) scale(0.9); }
.scripture-leave-to { opacity: 0; transform: translate(-50%, -50%) scale(1.05); }
</style>
