<script setup>
import { ref } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { Users, Image, MessageCircle, TrendingUp, Shield, Crown, BadgeCheck, Trash2, ChevronLeft, Star, UserCheck } from 'lucide-vue-next'

const props = defineProps({
  stats: Object,
  users: { type: Array, default: () => [] },
  posts: { type: Array, default: () => [] },
})

const page = usePage()
const activeTab = ref('users')

const toggleFlag = (userId, flag, currentValue) => {
  router.put(`/admin/users/${userId}`, { [flag]: !currentValue }, {
    preserveScroll: true,
    preserveState: true,
  })
}

const deleteUser = (userId, name) => {
  if (!confirm(`Opravdu chcete smazat uživatele ${name}? Toto je nevratné.`)) return
  router.delete(`/admin/users/${userId}`, { preserveScroll: true })
}

const deletePost = (postId) => {
  if (!confirm('Opravdu chcete smazat tento příspěvek?')) return
  router.delete(`/admin/posts/${postId}`, { preserveScroll: true })
}
</script>

<template>
  <Head title="Admin Dashboard" />
  
  <div class="min-h-dvh bg-background">
    <!-- Top Bar -->
    <div class="sticky top-0 z-50 bg-background/80 backdrop-blur-xl border-b border-border/50">
      <div class="max-w-7xl mx-auto px-4 lg:px-8 py-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
          <button @click="router.visit('/')" class="p-2 rounded-xl hover:bg-secondary/50 transition-colors">
            <ChevronLeft class="w-5 h-5" />
          </button>
          <div>
            <h1 class="text-xl font-black tracking-tight">
              <span class="text-gradient-premium">SOCLY</span>
              <span class="text-sm font-semibold text-destructive ml-2">ADMIN</span>
            </h1>
          </div>
        </div>
        <div class="flex items-center gap-2 text-sm text-muted-foreground">
          <Shield class="w-4 h-4 text-destructive" />
          {{ page.props.auth.user.name }}
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-6">
      <!-- Stats Grid -->
      <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-8">
        <div class="p-4 rounded-2xl bg-card/50 border border-border/50">
          <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
              <Users class="w-5 h-5 text-primary" />
            </div>
          </div>
          <p class="text-2xl font-bold">{{ stats.totalUsers }}</p>
          <p class="text-xs text-muted-foreground">Uživatelů</p>
        </div>
        <div class="p-4 rounded-2xl bg-card/50 border border-border/50">
          <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-pink-500/10 flex items-center justify-center">
              <Image class="w-5 h-5 text-pink-500" />
            </div>
          </div>
          <p class="text-2xl font-bold">{{ stats.totalPosts }}</p>
          <p class="text-xs text-muted-foreground">Příspěvků</p>
        </div>
        <div class="p-4 rounded-2xl bg-card/50 border border-border/50">
          <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center">
              <MessageCircle class="w-5 h-5 text-blue-500" />
            </div>
          </div>
          <p class="text-2xl font-bold">{{ stats.totalMessages }}</p>
          <p class="text-xs text-muted-foreground">Zpráv</p>
        </div>
        <div class="p-4 rounded-2xl bg-card/50 border border-border/50">
          <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-green-500/10 flex items-center justify-center">
              <TrendingUp class="w-5 h-5 text-green-500" />
            </div>
          </div>
          <p class="text-2xl font-bold">{{ stats.newUsersToday }}</p>
          <p class="text-xs text-muted-foreground">Noví dnes</p>
        </div>
        <div class="p-4 rounded-2xl bg-card/50 border border-border/50">
          <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center">
              <Star class="w-5 h-5 text-amber-500" />
            </div>
          </div>
          <p class="text-2xl font-bold">{{ stats.newPostsToday }}</p>
          <p class="text-xs text-muted-foreground">Posty dnes</p>
        </div>
      </div>

      <!-- Tab Navigation -->
      <div class="flex gap-2 mb-6">
        <button 
          @click="activeTab = 'users'"
          :class="['px-5 py-2.5 rounded-xl text-sm font-medium transition-all', activeTab === 'users' ? 'bg-primary text-white' : 'bg-secondary/50 text-muted-foreground hover:text-foreground']"
        >
          <span class="flex items-center gap-2"><Users class="w-4 h-4" /> Uživatelé</span>
        </button>
        <button 
          @click="activeTab = 'posts'"
          :class="['px-5 py-2.5 rounded-xl text-sm font-medium transition-all', activeTab === 'posts' ? 'bg-primary text-white' : 'bg-secondary/50 text-muted-foreground hover:text-foreground']"
        >
          <span class="flex items-center gap-2"><Image class="w-4 h-4" /> Příspěvky</span>
        </button>
      </div>

      <!-- Users Tab -->
      <div v-if="activeTab === 'users'" class="space-y-2">
        <div 
          v-for="user in users" 
          :key="user.id"
          class="flex items-center gap-4 p-4 rounded-2xl bg-card/50 border border-border/50 hover:bg-secondary/20 transition-all"
        >
          <img :src="user.avatar || '/images/default-avatar.svg'" class="w-12 h-12 rounded-xl object-cover" />
          
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-0.5">
              <span class="font-semibold text-sm">{{ user.name }}</span>
              <span class="text-xs text-muted-foreground">@{{ user.username }}</span>
              <BadgeCheck v-if="user.is_verified" class="w-4 h-4 text-primary" />
              <Crown v-if="user.is_vip" class="w-4 h-4 text-amber-500" />
              <Shield v-if="user.is_admin" class="w-4 h-4 text-destructive" />
            </div>
            <p class="text-xs text-muted-foreground">{{ user.email }} · {{ user.created_at }}</p>
            <div class="flex gap-3 mt-1 text-xs text-muted-foreground">
              <span>{{ user.posts_count }} postů</span>
              <span>{{ user.followers_count }} followers</span>
              <span>{{ user.following_count }} following</span>
            </div>
          </div>

          <!-- Toggle Buttons -->
          <div class="flex items-center gap-1.5 flex-shrink-0">
            <button 
              @click="toggleFlag(user.id, 'is_verified', user.is_verified)"
              :class="['p-2 rounded-lg text-xs font-medium transition-all', user.is_verified ? 'bg-primary/20 text-primary' : 'bg-secondary/50 text-muted-foreground hover:text-foreground']"
              title="Verified"
            >
              <BadgeCheck class="w-4 h-4" />
            </button>
            <button 
              @click="toggleFlag(user.id, 'is_vip', user.is_vip)"
              :class="['p-2 rounded-lg text-xs font-medium transition-all', user.is_vip ? 'bg-amber-500/20 text-amber-500' : 'bg-secondary/50 text-muted-foreground hover:text-foreground']"
              title="VIP"
            >
              <Crown class="w-4 h-4" />
            </button>
            <button 
              @click="toggleFlag(user.id, 'is_creator', user.is_creator)"
              :class="['p-2 rounded-lg text-xs font-medium transition-all', user.is_creator ? 'bg-pink-500/20 text-pink-500' : 'bg-secondary/50 text-muted-foreground hover:text-foreground']"
              title="Creator"
            >
              <UserCheck class="w-4 h-4" />
            </button>
            <button 
              v-if="!user.is_admin"
              @click="deleteUser(user.id, user.name)"
              class="p-2 rounded-lg bg-secondary/50 text-muted-foreground hover:bg-destructive/20 hover:text-destructive transition-all"
              title="Smazat"
            >
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      <!-- Posts Tab -->
      <div v-if="activeTab === 'posts'" class="grid grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-3">
        <div 
          v-for="post in posts" 
          :key="post.id"
          class="relative rounded-2xl overflow-hidden bg-card/50 border border-border/50 group"
        >
          <div class="aspect-square">
            <img :src="post.image" :alt="post.caption" class="w-full h-full object-cover" />
          </div>
          <div class="p-3">
            <p class="text-xs font-medium truncate">{{ post.user_name }}</p>
            <p v-if="post.caption" class="text-xs text-muted-foreground truncate mt-0.5">{{ post.caption }}</p>
            <div class="flex items-center gap-3 mt-1.5 text-xs text-muted-foreground">
              <span>❤ {{ post.likes_count }}</span>
              <span>💬 {{ post.comments_count }}</span>
            </div>
            <p class="text-[10px] text-muted-foreground/60 mt-1">{{ post.created_at }}</p>
          </div>
          <button 
            @click="deletePost(post.id)"
            class="absolute top-2 right-2 p-2 rounded-lg bg-black/60 text-white hover:bg-destructive transition-all opacity-0 group-hover:opacity-100"
          >
            <Trash2 class="w-4 h-4" />
          </button>
        </div>
      </div>

      <div v-if="(activeTab === 'users' && !users.length) || (activeTab === 'posts' && !posts.length)" class="text-center py-16">
        <p class="text-muted-foreground">Žádná data</p>
      </div>
    </div>
  </div>
</template>
