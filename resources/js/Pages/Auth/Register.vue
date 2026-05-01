<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Eye, EyeOff, Mail, Lock, User, AtSign } from 'lucide-vue-next'

const form = useForm({
  name: '',
  username: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const showPassword = ref(false)
const showConfirmPassword = ref(false)

const submit = () => {
  form.post('/register', {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>

<template>
  <Head title="Registrace" />
  
  <div class="min-h-dvh bg-background flex items-center justify-center p-4">
    <div class="w-full max-w-[400px]">
      <!-- Logo -->
      <div class="flex flex-col items-center mb-8">
        <h1 class="text-4xl font-black tracking-tight mb-2">
          <span class="text-gradient-premium">SOCLY</span><sup class="text-[0.4em] text-primary animate-pulse-spark align-super font-bold">;)</sup>
        </h1>
        <p class="text-sm text-muted-foreground mt-1">Vytvořte si účet</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="space-y-4">
        <div class="glass-card p-6 space-y-4">
          <!-- Name -->
          <div>
            <label class="block text-sm font-medium mb-2">Jméno</label>
            <div class="relative">
              <User class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-muted-foreground" />
              <input
                v-model="form.name"
                type="text"
                required
                class="w-full pl-11 pr-4 py-3 bg-secondary/50 border border-border/50 rounded-xl text-sm focus:outline-none focus:border-primary/50 focus:ring-2 focus:ring-primary/20 transition-all"
                placeholder="Vaše jméno"
              />
            </div>
            <p v-if="form.errors.name" class="text-xs text-destructive mt-1">{{ form.errors.name }}</p>
          </div>

          <!-- Username -->
          <div>
            <label class="block text-sm font-medium mb-2">Uživatelské jméno</label>
            <div class="relative">
              <AtSign class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-muted-foreground" />
              <input
                v-model="form.username"
                type="text"
                required
                class="w-full pl-11 pr-4 py-3 bg-secondary/50 border border-border/50 rounded-xl text-sm focus:outline-none focus:border-primary/50 focus:ring-2 focus:ring-primary/20 transition-all"
                placeholder="username"
              />
            </div>
            <p v-if="form.errors.username" class="text-xs text-destructive mt-1">{{ form.errors.username }}</p>
          </div>

          <!-- Email -->
          <div>
            <label class="block text-sm font-medium mb-2">Email</label>
            <div class="relative">
              <Mail class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-muted-foreground" />
              <input
                v-model="form.email"
                type="email"
                required
                class="w-full pl-11 pr-4 py-3 bg-secondary/50 border border-border/50 rounded-xl text-sm focus:outline-none focus:border-primary/50 focus:ring-2 focus:ring-primary/20 transition-all"
                placeholder="vas@email.cz"
              />
            </div>
            <p v-if="form.errors.email" class="text-xs text-destructive mt-1">{{ form.errors.email }}</p>
          </div>

          <!-- Password -->
          <div>
            <label class="block text-sm font-medium mb-2">Heslo</label>
            <div class="relative">
              <Lock class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-muted-foreground" />
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                required
                class="w-full pl-11 pr-12 py-3 bg-secondary/50 border border-border/50 rounded-xl text-sm focus:outline-none focus:border-primary/50 focus:ring-2 focus:ring-primary/20 transition-all"
                placeholder="Minimálně 8 znaků"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
              >
                <Eye v-if="!showPassword" class="w-5 h-5" />
                <EyeOff v-else class="w-5 h-5" />
              </button>
            </div>
            <p v-if="form.errors.password" class="text-xs text-destructive mt-1">{{ form.errors.password }}</p>
          </div>

          <!-- Confirm Password -->
          <div>
            <label class="block text-sm font-medium mb-2">Potvrdit heslo</label>
            <div class="relative">
              <Lock class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-muted-foreground" />
              <input
                v-model="form.password_confirmation"
                :type="showConfirmPassword ? 'text' : 'password'"
                required
                class="w-full pl-11 pr-12 py-3 bg-secondary/50 border border-border/50 rounded-xl text-sm focus:outline-none focus:border-primary/50 focus:ring-2 focus:ring-primary/20 transition-all"
                placeholder="Zopakujte heslo"
              />
              <button
                type="button"
                @click="showConfirmPassword = !showConfirmPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
              >
                <Eye v-if="!showConfirmPassword" class="w-5 h-5" />
                <EyeOff v-else class="w-5 h-5" />
              </button>
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="form.processing"
          class="w-full py-4 rounded-xl bg-gradient-to-r from-primary via-pink-500 to-accent text-white font-bold btn-premium glow-primary transition-all hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50"
        >
          <span v-if="form.processing">Registrace...</span>
          <span v-else class="flex items-center justify-center gap-2">
            <Sparkles class="w-5 h-5" />
            Vytvořit účet
          </span>
        </button>

        <!-- Login Link -->
        <p class="text-center text-sm text-muted-foreground">
          Již máte účet?
          <Link href="/login" class="text-primary hover:text-primary/80 font-medium ml-1">
            Přihlaste se
          </Link>
        </p>
      </form>
    </div>
  </div>
</template>
