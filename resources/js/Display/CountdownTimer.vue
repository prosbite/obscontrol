<script setup lang="ts">
import { computed, ref, watch, onUnmounted } from 'vue'
import { useGraphicsStore } from '@/Stores/graphics'

const store = useGraphicsStore()
const displayTime = ref('')
let interval: ReturnType<typeof setInterval> | null = null

const running = computed(() => store.state.timerRunning)

function updateDisplay() {
  const state = store.state
  let remaining = state.timerRemaining
  if (state.timerRunning && state.timerStartedAt) {
    const elapsed = Date.now() / 1000 - state.timerStartedAt
    remaining = Math.max(0, state.timerDuration - elapsed)
  }
  const mins = Math.floor(remaining / 60)
  const secs = Math.floor(remaining % 60)
  displayTime.value = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`
}

watch(running, (val) => {
  if (val) {
    interval = setInterval(updateDisplay, 200)
  } else {
    if (interval) clearInterval(interval)
    updateDisplay()
  }
}, { immediate: true })

onUnmounted(() => { if (interval) clearInterval(interval) })
</script>

<template>
  <Transition name="timer">
    <div v-if="store.state.timerRunning || store.state.timerPaused" class="timer-overlay">
      <div class="timer-display">{{ displayTime }}</div>
    </div>
  </Transition>
</template>

<style scoped>
.timer-overlay {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}
.timer-display {
  font-size: 160px;
  font-weight: 800;
  color: white;
  text-shadow: 0 4px 20px rgba(0,0,0,0.5);
  letter-spacing: 4px;
}
.timer-enter-active { transition: all 0.5s ease-out; }
.timer-leave-active { transition: all 0.3s ease-in; }
.timer-enter-from { opacity: 0; transform: translate(-50%, -50%) scale(0.8); }
.timer-leave-to { opacity: 0; transform: translate(-50%, -50%) scale(1.1); }
</style>
