<script setup lang="ts">
defineProps<{
  name: string
  subtitle?: string | null
  image?: string | null
  template: string
}>()
</script>

<template>
  <div class="preview-wrapper">
    <!-- Classic -->
    <div v-if="template === 'classic'" class="preview classic">
      <div class="plogo">
        <svg viewBox="0 0 48 48" fill="none" class="pstar">
          <path d="M24 4L29 16H43L32 24L36 38L24 30L12 38L16 24L5 16H19L24 4Z" fill="#D4AF37"/>
          <circle cx="24" cy="24" r="18" stroke="#D4AF37" stroke-width="1.5" fill="none"/>
          <path d="M24 10L27 17H35L29 22L31.5 30L24 25.5L16.5 30L19 22L13 17H21L24 10Z" fill="#D4AF37" opacity="0.6"/>
          <rect x="22" y="32" width="4" height="6" rx="2" fill="#D4AF37"/>
        </svg>
      </div>
      <div class="ptext">
        <span class="pname">{{ name }}</span>
        <span class="psub">{{ subtitle || 'SUBTITLE' }}</span>
      </div>
    </div>

    <!-- Minimal -->
    <div v-else-if="template === 'minimal'" class="preview minimal">
      <div class="pbar"></div>
      <span class="pname">{{ name }}</span>
      <span v-if="subtitle" class="psub">{{ subtitle }}</span>
    </div>

    <!-- Banner -->
    <div v-else class="preview banner">
      <div class="banner-bg" :style="image ? { backgroundImage: `url(${image})` } : {}">
        <div class="banner-overlay">
          <span class="pname">{{ name }}</span>
          <span v-if="subtitle" class="psub">{{ subtitle }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.preview-wrapper {
  width: 100%;
  height: 64px;
  border-radius: 8px;
  overflow: hidden;
}
.preview {
  width: 100%;
  height: 100%;
  display: flex;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

/* Classic */
.classic .plogo {
  flex: 0 0 48px;
  background: #0A1F3F;
  display: flex;
  align-items: center;
  justify-content: center;
}
.classic .pstar { width: 24px; height: 24px; }
.classic .ptext {
  flex: 1;
  background: white;
  padding: 6px 12px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 2px;
}
.classic .pname { font-size: 15px; font-weight: 800; color: #222; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.classic .psub { font-size: 10px; font-weight: 600; color: #999; text-transform: uppercase; letter-spacing: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* Minimal */
.minimal {
  background: rgba(10, 15, 25, 0.88);
  padding: 8px 14px;
  align-items: center;
  gap: 8px;
  border-left: 3px solid #D4AF37;
}
.minimal .pbar { display: none; }
.minimal .pname { font-size: 14px; font-weight: 700; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.minimal .psub { font-size: 10px; font-weight: 500; color: #aaa; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* Banner */
.banner .banner-bg {
  width: 100%;
  height: 100%;
  background-size: cover;
  background-position: center;
  background-color: #0A1F3F;
}
.banner .banner-overlay {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, rgba(10, 31, 63, 0.92), rgba(10, 31, 63, 0.75));
  padding: 8px 14px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 2px;
}
.banner .pname { font-size: 14px; font-weight: 800; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.banner .psub { font-size: 10px; font-weight: 500; color: #D4AF37; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
</style>
