<script setup>
import { Search, CheckCheck, Image as ImageIcon, Video, Sparkles, Crown, BadgeCheck, ChevronRight } from 'lucide-vue-next'

const conversations = [
  {
    id: 1,
    name: 'Karolína M.',
    avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&h=200&fit=crop&crop=face',
    lastMessage: 'Děkuji za podporu!',
    time: 'Teď',
    unread: 2,
    isOnline: true,
    verified: true,
    isVIP: true,
    hasMedia: false,
  },
  {
    id: 2,
    name: 'Tereza B.',
    avatar: 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=200&h=200&fit=crop&crop=face',
    lastMessage: 'Nový obsah bude zítra!',
    time: 'před 5 min',
    unread: 0,
    isOnline: true,
    verified: true,
    isVIP: false,
    hasMedia: true,
  },
  {
    id: 3,
    name: 'Nikola S.',
    avatar: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=200&h=200&fit=crop&crop=face',
    lastMessage: 'Ráda, že se ti líbí',
    time: 'před 1 hod',
    unread: 0,
    isOnline: false,
    verified: false,
    isVIP: false,
    hasMedia: false,
  },
  {
    id: 4,
    name: 'Eliška V.',
    avatar: 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=200&h=200&fit=crop&crop=face',
    lastMessage: 'Už brzy bude živé vysílání!',
    time: 'před 3 hod',
    unread: 1,
    isOnline: true,
    verified: true,
    isVIP: true,
    hasMedia: false,
    hasVideo: true,
  },
  {
    id: 5,
    name: 'Adéla K.',
    avatar: 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=200&h=200&fit=crop&crop=face',
    lastMessage: 'Díky za tip!',
    time: 'včera',
    unread: 0,
    isOnline: false,
    verified: true,
    isVIP: false,
    hasMedia: true,
  },
]

const onlineConversations = conversations.filter(c => c.isOnline)
</script>

<template>
  <div class="pt-20 pb-32 px-4">
    <!-- Header -->
    <div class="mb-6">
      <div class="flex items-center justify-between mb-1">
        <h1 class="text-2xl font-bold text-foreground">Zprávy</h1>
        <div class="flex items-center gap-1 px-2.5 py-1 rounded-full bg-primary/10">
          <span class="text-xs font-semibold text-primary">3 nové</span>
        </div>
      </div>
      <p class="text-sm text-muted-foreground">Vaše konverzace s tvůrci</p>
    </div>

    <!-- Search -->
    <div class="relative mb-6">
      <div class="flex items-center glass-card rounded-2xl px-4 py-3.5 focus-within:ring-2 focus-within:ring-primary/50 transition-all">
        <Search class="w-5 h-5 text-muted-foreground mr-3" />
        <input
          type="text"
          placeholder="Hledat v konverzacích..."
          class="flex-1 bg-transparent text-foreground placeholder-muted-foreground text-sm outline-none"
        />
      </div>
    </div>

    <!-- VIP Banner -->
    <div class="relative overflow-hidden rounded-2xl mb-6 touch-feedback group">
      <div class="absolute inset-0 bg-gradient-to-r from-primary/20 via-purple-500/20 to-accent/20" />
      <div class="absolute inset-0 glass-card" />
      <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-primary/40 to-accent/40 rounded-full blur-3xl animate-pulse" />
      
      <div class="relative p-5">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <div class="flex items-center gap-2 mb-2">
              <Crown class="w-5 h-5 text-gold" />
              <h3 class="font-bold text-foreground">VIP Zprávy</h3>
            </div>
            <p class="text-sm text-muted-foreground mb-4 max-w-[200px]">
              Odemkněte prioritní přístup k odpovědím tvůrců
            </p>
            <button class="flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-primary to-accent rounded-xl text-sm font-bold text-white neon-glow hover:scale-105 transition-transform">
              <Sparkles class="w-4 h-4" />
              Upgradovat
            </button>
          </div>
          
          <div class="flex -space-x-3">
            <div 
              v-for="(conv, i) in conversations.slice(0, 3)" 
              :key="conv.id"
              class="w-12 h-12 rounded-full border-2 border-background overflow-hidden"
              :style="{ zIndex: 3 - i }"
            >
              <img
                :src="conv.avatar"
                alt=""
                class="w-full h-full object-cover"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Online Now -->
    <div class="mb-6">
      <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
          <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse" />
          <span class="text-sm font-medium text-foreground">Online</span>
        </div>
        <button class="flex items-center gap-1 text-xs text-primary touch-feedback">
          Všichni
          <ChevronRight class="w-3 h-3" />
        </button>
      </div>
      
      <div class="flex gap-3 overflow-x-auto hide-scrollbar -mx-4 px-4">
        <button 
          v-for="conv in onlineConversations" 
          :key="conv.id"
          class="flex flex-col items-center gap-2 flex-shrink-0 touch-feedback"
        >
          <div class="relative">
            <div :class="[
              'w-16 h-16 rounded-full p-[2px]',
              conv.unread > 0 
                ? 'bg-gradient-to-br from-primary via-purple-500 to-accent animate-pulse-glow' 
                : 'bg-gradient-to-br from-primary/30 to-accent/30'
            ]">
              <div class="w-full h-full rounded-full overflow-hidden bg-background p-[2px]">
                <div class="w-full h-full rounded-full overflow-hidden">
                  <img
                    :src="conv.avatar"
                    :alt="conv.name"
                    class="w-full h-full object-cover"
                  />
                </div>
              </div>
            </div>
            <div class="absolute bottom-0.5 right-0.5 w-4 h-4 bg-green-500 rounded-full border-2 border-background" />
            <div v-if="conv.unread > 0" class="absolute -top-1 -right-1 min-w-[20px] h-5 px-1.5 rounded-full bg-gradient-to-br from-primary to-accent flex items-center justify-center">
              <span class="text-[10px] font-bold text-white">{{ conv.unread }}</span>
            </div>
          </div>
          <span class="text-xs text-muted-foreground truncate w-16 text-center">
            {{ conv.name.split(' ')[0] }}
          </span>
        </button>
      </div>
    </div>

    <!-- Conversations List -->
    <div>
      <h3 class="text-sm font-medium text-muted-foreground mb-3">Všechny zprávy</h3>
      <div class="flex flex-col gap-1">
        <button
          v-for="conv in conversations"
          :key="conv.id"
          class="flex items-center gap-3 p-3 rounded-xl hover:bg-secondary/30 transition-all touch-feedback text-left group"
        >
          <div class="relative flex-shrink-0">
            <div :class="[
              'w-14 h-14 rounded-xl p-[2px]',
              conv.unread > 0 
                ? 'bg-gradient-to-br from-primary to-accent' 
                : 'bg-secondary'
            ]">
              <div class="w-full h-full rounded-xl overflow-hidden bg-background p-[1px]">
                <div class="w-full h-full rounded-xl overflow-hidden">
                  <img
                    :src="conv.avatar"
                    :alt="conv.name"
                    class="w-full h-full object-cover"
                  />
                </div>
              </div>
            </div>
            <div v-if="conv.isOnline" class="absolute bottom-0 right-0 w-4 h-4 bg-green-500 rounded-full border-2 border-background" />
          </div>

          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-1.5 mb-1">
              <Crown v-if="conv.isVIP" class="w-3.5 h-3.5 text-gold flex-shrink-0" />
              <span :class="[
                'font-semibold truncate',
                conv.unread > 0 ? 'text-foreground' : 'text-foreground/90'
              ]">
                {{ conv.name }}
              </span>
              <BadgeCheck v-if="conv.verified" class="w-4 h-4 text-primary fill-primary/20 flex-shrink-0" />
            </div>
            <div class="flex items-center gap-1.5">
              <ImageIcon v-if="conv.hasMedia" class="w-3.5 h-3.5 text-muted-foreground flex-shrink-0" />
              <Video v-if="conv.hasVideo" class="w-3.5 h-3.5 text-muted-foreground flex-shrink-0" />
              <p :class="[
                'text-sm truncate',
                conv.unread > 0 ? 'text-foreground font-medium' : 'text-muted-foreground'
              ]">
                {{ conv.lastMessage }}
              </p>
            </div>
          </div>

          <div class="flex flex-col items-end gap-1.5 flex-shrink-0">
            <span :class="[
              'text-xs',
              conv.unread > 0 ? 'text-primary font-medium' : 'text-muted-foreground'
            ]">
              {{ conv.time }}
            </span>
            <div v-if="conv.unread > 0" class="min-w-[20px] h-5 px-1.5 rounded-full bg-gradient-to-r from-primary to-accent flex items-center justify-center">
              <span class="text-[10px] font-bold text-white">{{ conv.unread }}</span>
            </div>
            <CheckCheck v-else class="w-4 h-4 text-primary" />
          </div>
        </button>
      </div>
    </div>
  </div>
</template>
