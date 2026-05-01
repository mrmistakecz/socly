<script setup>
import { ref, computed } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
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
  <AuthenticatedLayout title="Hlavní zeď">
    <template #default="{ activeTab: layoutTab, onOpenLive, onTabChange }">
      <component 
        :is="currentScreen" 
        v-bind="screenProps" 
      />
    </template>
    
    <template #live="{ onClose }">
      <LiveScreen @close="handleCloseLive" />
    </template>
  </AuthenticatedLayout>
</template>
