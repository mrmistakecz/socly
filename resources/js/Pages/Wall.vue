<script setup>
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import Header from '@/Components/Socly/Header.vue'
import BottomNav from '@/Components/Socly/BottomNav.vue'
import FeedScreen from '@/Components/Socly/Screens/FeedScreen.vue'
import DiscoverScreen from '@/Components/Socly/Screens/DiscoverScreen.vue'
import MessagesScreen from '@/Components/Socly/Screens/MessagesScreen.vue'
import ProfileScreen from '@/Components/Socly/Screens/ProfileScreen.vue'
import LiveScreen from '@/Components/Socly/Screens/LiveScreen.vue'

const activeTab = ref('home')
const showLive = ref(false)

const handleOpenLive = () => {
  showLive.value = true
}

const handleCloseLive = () => {
  showLive.value = false
}

const handleTabChange = (tab) => {
  activeTab.value = tab
}

const currentScreen = computed(() => {
  switch (activeTab.value) {
    case 'home':
      return FeedScreen
    case 'discover':
      return DiscoverScreen
    case 'messages':
      return MessagesScreen
    case 'profile':
      return ProfileScreen
    default:
      return FeedScreen
  }
})

const screenProps = computed(() => {
  if (activeTab.value === 'discover') {
    return { onOpenLive: handleOpenLive }
  }
  if (activeTab.value === 'profile') {
    return { onBack: () => handleTabChange('home') }
  }
  return {}
})
</script>

<template>
  <Head title="Hlavní zeď" />
  
  <div class="min-h-dvh bg-background">
    <!-- Mobile Container - Premium iPhone 15 Pro viewport -->
    <div class="max-w-[430px] mx-auto min-h-dvh bg-background relative overflow-hidden shadow-2xl shadow-primary/5">
      <!-- Live Stream Overlay -->
      <LiveScreen 
        v-if="showLive" 
        @close="handleCloseLive" 
      />

      <!-- Header - only show on feed and discover -->
      <Header 
        v-if="(activeTab === 'home' || activeTab === 'discover') && !showLive" 
      />

      <!-- Main Content -->
      <main 
        v-if="!showLive" 
        class="hide-scrollbar overflow-y-auto min-h-dvh overscroll-y-contain"
      >
        <component :is="currentScreen" v-bind="screenProps" />
      </main>

      <!-- Bottom Navigation -->
      <BottomNav 
        v-if="!showLive"
        :active-tab="activeTab" 
        @tab-change="handleTabChange" 
      />
    </div>
  </div>
</template>
