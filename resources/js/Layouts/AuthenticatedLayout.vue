<script setup>
import { ref, computed } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import Header from '@/Components/Socly/Header.vue'
import BottomNav from '@/Components/Socly/BottomNav.vue'
import FlashMessage from '@/Components/Socly/FlashMessage.vue'

const props = defineProps({
  title: {
    type: String,
    default: 'SOCLY'
  }
})

const emit = defineEmits(['tabChange'])

const page = usePage()
const authUser = computed(() => page.props.auth?.user)

const activeTab = ref('home')
const showLive = ref(false)

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

// Slots for content injection
const slots = defineSlots()
</script>

<template>
  <Head :title="title" />
  
  <div class="min-h-dvh bg-background">
    <!-- Mobile Container - Premium iPhone 15 Pro viewport -->
    <div class="max-w-[430px] mx-auto min-h-dvh bg-background relative overflow-hidden shadow-2xl shadow-primary/5 lg:max-w-none lg:shadow-none">
      
      <!-- Header - persistent across navigation -->
      <Header 
        v-if="!showLive" 
        class="sticky top-0 z-30"
      />

      <!-- Main Content Area -->
      <main 
        v-if="!showLive"
        class="hide-scrollbar overflow-y-auto min-h-dvh overscroll-y-contain pb-24"
      >
        <slot 
          :activeTab="activeTab" 
          :onOpenLive="handleOpenLive"
          :onTabChange="handleTabChange"
        />
      </main>

      <!-- Live Stream Overlay (full screen) -->
      <slot 
        v-if="showLive" 
        name="live"
        :onClose="handleCloseLive"
      />

      <!-- Bottom Navigation - persistent -->
      <BottomNav 
        v-if="!showLive"
        :active-tab="activeTab" 
        @tab-change="handleTabChange"
        class="fixed bottom-0 left-0 right-0 z-40 lg:hidden"
      />
      
      <!-- Desktop Sidebar Navigation -->
      <nav class="hidden lg:flex fixed left-0 top-0 bottom-0 w-64 bg-card/30 border-r border-border/30 flex-col p-4 z-40">
        <div class="flex items-center px-4 py-5 mb-4 border-b border-border/50">
          <h1 class="text-2xl font-black tracking-tight">
            <span class="text-gradient-premium">SOCLY</span><span class="inline-block animate-pulse-spark text-sm ml-0.5">😉</span>
          </h1>
        </div>
        
        <div class="space-y-2">
          <button 
            @click="handleTabChange('home')"
            :class="[
              'w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all',
              activeTab === 'home' ? 'bg-primary/10 text-primary' : 'hover:bg-secondary/50 text-muted-foreground'
            ]"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span class="font-medium">Hlavní zeď</span>
          </button>
          
          <button 
            @click="handleTabChange('discover')"
            :class="[
              'w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all',
              activeTab === 'discover' ? 'bg-primary/10 text-primary' : 'hover:bg-secondary/50 text-muted-foreground'
            ]"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <span class="font-medium">Objevit</span>
          </button>
          
          <button 
            @click="handleTabChange('messages')"
            :class="[
              'w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all',
              activeTab === 'messages' ? 'bg-primary/10 text-primary' : 'hover:bg-secondary/50 text-muted-foreground'
            ]"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            <span class="font-medium">Zprávy</span>
            <span class="ml-auto bg-destructive text-white text-xs px-2 py-0.5 rounded-full">3</span>
          </button>
          
          <button 
            @click="handleTabChange('profile')"
            :class="[
              'w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all',
              activeTab === 'profile' ? 'bg-primary/10 text-primary' : 'hover:bg-secondary/50 text-muted-foreground'
            ]"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span class="font-medium">Profil</span>
          </button>

          <button 
            @click="handleTabChange('add')"
            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl bg-gradient-to-r from-primary via-pink-500 to-accent text-white font-semibold btn-premium glow-primary transition-all hover:scale-[1.02]"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span class="font-medium">Nový příspěvek</span>
          </button>
        </div>
        
        <div class="mt-auto space-y-2">
          <button 
            @click="router.visit('/settings')"
            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-secondary/50 transition-all text-muted-foreground"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span class="font-medium">Nastavení</span>
          </button>
          <button 
            @click="handleLogout"
            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-destructive/10 hover:text-destructive transition-all text-muted-foreground"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            <span class="font-medium">Odhlásit se</span>
          </button>
        </div>
      </nav>
    </div>

    <!-- Flash Messages -->
    <FlashMessage />
  </div>
</template>
