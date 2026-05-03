<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { Home, Compass, Plus, MessageCircle, User } from 'lucide-vue-next'

const props = defineProps({
  activeTab: {
    type: String,
    default: 'home'
  }
})

const emit = defineEmits(['tabChange'])

const page = usePage()
const unreadCount = computed(() => {
  const convs = page.props.conversations || []
  return convs.reduce((sum, c) => sum + (c.unread || 0), 0)
})

const navItems = [
  { id: 'home', icon: Home, label: 'Domů' },
  { id: 'discover', icon: Compass, label: 'Objevit' },
  { id: 'add', icon: Plus, label: '' },
  { id: 'messages', icon: MessageCircle, label: 'Zprávy' },
  { id: 'profile', icon: User, label: 'Profil' },
]

const handleTabChange = (tabId) => {
  emit('tabChange', tabId)
}
</script>

<template>
  <nav class="fixed bottom-0 left-0 right-0 z-50 pb-safe lg:hidden">
    <div class="mx-3 mb-3">
      <div class="glass-nav rounded-2xl border border-border/20 shadow-lg shadow-black/20">
        <div class="flex items-center justify-around py-2 px-2">
          <template v-for="item in navItems" :key="item.id">
            <button
              v-if="item.id === 'add'"
              @click="handleTabChange(item.id)"
              class="relative -mt-7"
            >
              <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary via-pink-500 to-accent flex items-center justify-center glow-primary-intense btn-premium">
                <Plus class="w-7 h-7 text-white" stroke-width="2.5" />
              </div>
            </button>

            <button
              v-else
              @click="handleTabChange(item.id)"
              :class="[
                'relative flex flex-col items-center gap-1 py-2 px-4 rounded-xl transition-all',
                activeTab === item.id ? 'text-primary' : 'text-muted-foreground hover:text-foreground'
              ]"
            >
              <div class="relative">
                <component 
                  :is="item.icon" 
                  class="w-6 h-6" 
                  :stroke-width="activeTab === item.id ? 2.5 : 2" 
                />
                <span 
                  v-if="item.id === 'messages' && unreadCount > 0" 
                  class="absolute -top-1.5 -right-2 min-w-[18px] h-[18px] px-1 rounded-full bg-gradient-to-r from-primary to-accent flex items-center justify-center animate-pulse"
                >
                  <span class="text-[10px] font-bold text-white">{{ unreadCount }}</span>
                </span>
              </div>
              <span class="text-[10px] font-medium">{{ item.label }}</span>
              <span 
                v-if="activeTab === item.id" 
                class="absolute bottom-0.5 w-1 h-1 rounded-full bg-primary" 
              />
            </button>
          </template>
        </div>
      </div>
    </div>
  </nav>
</template>
