<script setup lang="ts">
import { computed } from 'vue'
import { useGraphicsStore } from '@/Stores/graphics'
import Classic from './LowerThirdDesigns/Classic.vue'
import Minimal from './LowerThirdDesigns/Minimal.vue'
import Banner from './LowerThirdDesigns/Banner.vue'

const store = useGraphicsStore()
const lt = computed(() => store.state.activeLowerThird)
const visible = computed(() => store.state.lowerThirdVisible)

const designMap: Record<string, any> = { classic: Classic, minimal: Minimal, banner: Banner }
const activeDesign = computed(() => lt.value ? designMap[lt.value.template] || Classic : null)
</script>

<template>
  <Transition name="lt">
    <div v-if="visible && lt" class="lower-third" :class="`design-${lt.template}`">
      <component :is="activeDesign" :name="lt.name" :subtitle="lt.subtitle" :image="lt.image" />
    </div>
  </Transition>
</template>

<style scoped>
.lower-third {
  position: fixed;
  bottom: 50px;
  left: 60px;
  z-index: 100;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 8px 32px rgba(0,0,0,0.35), 0 2px 8px rgba(0,0,0,0.2);
}

.design-minimal {
  background: rgba(10, 15, 25, 0.88);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-left: 5px solid #D4AF37;
}
.design-classic {
  display: flex;
  width: 560px;
  height: 130px;
}
.design-banner {
  width: 720px;
}

.lt-enter-active { transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
.lt-leave-active { transition: all 0.3s ease-in; }
.lt-enter-from { transform: translateX(-120%); opacity: 0; }
.lt-leave-to { transform: translateX(-120%); opacity: 0; }
</style>
