<script setup>
import { ref } from 'vue'
import { MessageCircle, Lock, Grid3X3, PlayCircle, Bookmark, Settings, ChevronLeft, BadgeCheck, Share2, Heart, Crown, Sparkles } from 'lucide-vue-next'

const props = defineProps({
  onBack: Function
})

const activeTab = ref('posts')
const isSubscribed = ref(false)

const profileData = {
  name: 'Karolina Majerova',
  username: '@karolina.m',
  avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400&h=400&fit=crop&crop=face',
  cover: 'https://images.unsplash.com/photo-1557682250-33bd709cbe85?w=800&h=400&fit=crop',
  bio: 'Tvurkyne exkluzivniho obsahu\nPraha | Modelka & Influencerka\nNovy obsah kazdy den',
  followers: 125400,
  likes: 2847000,
  posts: 342,
  subscriptionPrice: 400,
  verified: true,
  isVIP: true,
}

const gridItems = [
  { id: 1, image: 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=400&h=400&fit=crop', locked: false, isVideo: false, likes: 2.4 },
  { id: 2, image: 'https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?w=400&h=400&fit=crop', locked: true, isVideo: false, likes: 5.1 },
  { id: 3, image: 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=400&h=400&fit=crop', locked: false, isVideo: true, likes: 8.7 },
  { id: 4, image: 'https://images.unsplash.com/photo-1488426862026-3ee34a7d66df?w=400&h=400&fit=crop', locked: true, isVideo: false, likes: 3.2 },
  { id: 5, image: 'https://images.unsplash.com/photo-1502823403499-6ccfcf4fb453?w=400&h=400&fit=crop', locked: false, isVideo: false, likes: 1.9 },
  { id: 6, image: 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=400&h=400&fit=crop', locked: true, isVideo: true, likes: 12.3 },
  { id: 7, image: 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=400&h=400&fit=crop', locked: false, isVideo: false, likes: 4.6 },
  { id: 8, image: 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=400&h=400&fit=crop', locked: true, isVideo: false, likes: 2.8 },
  { id: 9, image: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&h=400&fit=crop', locked: true, isVideo: false, likes: 6.1 },
]

const tabs = [
  { id: 'posts', label: 'Prispevky', icon: Grid3X3 },
  { id: 'videos', label: 'Videa', icon: PlayCircle },
  { id: 'saved', label: 'Ulozene', icon: Bookmark },
]

const formatNumber = (num) => {
  if (num >= 1000000) return `${(num / 1000000).toFixed(1)}M`
  if (num >= 1000) return `${(num / 1000).toFixed(1)}K`
  return num.toString()
}
</script>

<template>
  <div class="min-h-dvh pb-32 lg:pb-8">
    <!-- Cover Photo -->
    <div class="relative h-48 lg:h-64">
      <img
        :src="profileData.cover"
        alt="Cover"
        class="w-full h-full object-cover"
      />
      <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-transparent to-background" />
      
      <!-- Top Actions - Mobile -->
      <div class="absolute top-0 left-0 right-0 pt-safe lg:hidden">
        <div class="flex items-center justify-between p-4">
          <button 
            @click="$emit('back')"
            class="w-10 h-10 rounded-xl glass flex items-center justify-center"
          >
            <ChevronLeft class="w-5 h-5 text-white" />
          </button>
          <div class="flex items-center gap-2">
            <button class="w-10 h-10 rounded-xl glass flex items-center justify-center">
              <Share2 class="w-5 h-5 text-white" />
            </button>
            <button class="w-10 h-10 rounded-xl glass flex items-center justify-center">
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
                :src="profileData.avatar"
                :alt="profileData.name"
                class="w-full h-full rounded-2xl object-cover"
              />
            </div>
          </div>
          
          <div v-if="profileData.isVIP" class="absolute -bottom-2 left-1/2 -translate-x-1/2 flex items-center gap-1 px-2.5 py-1 bg-gradient-to-r from-gold to-amber-500 rounded-full border-2 border-background">
            <Crown class="w-3 h-3 text-black" />
            <span class="text-[9px] font-bold text-black uppercase">TOP</span>
          </div>
        </div>
        
        <!-- Stats - Desktop -->
        <div class="hidden lg:flex gap-8 mb-3">
          <div class="text-center">
            <p class="text-2xl font-bold">{{ profileData.posts }}</p>
            <p class="text-sm text-muted-foreground">Prispevku</p>
          </div>
          <div class="text-center">
            <p class="text-2xl font-bold">{{ formatNumber(profileData.followers) }}</p>
            <p class="text-sm text-muted-foreground">Odberatelu</p>
          </div>
          <div class="text-center">
            <p class="text-2xl font-bold">{{ formatNumber(profileData.likes) }}</p>
            <p class="text-sm text-muted-foreground">Libi se</p>
          </div>
        </div>

        <!-- Desktop Actions -->
        <div class="hidden lg:flex gap-3 ml-auto mb-3">
          <button
            @click="isSubscribed = !isSubscribed"
            :class="[
              'px-8 py-3 rounded-xl font-semibold transition-all btn-premium',
              isSubscribed 
                ? 'bg-secondary border border-border' 
                : 'bg-gradient-to-r from-primary to-pink-500 text-white glow-primary'
            ]"
          >
            {{ isSubscribed ? 'Odebirate' : `Odebirat - ${profileData.subscriptionPrice} Kc/mesic` }}
          </button>
          <button class="px-4 py-3 rounded-xl bg-secondary hover:bg-secondary/80 transition-colors">
            <MessageCircle class="w-5 h-5" />
          </button>
        </div>
      </div>

      <!-- Stats - Mobile -->
      <div class="flex justify-around py-4 border-y border-border/50 mb-4 lg:hidden">
        <div class="text-center">
          <p class="text-lg font-bold">{{ profileData.posts }}</p>
          <p class="text-xs text-muted-foreground">Prispevku</p>
        </div>
        <div class="text-center">
          <p class="text-lg font-bold">{{ formatNumber(profileData.followers) }}</p>
          <p class="text-xs text-muted-foreground">Odberatelu</p>
        </div>
        <div class="text-center">
          <p class="text-lg font-bold">{{ formatNumber(profileData.likes) }}</p>
          <p class="text-xs text-muted-foreground">Libi se</p>
        </div>
      </div>

      <!-- Name & Bio -->
      <div class="mb-5">
        <div class="flex items-center gap-2 mb-1">
          <h1 class="text-xl lg:text-2xl font-bold">{{ profileData.name }}</h1>
          <BadgeCheck v-if="profileData.verified" class="w-5 h-5 lg:w-6 lg:h-6 text-primary fill-primary/20" />
        </div>
        <p class="text-sm text-muted-foreground mb-3">{{ profileData.username }}</p>
        <p class="text-sm whitespace-pre-line leading-relaxed text-foreground/85">{{ profileData.bio }}</p>
      </div>

      <!-- Mobile Action Buttons -->
      <div class="flex gap-3 mb-6 lg:hidden">
        <button
          @click="isSubscribed = !isSubscribed"
          :class="[
            'flex-1 py-3.5 rounded-xl font-bold transition-all',
            isSubscribed 
              ? 'bg-secondary border border-border' 
              : 'bg-gradient-to-r from-primary to-pink-500 text-white glow-primary btn-premium'
          ]"
        >
          <span class="flex items-center justify-center gap-2">
            <Sparkles v-if="!isSubscribed" class="w-4 h-4" />
            {{ isSubscribed ? 'Odebirate' : `${profileData.subscriptionPrice} Kc/mesic` }}
          </span>
        </button>
        <button class="w-14 h-14 rounded-xl bg-secondary flex items-center justify-center">
          <MessageCircle class="w-5 h-5" />
        </button>
      </div>

      <!-- Tabs -->
      <div class="flex border-b border-border">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="activeTab = tab.id"
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
        v-for="item in gridItems" 
        :key="item.id" 
        class="relative aspect-square bg-secondary/20 overflow-hidden group"
      >
        <img
          :src="item.image"
          :alt="`Post ${item.id}`"
          :class="[
            'w-full h-full object-cover transition-all duration-300 group-hover:scale-105',
            item.locked && !isSubscribed ? 'blur-xl scale-110' : ''
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
        
        <div v-if="item.locked && !isSubscribed" class="absolute inset-0 flex items-center justify-center bg-black/30">
          <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl glass flex items-center justify-center">
            <Lock class="w-5 h-5 lg:w-6 lg:h-6 text-primary" />
          </div>
        </div>
      </button>
    </div>
  </div>
</template>
