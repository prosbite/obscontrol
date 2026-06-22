<script setup lang="ts">
import { computed } from 'vue'
import { useGraphicsStore } from '@/Stores/graphics'

const store = useGraphicsStore()
const visible = computed(() => store.state.announcementVisible)
const ann = computed(() => store.state.activeAnnouncement)
</script>

<template>
  <Transition name="ann">
    <div v-if="visible && ann" class="announcement-overlay">
      <div class="ann-content">
        <h2 class="ann-title">{{ ann.title }}</h2>
        <p class="ann-body">{{ ann.content }}</p>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
.announcement-overlay {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  color: white;
  text-shadow: 0 2px 10px rgba(0,0,0,0.6);
  max-width: 1000px;
  width: 80%;
}
.ann-title { font-size: 48px; font-weight: 700; margin: 0 0 16px; }
.ann-body { font-size: 28px; line-height: 1.5; margin: 0; }

.ann-enter-active { transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
.ann-leave-active { transition: all 0.3s ease-in; }
.ann-enter-from { opacity: 0; transform: translate(-50%, -50%) translateY(40px); }
.ann-leave-to { opacity: 0; transform: translate(-50%, -50%) translateY(-30px); }
</style>
