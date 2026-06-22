<script setup lang="ts">
defineProps<{ show: boolean; title?: string }>()
const emit = defineEmits<{ close: [] }>()
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show" class="modal-backdrop" @click.self="emit('close')">
        <div class="modal-panel">
          <div class="modal-header">
            <h3 class="modal-title">{{ title ?? '' }}</h3>
            <button @click="emit('close')" class="modal-close">&times;</button>
          </div>
          <div class="modal-body">
            <slot />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}
.modal-panel {
  background: #1f2937;
  border: 1px solid #374151;
  border-radius: 16px;
  width: 90%;
  max-width: 480px;
  max-height: 90vh;
  overflow: auto;
  box-shadow: 0 20px 60px rgba(0,0,0,0.5);
}
.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid #374151;
}
.modal-title {
  font-size: 16px;
  font-weight: 600;
  color: #f9fafb;
  margin: 0;
}
.modal-close {
  background: none;
  border: none;
  color: #9ca3af;
  font-size: 24px;
  cursor: pointer;
  padding: 0 4px;
}
.modal-close:hover { color: #f9fafb; }
.modal-body { padding: 20px; }
.modal-enter-active { transition: all 0.2s ease-out; }
.modal-leave-active { transition: all 0.15s ease-in; }
.modal-enter-from { opacity: 0; }
.modal-enter-from .modal-panel { transform: scale(0.95); }
.modal-leave-to { opacity: 0; }
</style>
