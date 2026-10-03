<script setup lang="ts">
import { computed } from 'vue'
import { useGraphicsStore } from '@/Stores/graphics'

const store = useGraphicsStore()
const visible = computed(() => store.state.scriptureVisible)
const scripture = computed(() => store.state.activeScripture)
const translationLabel = computed(() => scripture.value?.translation_abbr || scripture.value?.translation || null)
</script>

<template>
  <Transition name="scripture">
    <div v-if="visible && scripture" class="scripture-overlay">
      <div class="scripture-bar">
        <p class="scripture-text">{{ scripture.text }}</p>
        <p class="scripture-ref">
          {{ scripture.reference }}<span v-if="translationLabel" class="scripture-abbr"> ({{ translationLabel }})</span>
        </p>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
.scripture-overlay {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  width: 100%;
  z-index: 95;
}
.scripture-bar {
  position: relative;
  width: 100%;
  box-sizing: border-box;
  background: rgba(0, 0, 0, 0.6);
  padding: 40px 80px 72px;
}
.scripture-text {
  color: #ffffff;
  font-size: 34px;
  font-weight: 500;
  line-height: 1.5;
  text-align: center;
  margin: 0 auto;
  max-width: 1600px;
  text-shadow: 0 2px 12px rgba(0, 0, 0, 0.7);
  white-space: pre-wrap;
  overflow-wrap: break-word;
}
.scripture-ref {
  position: absolute;
  right: 80px;
  bottom: 20px;
  margin: 0;
  color: #d4af37;
  font-size: 24px;
  font-weight: 700;
  letter-spacing: 0.02em;
  text-align: right;
  text-shadow: 0 2px 8px rgba(0, 0, 0, 0.7);
}
.scripture-abbr {
  font-size: 20px;
  font-weight: 600;
  opacity: 0.9;
}

.scripture-enter-active { transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
.scripture-leave-active { transition: all 0.3s ease-in; }
.scripture-enter-from { opacity: 0; transform: translateY(100%); }
.scripture-leave-to { opacity: 0; transform: translateY(100%); }
</style>
