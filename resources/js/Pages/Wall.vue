<script setup>
import { ref, computed, watch } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FeedScreen from '@/Components/Socly/Screens/FeedScreen.vue'
import DiscoverScreen from '@/Components/Socly/Screens/DiscoverScreen.vue'
import MessagesScreen from '@/Components/Socly/Screens/MessagesScreen.vue'
import LiveScreen from '@/Components/Socly/Screens/LiveScreen.vue'
import CreatePostModal from '@/Components/Socly/CreatePostModal.vue'
import RealtimeToast from '@/Components/Socly/RealtimeToast.vue'
import { useRealtime } from '@/composables/useRealtime'

const props = defineProps({
  posts: { type: Array, default: () => [] },
  stories: { type: Array, default: () => [] },
  topCreators: { type: Array, default: () => [] },
  trendingPosts: { type: Array, default: () => [] },
  conversations: { type: Array, default: () => [] },
})

const page = usePage()
const urlParams = new URLSearchParams(window.location.search)
const initialTab = urlParams.get('tab') || 'home'
const initialChatId = urlParams.get('chat')
const activeTab = ref(initialTab)
const showLive = ref(false)
const showCreatePost = ref(false)
const pendingChatUserId = ref(initialChatId ? parseInt(initialChatId) : null)

const { notifications, incomingMessage, postUpdates, dismissNotification } = useRealtime()

// When a new message arrives and we're on the messages tab, soft-reload
watch(incomingMessage, (msg) => {
  if (msg) {
    router.reload({ only: ['conversations'], preserveScroll: true })
  }
})

const handleOpenLive = () => {
  showLive.value = true
}

const handleCloseLive = () => {
  showLive.value = false
}

const handleTabChange = (tab) => {
  if (tab === 'add') {
    showCreatePost.value = true
    return
  }
  if (tab === 'profile') {
    router.visit('/profile')
    return
  }
  activeTab.value = tab
}

const newLocalPosts = ref([])

const handlePostCreated = (post) => {
  showCreatePost.value = false
  newLocalPosts.value.unshift(post)
  if (activeTab.value !== 'home') activeTab.value = 'home'
}

const currentScreen = computed(() => {
  switch (activeTab.value) {
    case 'home':
      return FeedScreen
    case 'discover':
      return DiscoverScreen
    case 'messages':
      return MessagesScreen
    default:
      return FeedScreen
  }
})

const screenProps = computed(() => {
  if (activeTab.value === 'home') {
    return { posts: [...newLocalPosts.value, ...props.posts], stories: props.stories, postUpdates: postUpdates.value }
  }
  if (activeTab.value === 'discover') {
    return { onOpenLive: handleOpenLive, topCreators: props.topCreators, trendingPosts: props.trendingPosts }
  }
  if (activeTab.value === 'messages') {
    return { conversations: props.conversations, pendingChatUserId: pendingChatUserId.value, incomingMessage: incomingMessage.value }
  }
  return {}
})
</script>

<template>
  <AuthenticatedLayout title="Hlavní zeď" :notifications="notifications" @tab-change="handleTabChange" @clear-notifications="clearNotifications">
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

  <!-- Create Post Modal -->
  <CreatePostModal 
    v-if="showCreatePost" 
    @close="showCreatePost = false"
    @created="handlePostCreated"
  />

  <!-- Real-time Notification Toasts -->
  <RealtimeToast :notifications="notifications" @dismiss="dismissNotification" />
</template>
