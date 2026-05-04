<template>
  <div :class="['animate-pulse rounded-xl bg-muted', widthClass, heightClass, props.class]" />
</template>

<script setup>
const props = defineProps({
  width: { type: String, default: 'full' },
  height: { type: String, default: '4' },
  class: { type: String, default: '' },
})

const widthClass = props.width === 'full' ? 'w-full' : `w-${props.width}`
const heightClass = `h-${props.height}`
</script>
