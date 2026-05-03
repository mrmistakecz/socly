<script setup>
import { watch } from 'vue'
import { Heart, MessageCircle, UserPlus, X } from 'lucide-vue-next'

const props = defineProps({
  notifications: { type: Array, default: () => [] },
})

const emit = defineEmits(['dismiss'])

const iconMap = {
  like: Heart,
  comment: MessageCircle,
  follow: UserPlus,
}

const colorMap = {
  like: 'text-red-400',
  comment: 'text-primary',
  follow: 'text-accent',
}

// Auto-dismiss after 5s
watch(() => props.notifications.length, () => {
  if (props.notifications.length > 0) {
    const latest = props.notifications[0]
    setTimeout(() => emit('dismiss', latest.id), 5000)
  }
})
</script>

<template>
  <Teleport to="body">
    <div class="fixed top-4 right-4 z-[100] flex flex-col gap-2 max-w-sm w-full pointer-events-none">
      <TransitionGroup
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 translate-x-8 scale-95"
        enter-to-class="opacity-100 translate-x-0 scale-100"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 translate-x-0 scale-100"
        leave-to-class="opacity-0 translate-x-8 scale-95"
      >
        <div
          v-for="notif in notifications.slice(0, 3)"
          :key="notif.id"
          class="pointer-events-auto glass-card p-3 flex items-center gap-3 shadow-lg shadow-black/20 cursor-pointer group"
          @click="emit('dismiss', notif.id)"
        >
          <div v-if="notif.avatar" class="w-10 h-10 rounded-xl overflow-hidden flex-shrink-0 ring-2 ring-primary/20">
            <img :src="notif.avatar" class="w-full h-full object-cover" />
          </div>
          <div v-else class="w-10 h-10 rounded-xl bg-secondary/50 flex items-center justify-center flex-shrink-0">
            <component :is="iconMap[notif.type] || MessageCircle" :class="['w-5 h-5', colorMap[notif.type] || 'text-primary']" />
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-foreground truncate">{{ notif.message }}</p>
            <p class="text-xs text-muted-foreground">právě teď</p>
          </div>
          <button class="p-1 rounded-lg opacity-0 group-hover:opacity-100 hover:bg-secondary/50 transition-all flex-shrink-0" @click.stop="emit('dismiss', notif.id)">
            <X class="w-3.5 h-3.5 text-muted-foreground" />
          </button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>
