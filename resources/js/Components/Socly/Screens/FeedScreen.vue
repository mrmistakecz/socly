<script setup>
import { ref, computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import { Play, Plus, Crown, Flame, Clock, ImageOff } from 'lucide-vue-next'
import FeedCard from './FeedCard.vue'

const props = defineProps({
  posts: { type: Array, default: () => [] },
  stories: { type: Array, default: () => [] },
  postUpdates: { type: Object, default: () => ({}) },
})

const page = usePage()
const authUser = computed(() => page.props.auth?.user)

const storiesWithOwn = computed(() => {
  const own = {
    id: 0,
    name: 'Vaše story',
    avatar: authUser.value?.avatar || '/images/default-avatar.svg',
    hasStory: false,
    isOwn: true,
  }
  return [own, ...props.stories]
})

const feedData = computed(() => props.posts)
const activeFilter = ref('latest')

const changeFilter = (filter) => {
  activeFilter.value = filter
  router.reload({ data: { sort: filter }, only: ['posts'], preserveScroll: true })
}
</script>

<template>
  <div class="min-h-dvh pb-32 lg:pb-8">
    <!-- Desktop Header -->
    <div class="hidden lg:block sticky top-0 z-30 bg-background/80 backdrop-blur-xl border-b border-border/50 px-6 py-4">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold">Hlavní zeď</h1>
          <p class="text-sm text-muted-foreground">Obsah od tvůrců, které sleduješ</p>
        </div>
        <div class="flex items-center gap-2">
          <button 
            @click="changeFilter('trending')"
            :class="['flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium transition-all', activeFilter === 'trending' ? 'bg-primary/10 text-primary' : 'bg-secondary/50 hover:bg-secondary text-muted-foreground']"
          >
            <Flame class="w-4 h-4" />
            Trendující
          </button>
          <button 
            @click="changeFilter('latest')"
            :class="['flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium transition-all', activeFilter === 'latest' ? 'bg-primary/10 text-primary' : 'bg-secondary/50 hover:bg-secondary text-muted-foreground']"
          >
            <Clock class="w-4 h-4" />
            Nejnovější
          </button>
        </div>
      </div>
    </div>

    <div class="pt-4 lg:pt-6 px-4 lg:px-6">
      <!-- Stories Row -->
      <div class="flex gap-3 overflow-x-auto hide-scrollbar py-2 -mx-4 px-4 lg:mx-0 lg:px-0 mb-6">
        <button 
          v-for="story in storiesWithOwn" 
          :key="story.id" 
          class="flex flex-col items-center gap-2 flex-shrink-0"
        >
          <div class="relative">
            <div :class="[
              'w-[72px] h-[72px] lg:w-[80px] lg:h-[80px] rounded-full p-[3px] transition-all',
              story.isOwn ? 'bg-secondary' : '',
              story.isLive ? 'bg-gradient-to-br from-destructive via-primary to-accent animate-pulse-glow' : '',
              story.hasStory && !story.isLive && !story.isOwn ? 'story-ring' : '',
              !story.hasStory && !story.isOwn ? 'bg-secondary/50' : ''
            ]">
              <div class="w-full h-full rounded-full overflow-hidden bg-background p-[2px]">
                <img
                  :src="story.avatar || '/images/default-avatar.svg'"
                  :alt="story.name"
                  class="w-full h-full rounded-full object-cover"
                />
              </div>
            </div>

            <div v-if="story.isOwn" class="absolute bottom-0 right-0 w-6 h-6 rounded-full bg-primary flex items-center justify-center border-2 border-background">
              <Plus class="w-3.5 h-3.5 text-white" stroke-width="3" />
            </div>

            <div v-if="story.isLive" class="absolute -bottom-1 left-1/2 -translate-x-1/2 flex items-center gap-1 px-2 py-0.5 bg-destructive rounded-full border-2 border-background">
              <Play class="w-2 h-2 text-white fill-white" />
              <span class="text-[9px] font-bold text-white uppercase">Zive</span>
            </div>

            <div v-if="story.isVIP && !story.isLive" class="absolute -bottom-1 left-1/2 -translate-x-1/2 flex items-center gap-0.5 px-1.5 py-0.5 bg-gradient-to-r from-gold to-amber-500 rounded-full border-2 border-background">
              <Crown class="w-2.5 h-2.5 text-black" />
            </div>

            <div v-if="story.isNew" class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-accent flex items-center justify-center border-2 border-background">
              <span class="text-[8px] font-bold text-white">N</span>
            </div>
          </div>
          
          <span :class="[
            'text-xs font-medium truncate w-[72px] lg:w-[80px] text-center',
            story.isOwn ? 'text-muted-foreground' : ''
          ]">
            {{ story.name }}
          </span>
        </button>
      </div>

      <div class="h-px bg-gradient-to-r from-transparent via-border to-transparent mb-6" />

      <!-- Feed - Responsive Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-3 gap-5 lg:gap-6 max-w-7xl">
        <FeedCard 
          v-for="post in feedData" 
          :key="post.id" 
          v-bind="post"
          :realtime-update="postUpdates.postId === post.id ? postUpdates : null"
        />
      </div>

      <!-- Empty State -->
      <div v-if="feedData.length === 0" class="flex flex-col items-center justify-center py-20 text-center">
        <div class="w-20 h-20 rounded-3xl bg-secondary/50 flex items-center justify-center mb-5">
          <ImageOff class="w-10 h-10 text-muted-foreground" />
        </div>
        <h3 class="text-lg font-semibold mb-2">Zatím žádné příspěvky</h3>
        <p class="text-sm text-muted-foreground max-w-xs">Začněte sledovat tvůrce, abyste viděli jejich obsah ve svém feedu</p>
      </div>
    </div>
  </div>
</template>
