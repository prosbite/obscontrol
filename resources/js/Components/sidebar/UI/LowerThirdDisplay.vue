<script setup lang="ts">
import { ref, watch } from "vue"

interface Props {
  logo?: string
  name?: string
  title?: string
  accentColor?: string
  visible?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  logo: "",
  name: "Name Here",
  title: "TITLE HERE",
  accentColor: "#0A1F3F",
  visible: false,
})

const show = ref(false)

watch(() => props.visible, (val) => {
  show.value = val
}, { immediate: true })
</script>

<template>
  <Transition name="lt">
    <div v-if="show" class="lower-third">
      <div class="logo-section" :style="{ backgroundColor: accentColor }">
        <img v-if="logo" :src="logo" class="logo" alt="" />
        <svg v-else class="logo" viewBox="0 0 48 48" fill="none">
          <path d="M24 4L29 16H43L32 24L36 38L24 30L12 38L16 24L5 16H19L24 4Z" fill="#D4AF37"/>
          <circle cx="24" cy="24" r="18" stroke="#D4AF37" stroke-width="1.5" fill="none"/>
          <path d="M24 10L27 17H35L29 22L31.5 30L24 25.5L16.5 30L19 22L13 17H21L24 10Z" fill="#D4AF37" opacity="0.6"/>
          <rect x="22" y="32" width="4" height="6" rx="2" fill="#D4AF37"/>
        </svg>
      </div>
      <div class="text-section">
        <h1 class="header">{{ name }}</h1>
        <p class="subheader">{{ title }}</p>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
.lower-third {
  position: fixed;
  bottom: 120px;
  left: 60px;
  display: flex;
  width: 560px;
  height: 130px;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.35), 0 2px 8px rgba(0, 0, 0, 0.2);
  z-index: 100;
}

.logo-section {
  flex: 0 0 110px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px 0 0 12px;
}

.logo {
  width: 56px;
  height: 56px;
  object-fit: contain;
}

.text-section {
  flex: 1;
  background: white;
  padding: 16px 24px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.header {
  font-family: "Inter", "Segoe UI", system-ui, sans-serif;
  font-size: 36px;
  font-weight: 800;
  color: #222222;
  margin: 0;
  line-height: 1.2;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.subheader {
  font-family: "Inter", "Segoe UI", system-ui, sans-serif;
  font-size: 18px;
  font-weight: 600;
  color: #777777;
  text-transform: uppercase;
  letter-spacing: 2.5px;
  margin: 2px 0 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.lt-enter-active {
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.lt-leave-active {
  transition: all 0.3s ease-in;
}

.lt-enter-from {
  transform: translateX(-120%);
  opacity: 0;
}

.lt-leave-to {
  transform: translateX(-120%);
  opacity: 0;
}
</style>