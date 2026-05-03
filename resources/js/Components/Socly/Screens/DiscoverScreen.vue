<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { Search, TrendingUp, Flame, Star, Crown, Lock, PlayCircle, BadgeCheck, Sparkles, ChevronRight, Users } from 'lucide-vue-next'
import axios from 'axios'

const props = defineProps({
  topCreators: { type: Array, default: () => [] },
  trendingPosts: { type: Array, default: () => [] },
})

const emit = defineEmits(['openLive'])

const activeCategory = ref('all')
const searchQuery = ref('')
const searchResults = ref([])

const categories = [
  { id: 'all', label: 'Vše', icon: Sparkles },
  { id: 'trending', label: 'Trendy', icon: TrendingUp },
  { id: 'popular', label: 'Populární', icon: Flame },
  { id: 'new', label: 'Nové', icon: Star },
  { id: 'vip', label: 'VIP', icon: Crown },
]

let searchTimeout = null
watch(searchQuery, (val) => {
  clearTimeout(searchTimeout)
  if (val.length < 2) { searchResults.value = []; return }
  searchTimeout = setTimeout(async () => {
    try {
      const { data } = await axios.get('/api/search', { params: { q: val } })
      searchResults.value = data.users || []
    } catch { searchResults.value = [] }
  }, 300)
})

const goToProfile = (id) => {
  searchQuery.value = ''
  searchResults.value = []
  router.visit(`/profile/${id}`)
}
</script>

<template>
  <div class="min-h-dvh pb-32 lg:pb-8">
    <!-- Desktop Header -->
    <div class="hidden lg:block sticky top-0 z-30 bg-background/80 backdrop-blur-xl border-b border-border/50 px-6 py-4">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold">Objevovat</h1>
          <p class="text-sm text-muted-foreground">Najděte nové tvůrce a obsah</p>
        </div>
        <div class="flex-1 max-w-md mx-8">
          <div class="relative">
            <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Hledat tvůrce..."
              class="w-full pl-11 pr-4 py-2.5 bg-secondary/50 border border-border/50 rounded-xl text-sm placeholder:text-muted-foreground focus:outline-none focus:border-primary/50 focus:ring-2 focus:ring-primary/20 transition-all"
            />
            <div v-if="searchResults.length" class="absolute top-full left-0 right-0 mt-2 bg-card border border-border/50 rounded-xl shadow-xl overflow-hidden z-50">
              <button v-for="user in searchResults" :key="user.id" @click="goToProfile(user.id)" class="w-full flex items-center gap-3 px-4 py-3 hover:bg-secondary/50 transition-colors text-left">
                <img :src="user.avatar || '/images/default-avatar.svg'" class="w-9 h-9 rounded-xl object-cover" />
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-1"><span class="text-sm font-medium truncate">{{ user.name }}</span><BadgeCheck v-if="user.verified" class="w-3.5 h-3.5 text-primary flex-shrink-0" /></div>
                  <p class="text-xs text-muted-foreground truncate">@{{ user.username }}</p>
                </div>
              </button>
            </div>
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
            v-model="searchQuery"
            type="text"
            placeholder="Hledat tvůrce..."
            class="w-full pl-11 pr-4 py-3 bg-secondary/50 border border-border/50 rounded-xl text-sm placeholder:text-muted-foreground focus:outline-none focus:border-primary/50 transition-all"
          />
          <div v-if="searchResults.length" class="absolute top-full left-0 right-0 mt-2 bg-card border border-border/50 rounded-xl shadow-xl overflow-hidden z-50">
            <button v-for="user in searchResults" :key="user.id" @click="goToProfile(user.id)" class="w-full flex items-center gap-3 px-4 py-3 hover:bg-secondary/50 transition-colors text-left">
              <img :src="user.avatar || '/images/default-avatar.svg'" class="w-9 h-9 rounded-xl object-cover" />
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-1"><span class="text-sm font-medium truncate">{{ user.name }}</span><BadgeCheck v-if="user.verified" class="w-3.5 h-3.5 text-primary flex-shrink-0" /></div>
                <p class="text-xs text-muted-foreground truncate">@{{ user.username }}</p>
              </div>
            </button>
          </div>
        </div>
      </div>

      <!-- Categories -->
      <div class="flex gap-2 overflow-x-auto hide-scrollbar mb-6 -mx-4 px-4 lg:mx-0 lg:px-0">
        <button
          v-for="cat in categories"
          :key="cat.id"
          @click="activeCategory = cat.id"
          :class="[
            'flex items-center gap-2 px-4 py-2.5 rounded-xl font-medium text-sm whitespace-nowrap transition-all',
            activeCategory === cat.id 
              ? 'bg-gradient-to-r from-primary to-pink-500 text-white glow-primary' 
              : 'bg-secondary/50 text-muted-foreground hover:text-foreground hover:bg-secondary'
          ]"
        >
          <component :is="cat.icon" class="w-4 h-4" />
          {{ cat.label }}
        </button>
      </div>

      <!-- Top Creators from Backend -->

      <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-bold">Top tvůrci</h2>
          <button class="flex items-center gap-1 text-sm text-primary hover:text-primary/80 transition-colors">
            Zobrazit vše
            <ChevronRight class="w-4 h-4" />
          </button>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
          <div
            v-for="creator in topCreators"
            :key="creator.id"
            @click="goToProfile(creator.id)"
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
              <p class="text-sm text-muted-foreground">{{ creator.followers }} odběratelů</p>
            </div>
            
            <div class="px-4 py-2 rounded-xl bg-primary/10 text-primary text-sm font-medium group-hover:bg-primary group-hover:text-white transition-all">
              {{ creator.isLive ? 'Sledovat' : 'Zobrazit' }}
            </div>
          </div>
        </div>
      </div>

      <!-- Trending Posts Grid -->
      <div v-if="trendingPosts.length">
        <h2 class="text-lg font-bold mb-4">Objevte</h2>
        
        <div class="grid grid-cols-2 lg:grid-cols-3 auto-rows-[140px] lg:auto-rows-[180px] gap-2">
          <button 
            v-for="(item, idx) in trendingPosts" 
            :key="item.id" 
            :class="['relative rounded-2xl overflow-hidden bg-secondary/20 group', idx % 3 === 0 ? 'col-span-1 row-span-2' : 'col-span-1 row-span-1']"
            @click="goToProfile(item.creator?.id)"
          >
            <img
              :src="item.image"
              :alt="item.caption || 'Post'"
              :class="[
                'w-full h-full object-cover transition-all duration-300 group-hover:scale-105',
                item.isLocked ? 'blur-xl scale-110' : ''
              ]"
            />
            
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity" />
            
            <div v-if="item.isVideo" class="absolute top-2 right-2 w-8 h-8 rounded-xl glass flex items-center justify-center">
              <PlayCircle class="w-4 h-4 text-white" />
            </div>
            
            <div class="absolute bottom-2 left-2 flex items-center gap-1 px-2 py-1 rounded-lg glass opacity-0 group-hover:opacity-100 transition-opacity">
              <Users class="w-3 h-3 text-white/80" />
              <span class="text-xs font-medium text-white/90">{{ item.likes }}</span>
            </div>
            
            <div v-if="item.isLocked" class="absolute inset-0 flex items-center justify-center bg-black/30">
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
