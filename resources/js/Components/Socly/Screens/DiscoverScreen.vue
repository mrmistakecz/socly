<script setup>
import { Search, TrendingUp, Flame, Star, Crown, Lock, PlayCircle, BadgeCheck, Sparkles, ChevronRight, Users } from 'lucide-vue-next'

defineProps({
  topCreators: { type: Array, default: () => [] },
  onOpenLive: { type: Function, default: null },
})

const emit = defineEmits(['openLive'])

const categories = [
  { id: 'all', label: 'Vse', icon: Sparkles, active: true },
  { id: 'trending', label: 'Trendy', icon: TrendingUp },
  { id: 'popular', label: 'Popularni', icon: Flame },
  { id: 'new', label: 'Nove', icon: Star },
  { id: 'vip', label: 'VIP', icon: Crown },
]

const liveCreators = [
  {
    id: 1,
    name: 'Tereza B.',
    avatar: 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=200&h=200&fit=crop&crop=face',
    viewers: 1247,
    isVIP: true,
  },
  {
    id: 2,
    name: 'Eliska V.',
    avatar: 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=200&h=200&fit=crop&crop=face',
    viewers: 892,
  },
  {
    id: 3,
    name: 'Adela K.',
    avatar: 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=200&h=200&fit=crop&crop=face',
    viewers: 567,
    isVIP: true,
  },
]

const topCreators = [
  {
    id: 1,
    name: 'Karolina M.',
    avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&h=200&fit=crop&crop=face',
    followers: '125.4K',
    verified: true,
    badge: 1,
  },
  {
    id: 2,
    name: 'Tereza B.',
    avatar: 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=200&h=200&fit=crop&crop=face',
    followers: '89.2K',
    isLive: true,
    verified: true,
    badge: 2,
  },
  {
    id: 3,
    name: 'Nikola S.',
    avatar: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=200&h=200&fit=crop&crop=face',
    followers: '67.8K',
    badge: 3,
  },
  {
    id: 4,
    name: 'Eliska V.',
    avatar: 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=200&h=200&fit=crop&crop=face',
    followers: '156K',
    isLive: true,
    verified: true,
    badge: 4,
  },
]

const discoverGrid = [
  { id: 1, image: 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=400&h=600&fit=crop', locked: false, isVideo: false, span: 'col-span-1 row-span-2', views: '24.5K' },
  { id: 2, image: 'https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?w=400&h=400&fit=crop', locked: true, span: 'col-span-1 row-span-1', views: '18.2K' },
  { id: 3, image: 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=400&h=400&fit=crop', isVideo: true, span: 'col-span-1 row-span-1', views: '45.1K' },
  { id: 4, image: 'https://images.unsplash.com/photo-1488426862026-3ee34a7d66df?w=400&h=600&fit=crop', locked: true, span: 'col-span-1 row-span-2', views: '12.8K' },
  { id: 5, image: 'https://images.unsplash.com/photo-1502823403499-6ccfcf4fb453?w=400&h=400&fit=crop', span: 'col-span-1 row-span-1', views: '8.9K' },
  { id: 6, image: 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=400&h=400&fit=crop', isVideo: true, span: 'col-span-1 row-span-1', views: '67.3K' },
]
</script>

<template>
  <div class="min-h-dvh pb-32 lg:pb-8">
    <!-- Desktop Header -->
    <div class="hidden lg:block sticky top-0 z-30 bg-background/80 backdrop-blur-xl border-b border-border/50 px-6 py-4">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold">Objevovat</h1>
          <p class="text-sm text-muted-foreground">Najdete nove tvurce a obsah</p>
        </div>
        <div class="flex-1 max-w-md mx-8">
          <div class="relative">
            <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
            <input
              type="text"
              placeholder="Hledat tvurce..."
              class="w-full pl-11 pr-4 py-2.5 bg-secondary/50 border border-border/50 rounded-xl text-sm placeholder:text-muted-foreground focus:outline-none focus:border-primary/50 focus:ring-2 focus:ring-primary/20 transition-all"
            />
          </div>
        </div>
      </div>
    </div>

    <div class="pt-4 lg:pt-6 px-4 lg:px-6">
      <!-- Mobile Search -->
      <div class="lg:hidden mb-5">
        <div class="relative">
          <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
          <input
            type="text"
            placeholder="Hledat tvurce..."
            class="w-full pl-11 pr-4 py-3 bg-secondary/50 border border-border/50 rounded-xl text-sm placeholder:text-muted-foreground focus:outline-none focus:border-primary/50 transition-all"
          />
        </div>
      </div>

      <!-- Categories -->
      <div class="flex gap-2 overflow-x-auto hide-scrollbar mb-6 -mx-4 px-4 lg:mx-0 lg:px-0">
        <button
          v-for="cat in categories"
          :key="cat.id"
          :class="[
            'flex items-center gap-2 px-4 py-2.5 rounded-xl font-medium text-sm whitespace-nowrap transition-all',
            cat.active 
              ? 'bg-gradient-to-r from-primary to-pink-500 text-white glow-primary' 
              : 'bg-secondary/50 text-muted-foreground hover:text-foreground hover:bg-secondary'
          ]"
        >
          <component :is="cat.icon" class="w-4 h-4" />
          {{ cat.label }}
        </button>
      </div>

      <!-- Live Now Section -->
      <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-destructive animate-pulse" />
            <h2 class="text-lg font-bold">Prave ted LIVE</h2>
          </div>
          <button class="flex items-center gap-1 text-sm text-primary hover:text-primary/80 transition-colors">
            Zobrazit vse
            <ChevronRight class="w-4 h-4" />
          </button>
        </div>
        
        <div class="flex gap-4 overflow-x-auto hide-scrollbar -mx-4 px-4 lg:mx-0 lg:px-0">
          <button
            v-for="creator in liveCreators"
            :key="creator.id"
            @click="$emit('openLive')"
            class="relative flex-shrink-0 w-32 lg:w-40 group"
          >
            <div class="relative aspect-[3/4] rounded-2xl overflow-hidden">
              <img
                :src="creator.avatar"
                :alt="creator.name"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent" />
              
              <div class="absolute top-2 left-2 flex items-center gap-1 px-2 py-1 rounded-full bg-destructive glow-live">
                <span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse" />
                <span class="text-[10px] font-bold text-white uppercase">LIVE</span>
              </div>
              
              <div v-if="creator.isVIP" class="absolute top-2 right-2">
                <Crown class="w-4 h-4 text-gold" />
              </div>
              
              <div class="absolute bottom-0 left-0 right-0 p-3">
                <p class="font-semibold text-white text-sm truncate mb-1">{{ creator.name }}</p>
                <div class="flex items-center gap-1 text-white/80">
                  <Users class="w-3 h-3" />
                  <span class="text-xs">{{ creator.viewers.toLocaleString() }}</span>
                </div>
              </div>
            </div>
          </button>
        </div>
      </div>

      <!-- Top Creators -->
      <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-bold">Top tvurci</h2>
          <button class="flex items-center gap-1 text-sm text-primary hover:text-primary/80 transition-colors">
            Zobrazit vse
            <ChevronRight class="w-4 h-4" />
          </button>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
          <div
            v-for="creator in topCreators"
            :key="creator.id"
            class="flex items-center gap-3 p-3 rounded-xl bg-card/50 border border-border/50 hover:bg-secondary/30 transition-all cursor-pointer group"
          >
            <div :class="[
              'w-8 h-8 rounded-lg flex items-center justify-center font-bold text-sm',
              creator.badge === 1 ? 'bg-gradient-to-br from-gold to-amber-600 text-black' : 
                creator.badge === 2 ? 'bg-gradient-to-br from-gray-300 to-gray-400 text-black' :
                creator.badge === 3 ? 'bg-gradient-to-br from-amber-700 to-amber-800 text-white' :
                'bg-secondary text-muted-foreground'
            ]">
              {{ creator.badge }}
            </div>
            
            <div class="relative">
              <div :class="[
                'w-12 h-12 rounded-xl p-[2px] transition-all',
                creator.isLive 
                  ? 'bg-gradient-to-br from-destructive via-primary to-accent animate-pulse-glow' 
                  : 'bg-gradient-to-br from-primary/30 to-accent/30'
              ]">
                <div class="w-full h-full rounded-xl overflow-hidden bg-background p-[1px]">
                  <img
                    :src="creator.avatar"
                    :alt="creator.name"
                    class="w-full h-full rounded-xl object-cover"
                  />
                </div>
              </div>
              <span 
                v-if="creator.isLive" 
                class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full bg-destructive border-2 border-background animate-pulse" 
              />
            </div>
            
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-1.5 mb-0.5">
                <p class="font-semibold truncate">{{ creator.name }}</p>
                <BadgeCheck 
                  v-if="creator.verified" 
                  class="w-4 h-4 text-primary fill-primary/20 flex-shrink-0" 
                />
              </div>
              <p class="text-sm text-muted-foreground">{{ creator.followers }} odberatelu</p>
            </div>
            
            <div class="px-4 py-2 rounded-xl bg-primary/10 text-primary text-sm font-medium group-hover:bg-primary group-hover:text-white transition-all">
              {{ creator.isLive ? 'Sledovat' : 'Zobrazit' }}
            </div>
          </div>
        </div>
      </div>

      <!-- Discover Grid -->
      <div>
        <h2 class="text-lg font-bold mb-4">Objevte</h2>
        
        <div class="grid grid-cols-2 lg:grid-cols-3 auto-rows-[140px] lg:auto-rows-[180px] gap-2">
          <button 
            v-for="item in discoverGrid" 
            :key="item.id" 
            :class="['relative rounded-2xl overflow-hidden bg-secondary/20 group', item.span]"
          >
            <img
              :src="item.image"
              :alt="`Discover ${item.id}`"
              :class="[
                'w-full h-full object-cover transition-all duration-300 group-hover:scale-105',
                item.locked ? 'blur-xl scale-110' : ''
              ]"
            />
            
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity" />
            
            <div v-if="item.isVideo" class="absolute top-2 right-2 w-8 h-8 rounded-xl glass flex items-center justify-center">
              <PlayCircle class="w-4 h-4 text-white" />
            </div>
            
            <div class="absolute bottom-2 left-2 flex items-center gap-1 px-2 py-1 rounded-lg glass opacity-0 group-hover:opacity-100 transition-opacity">
              <Users class="w-3 h-3 text-white/80" />
              <span class="text-xs font-medium text-white/90">{{ item.views }}</span>
            </div>
            
            <div v-if="item.locked" class="absolute inset-0 flex items-center justify-center bg-black/30">
              <div class="w-12 h-12 rounded-xl glass flex items-center justify-center glow-primary">
                <Lock class="w-6 h-6 text-primary" />
              </div>
            </div>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
