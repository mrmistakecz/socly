<script setup>
import { ref } from 'vue'
import { Heart, MessageCircle, Share2, Bookmark, Lock, Sparkles, Eye, MoreHorizontal } from 'lucide-vue-next'

const props = defineProps({
  id: Number,
  creator: Object,
  image: String,
  likes: Number,
  comments: Number,
  isLocked: {
    type: Boolean,
    default: false
  },
  price: {
    type: Number,
    default: 150
  },
  caption: String,
  timeAgo: String
})

const isLiked = ref(false)
const isBookmarked = ref(false)
const currentLikes = ref(props.likes)
const showHeart = ref(false)

const handleLike = () => {
  isLiked.value = !isLiked.value
  currentLikes.value = isLiked.value ? currentLikes.value + 1 : currentLikes.value - 1
}

const handleDoubleTap = () => {
  if (!isLiked.value) {
    isLiked.value = true
    currentLikes.value++
  }
  showHeart.value = true
  setTimeout(() => showHeart.value = false, 800)
}
</script>

<template>
  <article class="bg-card/50 border border-border/50 rounded-2xl overflow-hidden card-interactive">
    <!-- Creator Header -->
    <div class="flex items-center gap-3 p-4">
      <button class="relative group">
        <div class="w-11 h-11 rounded-xl overflow-hidden ring-2 ring-primary/20 group-hover:ring-primary/40 transition-all">
          <img
            :src="creator.avatar"
            :alt="creator.name"
            class="w-full h-full object-cover"
          />
        </div>
        <div v-if="creator.verified" class="absolute -bottom-0.5 -right-0.5 w-4.5 h-4.5 bg-primary rounded-full flex items-center justify-center border-2 border-card">
          <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
          </svg>
        </div>
      </button>
      
      <div class="flex-1 min-w-0">
        <p class="font-semibold text-sm truncate">{{ creator.name }}</p>
        <p class="text-xs text-muted-foreground">{{ timeAgo }}</p>
      </div>
      
      <button class="p-2 rounded-lg hover:bg-secondary/50 transition-colors">
        <MoreHorizontal class="w-5 h-5 text-muted-foreground" />
      </button>
    </div>

    <!-- Image -->
    <div 
      class="relative aspect-[4/5] bg-secondary/30 cursor-pointer overflow-hidden"
      @dblclick="handleDoubleTap"
    >
      <img
        :src="image"
        alt="Post"
        :class="[
          'w-full h-full object-cover transition-all duration-300',
          isLocked ? 'blur-xl scale-105' : ''
        ]"
      />
      
      <div v-if="showHeart" class="absolute inset-0 flex items-center justify-center pointer-events-none">
        <Heart class="w-20 h-20 text-white fill-white animate-scale-in opacity-90" />
      </div>
      
      <div v-if="isLocked" class="absolute inset-0 flex flex-col items-center justify-center bg-black/40">
        <div class="w-16 h-16 rounded-2xl glass flex items-center justify-center mb-4">
          <Lock class="w-7 h-7 text-primary" />
        </div>
        <p class="text-sm text-white/90 font-medium mb-1">Exkluzivni obsah</p>
        <p class="text-xs text-white/60 mb-5">Pro odberatele</p>
        <button class="px-6 py-3 rounded-xl bg-gradient-to-r from-primary to-pink-500 text-white font-semibold text-sm btn-premium glow-primary">
          <span class="flex items-center gap-2">
            <Sparkles class="w-4 h-4" />
            Odemknout za {{ price }} Kc
          </span>
        </button>
      </div>

      <div v-if="!isLocked" class="absolute top-3 right-3 flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg glass text-white/90">
        <Eye class="w-3.5 h-3.5" />
        <span class="text-xs font-medium">12.4K</span>
      </div>
    </div>

    <!-- Actions -->
    <div class="p-4">
      <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-1">
          <button 
            @click="handleLike"
            :class="[
              'flex items-center gap-2 px-3 py-2 rounded-lg transition-all',
              isLiked ? 'bg-destructive/10 text-destructive' : 'hover:bg-secondary/50 text-muted-foreground'
            ]"
          >
            <Heart :class="['w-5 h-5 transition-transform', isLiked ? 'fill-current scale-110' : '']" />
            <span class="text-sm font-medium">{{ currentLikes.toLocaleString() }}</span>
          </button>
          
          <button class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-secondary/50 transition-colors text-muted-foreground">
            <MessageCircle class="w-5 h-5" />
            <span class="text-sm font-medium">{{ comments }}</span>
          </button>
          
          <button class="p-2 rounded-lg hover:bg-secondary/50 transition-colors text-muted-foreground">
            <Share2 class="w-5 h-5" />
          </button>
        </div>
        
        <button 
          @click="isBookmarked = !isBookmarked"
          :class="[
            'p-2 rounded-lg transition-all',
            isBookmarked ? 'bg-primary/10 text-primary' : 'hover:bg-secondary/50 text-muted-foreground'
          ]"
        >
          <Bookmark :class="['w-5 h-5', isBookmarked ? 'fill-current' : '']" />
        </button>
      </div>

      <p v-if="caption" class="text-sm leading-relaxed">
        <span class="font-semibold mr-1.5 hover:text-primary cursor-pointer">{{ creator.name }}</span>
        <span class="text-muted-foreground">{{ caption }}</span>
      </p>
    </div>
  </article>
</template>
