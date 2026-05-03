<script setup>
import { ref } from 'vue'
import { Head, useForm, router, usePage } from '@inertiajs/vue3'
import { ChevronLeft, Camera, User, Mail, FileText, Shield, Bell, Palette, LogOut, Sparkles, Crown, ChevronRight } from 'lucide-vue-next'

const page = usePage()
const user = page.props.auth.user

const form = useForm({
  name: user.name,
  bio: user.bio || '',
  avatar: null,
  cover_image: null,
})

const avatarPreview = ref(user.avatar)
const coverPreview = ref(user.cover_image)
const activeSection = ref('profile')

const handleAvatarChange = (e) => {
  const file = e.target.files[0]
  if (file) {
    form.avatar = file
    avatarPreview.value = URL.createObjectURL(file)
  }
}

const handleCoverChange = (e) => {
  const file = e.target.files[0]
  if (file) {
    form.cover_image = file
    coverPreview.value = URL.createObjectURL(file)
  }
}

const submit = () => {
  form.transform((data) => ({
    ...data,
    _method: 'PUT',
  })).post('/profile', {
    forceFormData: true,
    preserveScroll: true,
  })
}

const handleLogout = () => {
  router.post('/logout')
}

const menuItems = [
  { id: 'profile', icon: User, label: 'Upravit profil', description: 'Jméno, bio, avatar' },
  { id: 'notifications', icon: Bell, label: 'Oznámení', description: 'Nastavení notifikací' },
  { id: 'privacy', icon: Shield, label: 'Soukromí', description: 'Viditelnost profilu' },
  { id: 'appearance', icon: Palette, label: 'Vzhled', description: 'Motiv aplikace' },
]
</script>

<template>
  <Head title="Nastavení" />
  
  <div class="min-h-dvh bg-background">
    <!-- Header -->
    <div class="sticky top-0 z-30 glass-heavy">
      <div class="flex items-center gap-3 px-4 py-4">
        <button @click="router.visit('/profile')" class="p-2 rounded-xl hover:bg-secondary/50 transition-colors">
          <ChevronLeft class="w-5 h-5" />
        </button>
        <h1 class="text-lg font-bold">Nastavení</h1>
      </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 py-6 space-y-6">
      <!-- Profile Header -->
      <div class="flex items-center gap-4 p-4 rounded-2xl bg-card/50 border border-border/50 relative overflow-hidden">
        <!-- Cover Photo Preview -->
        <div class="absolute inset-0 z-0">
          <img :src="coverPreview || user.cover_image || '/images/default-cover.svg'" class="w-full h-full object-cover opacity-30" />
          <label class="absolute top-2 right-2 px-3 py-1.5 bg-black/50 text-white rounded-lg flex items-center gap-2 cursor-pointer text-xs font-medium hover:bg-black/70 transition-all z-10 backdrop-blur-md">
            <Camera class="w-3.5 h-3.5" />
            Změnit pozadí
            <input type="file" accept="image/*" @change="handleCoverChange" class="hidden" />
          </label>
        </div>

        <div class="relative z-10 flex items-center gap-4 w-full">
          <div class="relative">
            <div class="w-16 h-16 rounded-2xl overflow-hidden bg-secondary ring-2 ring-background">
              <img 
                :src="avatarPreview || user.avatar || '/images/default-avatar.svg'" 
                class="w-full h-full object-cover" 
              />
            </div>
            <label class="absolute -bottom-1 -right-1 w-7 h-7 rounded-full bg-primary flex items-center justify-center cursor-pointer border-2 border-background shadow-sm hover:scale-105 transition-transform">
              <Camera class="w-3.5 h-3.5 text-white" />
              <input type="file" accept="image/*" @change="handleAvatarChange" class="hidden" />
            </label>
          </div>
          <div>
            <p class="font-bold text-lg drop-shadow-sm">{{ user.name }}</p>
            <p class="text-sm text-foreground/80 drop-shadow-sm">@{{ user.username }}</p>
          </div>
          <div v-if="user.is_vip" class="ml-auto flex items-center gap-1 px-3 py-1.5 bg-gradient-to-r from-gold/20 to-amber-500/20 rounded-xl border border-gold/30 backdrop-blur-md">
            <Crown class="w-4 h-4 text-gold" />
            <span class="text-xs font-semibold text-gold">VIP</span>
          </div>
        </div>
      </div>

      <!-- VIP Upgrade Banner -->
      <div v-if="!user.is_vip" class="relative overflow-hidden rounded-2xl">
        <div class="absolute inset-0 bg-gradient-to-r from-primary/20 via-purple-500/20 to-accent/20" />
        <div class="absolute inset-0 glass-card" />
        <div class="relative p-5">
          <div class="flex items-center gap-2 mb-2">
            <Crown class="w-5 h-5 text-gold" />
            <h3 class="font-bold">Získejte VIP</h3>
          </div>
          <p class="text-sm text-muted-foreground mb-4">Prioritní odpovědi, exkluzivní badge a více</p>
          <button class="flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-primary to-accent rounded-xl text-sm font-bold text-white glow-primary btn-premium">
            <Sparkles class="w-4 h-4" />
            Upgradovat
          </button>
        </div>
      </div>

      <!-- Menu Items -->
      <div class="space-y-1">
        <button
          v-for="item in menuItems"
          :key="item.id"
          @click="activeSection = item.id"
          :class="[
            'w-full flex items-center gap-4 p-4 rounded-xl transition-all text-left',
            activeSection === item.id ? 'bg-primary/10 border border-primary/20' : 'hover:bg-secondary/30'
          ]"
        >
          <div :class="[
            'w-10 h-10 rounded-xl flex items-center justify-center',
            activeSection === item.id ? 'bg-primary/20 text-primary' : 'bg-secondary/50 text-muted-foreground'
          ]">
            <component :is="item.icon" class="w-5 h-5" />
          </div>
          <div class="flex-1">
            <p class="font-medium text-sm">{{ item.label }}</p>
            <p class="text-xs text-muted-foreground">{{ item.description }}</p>
          </div>
          <ChevronRight class="w-4 h-4 text-muted-foreground" />
        </button>
      </div>

      <!-- Edit Profile Form -->
      <div v-if="activeSection === 'profile'" class="space-y-4 p-4 rounded-2xl bg-card/50 border border-border/50">
        <h3 class="font-bold mb-4">Upravit profil</h3>
        
        <div>
          <label class="block text-sm font-medium mb-2">Jméno</label>
          <input
            v-model="form.name"
            type="text"
            class="w-full px-4 py-3 bg-secondary/50 border border-border/50 rounded-xl text-sm focus:outline-none focus:border-primary/50 focus:ring-2 focus:ring-primary/20 transition-all"
          />
          <p v-if="form.errors.name" class="text-xs text-destructive mt-1">{{ form.errors.name }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium mb-2">Bio</label>
          <textarea
            v-model="form.bio"
            rows="4"
            class="w-full px-4 py-3 bg-secondary/50 border border-border/50 rounded-xl text-sm resize-none focus:outline-none focus:border-primary/50 focus:ring-2 focus:ring-primary/20 transition-all"
            placeholder="Napište něco o sobě..."
          />
          <div class="flex justify-between mt-1">
            <p v-if="form.errors.bio" class="text-xs text-destructive">{{ form.errors.bio }}</p>
            <p class="text-xs text-muted-foreground ml-auto">{{ form.bio.length }}/500</p>
          </div>
        </div>

        <button
          @click="submit"
          :disabled="form.processing"
          class="w-full py-3 rounded-xl bg-gradient-to-r from-primary via-pink-500 to-accent text-white font-bold btn-premium transition-all hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50"
        >
          {{ form.processing ? 'Ukládání...' : 'Uložit změny' }}
        </button>
      </div>

      <!-- Notifications Settings -->
      <div v-if="activeSection === 'notifications'" class="space-y-3 p-4 rounded-2xl bg-card/50 border border-border/50">
        <h3 class="font-bold mb-4">Oznámení</h3>
        <p class="text-sm text-muted-foreground">Nastavení notifikací bude brzy dostupné.</p>
      </div>

      <!-- Privacy Settings -->
      <div v-if="activeSection === 'privacy'" class="space-y-3 p-4 rounded-2xl bg-card/50 border border-border/50">
        <h3 class="font-bold mb-4">Soukromí</h3>
        <p class="text-sm text-muted-foreground">Nastavení soukromí bude brzy dostupné.</p>
      </div>

      <!-- Appearance Settings -->
      <div v-if="activeSection === 'appearance'" class="space-y-3 p-4 rounded-2xl bg-card/50 border border-border/50">
        <h3 class="font-bold mb-4">Vzhled</h3>
        <p class="text-sm text-muted-foreground">Tmavý motiv je aktivní. Další motivy budou brzy dostupné.</p>
      </div>

      <!-- Logout -->
      <button
        @click="handleLogout"
        class="w-full flex items-center justify-center gap-2 p-4 rounded-xl text-destructive hover:bg-destructive/10 transition-all"
      >
        <LogOut class="w-5 h-5" />
        <span class="font-medium">Odhlásit se</span>
      </button>

      <!-- App Info -->
      <div class="text-center text-xs text-muted-foreground pb-8">
        <p>SOCLY v3.0 &middot; Premium Content Platform</p>
        <p class="mt-1">© 2026 SOCLY. Všechna práva vyhrazena.</p>
      </div>
    </div>
  </div>
</template>
