<script setup>
import { ref, computed } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { Home, Compass, Plus, MessageCircle, User, Settings, LogOut, Search, Shield } from 'lucide-vue-next'
import Header from '@/Components/Socly/Header.vue'
import BottomNav from '@/Components/Socly/BottomNav.vue'
import FlashMessage from '@/Components/Socly/FlashMessage.vue'

const props = defineProps({
  title: {
    type: String,
    default: 'SOCLY'
  },
  notifications: {
    type: Array,
    default: () => [],
  }
})

const emit = defineEmits(['tabChange', 'clear-notifications'])

const page = usePage()
const authUser = computed(() => page.props.auth?.user)
const unreadCount = computed(() => {
  const convs = page.props.conversations || []
  return convs.reduce((sum, c) => sum + (c.unread || 0), 0)
})

const activeTab = ref('home')
const showLive = ref(false)

const sidebarNav = [
  { id: 'home', icon: Home, label: 'Hlavní zeď' },
  { id: 'discover', icon: Compass, label: 'Objevit' },
  { id: 'messages', icon: MessageCircle, label: 'Zprávy' },
]

const handleTabChange = (tab) => {
  emit('tabChange', tab)
  if (tab !== 'add' && tab !== 'profile') {
    activeTab.value = tab
  }
}

const handleOpenLive = () => {
  showLive.value = true
}

const handleCloseLive = () => {
  showLive.value = false
}

const handleLogout = () => {
  router.post('/logout')
}
</script>

<template>
  <Head :title="title" />
  
  <div class="min-h-dvh bg-background">
    <!-- Desktop Sidebar Navigation -->
    <aside class="hidden lg:flex fixed left-0 top-0 bottom-0 w-[260px] glass-sidebar border-r border-border/20 flex-col z-40">
      <!-- Logo -->
      <div class="flex items-center px-6 h-16 border-b border-border/20">
        <h1 class="text-2xl font-black tracking-tight">
          <span class="text-gradient-premium">SOCLY</span><sup class="text-[0.4em] text-primary animate-pulse-spark align-super font-bold">;)</sup>
        </h1>
      </div>

      <!-- Nav Items -->
      <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto hide-scrollbar">
        <button
          v-for="item in sidebarNav"
          :key="item.id"
          @click="handleTabChange(item.id)"
          :class="[
            'w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 focus-ring group',
            activeTab === item.id
              ? 'nav-item-active text-primary font-semibold'
              : 'hover:bg-secondary/40 text-muted-foreground hover:text-foreground'
          ]"
        >
          <component
            :is="item.icon"
            :class="['w-5 h-5 transition-transform duration-200', activeTab === item.id ? 'scale-110' : 'group-hover:scale-105']"
            :stroke-width="activeTab === item.id ? 2.5 : 2"
          />
          <span class="text-[15px]">{{ item.label }}</span>
          <span
            v-if="item.id === 'messages' && unreadCount > 0"
            class="ml-auto min-w-[22px] h-[22px] px-1.5 rounded-full bg-gradient-to-r from-primary to-accent flex items-center justify-center"
          >
            <span class="text-[10px] font-bold text-white">{{ unreadCount }}</span>
          </span>
        </button>

        <div class="h-px bg-gradient-to-r from-transparent via-border/40 to-transparent my-3" />

        <button
          @click="handleTabChange('profile')"
          :class="[
            'w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 focus-ring group',
            activeTab === 'profile'
              ? 'nav-item-active text-primary font-semibold'
              : 'hover:bg-secondary/40 text-muted-foreground hover:text-foreground'
          ]"
        >
          <User class="w-5 h-5" :stroke-width="activeTab === 'profile' ? 2.5 : 2" />
          <span class="text-[15px]">Profil</span>
        </button>

        <!-- Create Post CTA -->
        <button
          @click="handleTabChange('add')"
          class="w-full flex items-center justify-center gap-2 px-4 py-3.5 mt-3 rounded-xl bg-gradient-to-r from-primary via-pink-500 to-accent text-white font-semibold text-[15px] btn-premium glow-primary transition-all hover:scale-[1.02] active:scale-[0.98] focus-ring"
        >
          <Plus class="w-5 h-5" stroke-width="2.5" />
          Nový příspěvek
        </button>
      </nav>

      <!-- Bottom: User + Settings + Logout -->
      <div class="px-3 pb-4 pt-2 border-t border-border/20 space-y-1">
        <!-- User Card -->
        <div v-if="authUser" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-secondary/20 mb-2">
          <img
            :src="authUser.avatar || '/images/default-avatar.svg'"
            class="w-9 h-9 rounded-lg object-cover ring-2 ring-primary/20"
          />
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium truncate">{{ authUser.name }}</p>
            <p class="text-xs text-muted-foreground truncate">@{{ authUser.username }}</p>
          </div>
        </div>

        <button
          v-if="authUser?.is_admin"
          @click="router.visit('/admin')"
          class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-destructive/10 hover:text-destructive transition-all text-destructive/70 focus-ring"
        >
          <Shield class="w-[18px] h-[18px]" />
          <span class="text-sm font-medium">Admin Panel</span>
        </button>
        <button
          @click="router.visit('/settings')"
          class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-secondary/40 transition-all text-muted-foreground hover:text-foreground focus-ring"
        >
          <Settings class="w-[18px] h-[18px]" />
          <span class="text-sm">Nastavení</span>
        </button>
        <button
          @click="handleLogout"
          class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-destructive/10 hover:text-destructive transition-all text-muted-foreground focus-ring"
        >
          <LogOut class="w-[18px] h-[18px]" />
          <span class="text-sm">Odhlásit se</span>
        </button>
      </div>
    </aside>

    <!-- Main Wrapper -->
    <div class="lg:pl-[260px]">
      <!-- Mobile Container -->
      <div class="max-w-[430px] mx-auto min-h-dvh bg-background relative overflow-hidden shadow-2xl shadow-primary/5 lg:max-w-none lg:shadow-none">

        <!-- Header - mobile only -->
        <Header
          v-if="!showLive"
          class="sticky top-0 z-30 lg:hidden"
          :notifications="notifications"
          @clear-notifications="emit('clear-notifications')"
        />

        <!-- Main Content Area -->
        <main
          v-if="!showLive"
          class="hide-scrollbar overflow-y-auto min-h-dvh overscroll-y-contain pb-24 lg:pb-8 page-enter-active"
        >
          <slot
            :activeTab="activeTab"
            :onOpenLive="handleOpenLive"
            :onTabChange="handleTabChange"
          />
        </main>

        <!-- Live Stream Overlay -->
        <slot
          v-if="showLive"
          name="live"
          :onClose="handleCloseLive"
        />

        <!-- Bottom Navigation - mobile -->
        <BottomNav
          v-if="!showLive"
          :active-tab="activeTab"
          @tab-change="handleTabChange"
          class="fixed bottom-0 left-0 right-0 z-40 lg:hidden"
        />
      </div>
    </div>

    <!-- Flash Messages -->
    <FlashMessage />
  </div>
</template>
