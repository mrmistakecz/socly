<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { Bell, Sparkles, Search, Crown, BadgeCheck } from 'lucide-vue-next'
import axios from 'axios'

const showSearch = ref(false)
const searchQuery = ref('')
const searchResults = ref([])

let searchTimeout = null
watch(searchQuery, (val) => {
  clearTimeout(searchTimeout)
  if (val.length < 2) {
    searchResults.value = []
    return
  }
  searchTimeout = setTimeout(async () => {
    try {
      const { data } = await axios.get('/api/search', { params: { q: val } })
      searchResults.value = data.users || []
    } catch (e) {
      searchResults.value = []
    }
  }, 300)
})

const goToProfile = (id) => {
  searchQuery.value = ''
  searchResults.value = []
  showSearch.value = false
  router.visit(`/profile/${id}`)
}
</script>

<template>
  <header class="sticky top-0 z-50 glass-heavy pt-safe lg:hidden">
    <div class="flex items-center justify-between px-4 py-3">
      <!-- Logo -->
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary via-pink-500 to-accent flex items-center justify-center glow-primary">
          <Sparkles class="w-4 h-4 text-white" />
        </div>
        <div>
          <h1 class="text-lg font-bold tracking-tight text-gradient-premium">SOCLY</h1>
        </div>
      </div>

      <!-- Search - Tablet -->
      <div class="hidden md:flex flex-1 max-w-md mx-6">
        <div class="relative w-full">
          <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Hledat tvurce, obsah..."
            class="w-full pl-10 pr-4 py-2.5 bg-secondary/50 border border-border/50 rounded-xl text-sm placeholder:text-muted-foreground focus:outline-none focus:border-primary/50 focus:ring-2 focus:ring-primary/20 transition-all"
          />
          <!-- Search Results Dropdown -->
          <div v-if="searchResults.length" class="absolute top-full left-0 right-0 mt-2 bg-card border border-border/50 rounded-xl shadow-xl overflow-hidden z-50">
            <button
              v-for="user in searchResults"
              :key="user.id"
              @click="goToProfile(user.id)"
              class="w-full flex items-center gap-3 px-4 py-3 hover:bg-secondary/50 transition-colors text-left"
            >
              <img :src="user.avatar || 'https://i.pravatar.cc/40'" class="w-9 h-9 rounded-xl object-cover" />
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-1">
                  <span class="text-sm font-medium truncate">{{ user.name }}</span>
                  <BadgeCheck v-if="user.verified" class="w-3.5 h-3.5 text-primary flex-shrink-0" />
                </div>
                <p class="text-xs text-muted-foreground truncate">@{{ user.username }}</p>
              </div>
            </button>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-2">
        <button 
          @click="showSearch = !showSearch"
          class="md:hidden p-2.5 rounded-xl bg-secondary/50 text-muted-foreground hover:text-foreground hover:bg-secondary transition-all touch-active"
        >
          <Search class="w-5 h-5" />
        </button>

        <button class="relative p-2.5 rounded-xl bg-secondary/50 text-muted-foreground hover:text-foreground hover:bg-secondary transition-all touch-active">
          <Bell class="w-5 h-5" />
          <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-primary rounded-full border-2 border-background animate-pulse" />
        </button>

        <div class="hidden sm:flex items-center gap-1.5 px-3 py-2 rounded-xl bg-gradient-to-r from-gold/20 to-amber-500/20 border border-gold/30">
          <Crown class="w-4 h-4 text-gold" />
          <span class="text-xs font-semibold text-gold">VIP</span>
        </div>
      </div>
    </div>

    <div v-if="showSearch" class="px-4 pb-3 md:hidden animate-slide-up">
      <div class="relative">
        <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Hledat tvurce, obsah..."
          autofocus
          class="w-full pl-10 pr-4 py-2.5 bg-secondary/50 border border-border/50 rounded-xl text-sm placeholder:text-muted-foreground focus:outline-none focus:border-primary/50 focus:ring-2 focus:ring-primary/20 transition-all"
        />
        <!-- Mobile Search Results -->
        <div v-if="searchResults.length" class="absolute top-full left-0 right-0 mt-2 bg-card border border-border/50 rounded-xl shadow-xl overflow-hidden z-50">
          <button
            v-for="user in searchResults"
            :key="user.id"
            @click="goToProfile(user.id)"
            class="w-full flex items-center gap-3 px-4 py-3 hover:bg-secondary/50 transition-colors text-left"
          >
            <img :src="user.avatar || 'https://i.pravatar.cc/40'" class="w-9 h-9 rounded-xl object-cover" />
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-1">
                <span class="text-sm font-medium truncate">{{ user.name }}</span>
                <BadgeCheck v-if="user.verified" class="w-3.5 h-3.5 text-primary flex-shrink-0" />
              </div>
              <p class="text-xs text-muted-foreground truncate">@{{ user.username }}</p>
            </div>
          </button>
        </div>
      </div>
    </div>
  </header>
</template>
