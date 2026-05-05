<template>
  <div class="flex flex-col items-center justify-center py-16 px-4 text-center">
    <div class="w-16 h-16 rounded-2xl bg-muted flex items-center justify-center mb-4">
      <component :is="icon" class="w-7 h-7 text-muted-foreground" />
    </div>
    <h3 class="text-lg font-bold mb-1">{{ title }}</h3>
    <p class="text-sm text-muted-foreground max-w-xs mb-5">{{ description }}</p>
    <button v-if="actionLabel" @click="$emit('action')" class="px-6 py-3 rounded-xl bg-primary text-white font-semibold text-sm transition-all hover:opacity-90">
      {{ actionLabel }}
    </button>
  </div>
</template>

<script setup>
defineProps({
  icon: { type: [Object, Function], required: true },
  title: { type: String, required: true },
  description: { type: String, default: '' },
  actionLabel: { type: String, default: '' },
})
defineEmits(['action'])
</script>
