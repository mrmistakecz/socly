<script setup>
import { ref, computed } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import { MessageCircle, Lock, Grid3X3, PlayCircle, Bookmark, Settings, ChevronLeft, BadgeCheck, Share2, Heart, Crown, Sparkles, Edit3 } from 'lucide-vue-next'

const props = defineProps({
  profileUser: Object,
  posts: { type: Array, default: () => [] },
  isFollowing: Boolean,
  isSubscribed: Boolean,
  isOwn: Boolean,
})

const page = usePage()
const activeTab = ref('posts')
const following = ref(props.isFollowing)
const subscribed = ref(props.isSubscribed)

const tabs = [
  { id: 'posts', label: 'Příspěvky', icon: Grid3X3 },
  { id: 'videos', label: 'Videa', icon: PlayCircle },
  { id: 'saved', label: 'Uložené', icon: Bookmark },
]

const formatNumber = (num) => {
  if (num >= 1000000) return `${(num / 1000000).toFixed(1)}M`
  if (num >= 1000) return `${(num / 1000).toFixed(1)}K`
  return num?.toString() || '0'
}

const handleFollow = () => {
  following.value = !following.value
  router.post(`/users/${props.profileUser.id}/follow`, {}, { preserveScroll: true, preserveState: true })
}

const handleMessage = () => {
  router.visit('/?tab=messages&chat=' + props.profileUser.id)
}

const savedPosts = ref([])
const savedLoaded = ref(false)

const filteredPosts = computed(() => {
  if (activeTab.value === 'videos') return props.posts.filter(p => p.isVideo)
  if (activeTab.value === 'saved') return savedPosts.value
  return props.posts
})

const handleTabClick = async (tabId) => {
  activeTab.value = tabId
  if (tabId === 'saved' && !savedLoaded.value && props.isOwn) {
    try {
      const { data } = await axios.get('/api/bookmarks')
      savedPosts.value = data.posts || []
      savedLoaded.value = true
    } catch { savedPosts.value = [] }
  }
}
</script>

<template>
  <Head :title="profileUser.name" />
  
  <div class="min-h-dvh bg-background pb-32 lg:pb-8">
    <!-- Cover Photo -->
    <div class="relative h-48 lg:h-64">
      <img
        :src="profileUser.cover"
        alt="Cover"
        class="w-full h-full object-cover"
      />
      <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-transparent to-background" />
      
      <!-- Top Actions -->
      <div class="absolute top-0 left-0 right-0 pt-safe">
        <div class="flex items-center justify-between p-4">
          <button 
            @click="router.visit('/')"
            class="w-10 h-10 rounded-xl glass flex items-center justify-center"
          >
            <ChevronLeft class="w-5 h-5 text-white" />
          </button>
          <div class="flex items-center gap-2">
            <button class="w-10 h-10 rounded-xl glass flex items-center justify-center">
              <Share2 class="w-5 h-5 text-white" />
            </button>
            <button 
              v-if="isOwn"
              @click="router.visit('/settings')"
              class="w-10 h-10 rounded-xl glass flex items-center justify-center"
            >
              <Settings class="w-5 h-5 text-white" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Profile Info -->
    <div class="relative px-4 lg:px-6">
      <!-- Avatar & Stats Row -->
      <div class="flex flex-col lg:flex-row lg:items-end lg:gap-6 -mt-16 lg:-mt-20 mb-5">
        <!-- Avatar -->
        <div class="relative self-start lg:self-auto">
          <div class="w-28 h-28 lg:w-36 lg:h-36 rounded-2xl bg-gradient-to-br from-primary via-pink-500 to-accent p-[3px] glow-primary-intense">
            <div class="w-full h-full rounded-2xl overflow-hidden bg-background p-[3px]">
              <img
                :src="profileUser.avatar || '/images/default-avatar.svg'"
                :alt="profileUser.name"
                class="w-full h-full rounded-2xl object-cover"
              />
            </div>
          </div>
          
          <div v-if="profileUser.isVIP" class="absolute -bottom-2 left-1/2 -translate-x-1/2 flex items-center gap-1 px-2.5 py-1 bg-gradient-to-r from-gold to-amber-500 rounded-full border-2 border-background">
            <Crown class="w-3 h-3 text-black" />
            <span class="text-[9px] font-bold text-black uppercase">TOP</span>
          </div>
        </div>
        
        <!-- Stats - Desktop -->
        <div class="hidden lg:flex gap-8 mb-3">
          <div class="text-center">
            <p class="text-2xl font-bold">{{ profileUser.posts_count }}</p>
            <p class="text-sm text-muted-foreground">Příspěvků</p>
          </div>
          <div class="text-center">
            <p class="text-2xl font-bold">{{ formatNumber(profileUser.followers) }}</p>
            <p class="text-sm text-muted-foreground">Odběratelů</p>
          </div>
          <div class="text-center">
            <p class="text-2xl font-bold">{{ formatNumber(profileUser.likes) }}</p>
            <p class="text-sm text-muted-foreground">Líbí se</p>
          </div>
        </div>

        <!-- Desktop Actions -->
        <div class="hidden lg:flex gap-3 ml-auto mb-3">
          <template v-if="isOwn">
            <button
              @click="router.visit('/settings')"
              class="px-8 py-3 rounded-xl font-semibold bg-secondary border border-border transition-all hover:bg-secondary/80"
            >
              Upravit profil
            </button>
          </template>
          <template v-else>
            <button
              @click="handleFollow"
              :class="[
                'px-8 py-3 rounded-xl font-semibold transition-all btn-premium',
                following 
                  ? 'bg-secondary border border-border' 
                  : 'bg-gradient-to-r from-primary to-pink-500 text-white glow-primary'
              ]"
            >
              {{ following ? 'Sledujete' : 'Sledovat' }}
            </button>
            <button @click="handleMessage" class="px-4 py-3 rounded-xl bg-secondary hover:bg-secondary/80 transition-colors">
              <MessageCircle class="w-5 h-5" />
            </button>
          </template>
        </div>
      </div>

      <!-- Stats - Mobile -->
      <div class="flex justify-around py-4 border-y border-border/50 mb-4 lg:hidden">
        <div class="text-center">
          <p class="text-lg font-bold">{{ profileUser.posts_count }}</p>
          <p class="text-xs text-muted-foreground">Příspěvků</p>
        </div>
        <div class="text-center">
          <p class="text-lg font-bold">{{ formatNumber(profileUser.followers) }}</p>
          <p class="text-xs text-muted-foreground">Odběratelů</p>
        </div>
        <div class="text-center">
          <p class="text-lg font-bold">{{ formatNumber(profileUser.likes) }}</p>
          <p class="text-xs text-muted-foreground">Líbí se</p>
        </div>
      </div>

      <!-- Name & Bio -->
      <div class="mb-5">
        <div class="flex items-center gap-2 mb-1">
          <h1 class="text-xl lg:text-2xl font-bold">{{ profileUser.name }}</h1>
          <BadgeCheck v-if="profileUser.verified" class="w-5 h-5 lg:w-6 lg:h-6 text-primary fill-primary/20" />
        </div>
        <p class="text-sm text-muted-foreground mb-3">{{ profileUser.username }}</p>
        <p v-if="profileUser.bio" class="text-sm whitespace-pre-line leading-relaxed text-foreground/85">{{ profileUser.bio }}</p>
      </div>

      <!-- Mobile Action Buttons -->
      <div class="flex gap-3 mb-6 lg:hidden">
        <template v-if="isOwn">
          <button
            @click="router.visit('/settings')"
            class="flex-1 py-3.5 rounded-xl font-bold bg-secondary border border-border transition-all"
          >
            <span class="flex items-center justify-center gap-2">
              <Edit3 class="w-4 h-4" />
              Upravit profil
            </span>
          </button>
        </template>
        <template v-else>
          <button
            @click="handleFollow"
            :class="[
              'flex-1 py-3.5 rounded-xl font-bold transition-all',
              following 
                ? 'bg-secondary border border-border' 
                : 'bg-gradient-to-r from-primary to-pink-500 text-white glow-primary btn-premium'
            ]"
          >
            <span class="flex items-center justify-center gap-2">
              <Sparkles v-if="!following" class="w-4 h-4" />
              {{ following ? 'Sledujete' : 'Sledovat' }}
            </span>
          </button>
          <button @click="handleMessage" class="w-14 h-14 rounded-xl bg-secondary flex items-center justify-center">
            <MessageCircle class="w-5 h-5" />
          </button>
        </template>
      </div>

      <!-- Tabs -->
      <div class="flex border-b border-border">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="handleTabClick(tab.id)"
          :class="[
            'flex-1 py-4 flex items-center justify-center gap-2 transition-all relative',
            activeTab === tab.id ? 'text-primary' : 'text-muted-foreground hover:text-foreground'
          ]"
        >
          <component :is="tab.icon" class="w-5 h-5" />
          <span class="text-sm font-medium hidden sm:inline">{{ tab.label }}</span>
          <span v-if="activeTab === tab.id" class="absolute bottom-0 left-4 right-4 h-0.5 bg-primary rounded-full" />
        </button>
      </div>
    </div>

    <!-- Content Grid -->
    <div class="grid grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-0.5 lg:gap-1 mt-0.5 lg:mt-1 lg:px-6">
      <button 
        v-for="item in filteredPosts" 
        :key="item.id" 
        class="relative aspect-square bg-secondary/20 overflow-hidden group"
      >
        <img
          :src="item.image"
          :alt="`Post ${item.id}`"
          :class="[
            'w-full h-full object-cover transition-all duration-300 group-hover:scale-105',
            item.locked && !subscribed && !isOwn ? 'blur-xl scale-110' : ''
          ]"
        />
        
        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
          <div class="flex items-center gap-1 text-white">
            <Heart class="w-5 h-5 fill-white" />
            <span class="font-bold">{{ item.likes }}K</span>
          </div>
        </div>
        
        <div v-if="item.isVideo" class="absolute top-2 right-2 w-6 h-6 rounded-full glass flex items-center justify-center">
          <PlayCircle class="w-3.5 h-3.5 text-white" />
        </div>
        
        <div v-if="item.locked && !subscribed && !isOwn" class="absolute inset-0 flex items-center justify-center bg-black/30">
          <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl glass flex items-center justify-center">
            <Lock class="w-5 h-5 lg:w-6 lg:h-6 text-primary" />
          </div>
        </div>
      </button>
    </div>

    <!-- Empty state -->
    <div v-if="filteredPosts.length === 0" class="flex flex-col items-center justify-center py-20 text-center">
      <div class="w-20 h-20 rounded-2xl bg-secondary/50 flex items-center justify-center mb-4">
        <Grid3X3 class="w-10 h-10 text-muted-foreground" />
      </div>
      <p class="text-lg font-semibold mb-1">Zatím žádné příspěvky</p>
      <p class="text-sm text-muted-foreground">Příspěvky se zobrazí zde</p>
    </div>
  </div>
</template>
