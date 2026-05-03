<script setup>
import { ref, watch, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { Bell, Search, Crown, BadgeCheck, Heart, MessageCircle, UserPlus, X } from 'lucide-vue-next'
import axios from 'axios'

const showSearch = ref(false)
const showNotifications = ref(false)
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

const page = usePage()
const user = computed(() => page.props.auth?.user)

const props = defineProps({
  notifications: { type: Array, default: () => [] },
})

const emit = defineEmits(['clear-notifications'])

const iconMap = {
  like: Heart,
  comment: MessageCircle,
  follow: UserPlus,
}

const colorMap = {
  like: 'text-red-400',
  comment: 'text-primary',
  follow: 'text-accent',
}

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
      <div class="flex items-center">
        <h1 class="text-2xl font-black tracking-tight">
          <span class="text-gradient-premium">SOCLY</span><sup class="text-[0.4em] text-primary animate-pulse-spark align-super font-bold">;)</sup>
        </h1>
      </div>

      <!-- Search - Tablet -->
      <div class="hidden md:flex flex-1 max-w-md mx-6">
        <div class="relative w-full">
          <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Hledat tvůrce, obsah..."
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
              <img :src="user.avatar || '/images/default-avatar.svg'" class="w-9 h-9 rounded-xl object-cover" />
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

        <div class="relative">
          <button 
            @click="showNotifications = !showNotifications"
            class="relative p-2.5 rounded-xl bg-secondary/50 text-muted-foreground hover:text-foreground hover:bg-secondary transition-all touch-active"
          >
            <Bell class="w-5 h-5" />
            <span 
              v-if="notifications.length > 0" 
              class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-primary rounded-full border-2 border-background animate-pulse" 
            />
          </button>

          <!-- Notifications Dropdown -->
          <div v-if="showNotifications" class="absolute top-full right-0 mt-2 w-80 max-h-[400px] overflow-y-auto bg-card border border-border/50 rounded-2xl shadow-xl z-50 hide-scrollbar">
            <div class="p-3 border-b border-border/50 flex items-center justify-between sticky top-0 bg-card/90 backdrop-blur-sm z-10">
              <h3 class="font-bold text-sm">Oznámení</h3>
              <button v-if="notifications.length" @click="emit('clear-notifications'); showNotifications = false" class="text-xs text-primary hover:text-primary/80">Vymazat vše</button>
            </div>
            
            <div v-if="notifications.length === 0" class="p-8 text-center text-muted-foreground text-sm">
              Žádná nová oznámení
            </div>
            
            <div v-else class="flex flex-col">
              <div
                v-for="notif in notifications"
                :key="notif.id"
                class="p-3 flex items-start gap-3 hover:bg-secondary/50 transition-colors border-b border-border/10 last:border-0"
              >
                <div v-if="notif.avatar" class="w-10 h-10 rounded-xl overflow-hidden flex-shrink-0 ring-2 ring-primary/10">
                  <img :src="notif.avatar" class="w-full h-full object-cover" />
                </div>
                <div v-else class="w-10 h-10 rounded-xl bg-secondary/50 flex items-center justify-center flex-shrink-0">
                  <component :is="iconMap[notif.type] || MessageCircle" :class="['w-5 h-5', colorMap[notif.type] || 'text-primary']" />
                </div>
                <div class="flex-1 min-w-0 pt-0.5">
                  <p class="text-sm text-foreground leading-tight">{{ notif.message }}</p>
                  <p class="text-xs text-muted-foreground mt-1">právě teď</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="user?.is_vip" class="hidden sm:flex items-center gap-1.5 px-3 py-2 rounded-xl bg-gradient-to-r from-gold/20 to-amber-500/20 border border-gold/30">
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
          placeholder="Hledat tvůrce, obsah..."
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
            <img :src="user.avatar || '/images/default-avatar.svg'" class="w-9 h-9 rounded-xl object-cover" />
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
