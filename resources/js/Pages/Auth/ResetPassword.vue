<script setup>
import { useForm, Head } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Lock, Eye, EyeOff } from 'lucide-vue-next'

const props = defineProps({ token: String, email: String })

const showPassword = ref(false)

const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: '',
})

const submit = () => form.post('/reset-password')
</script>

<template>
  <Head title="Nové heslo" />
  <div class="min-h-dvh bg-background flex items-center justify-center p-4">
    <div class="w-full max-w-[400px]">
      <div class="flex flex-col items-center mb-8">
        <h1 class="text-4xl font-black tracking-tight mb-2">
          <span class="text-gradient-premium">SOCLY</span>
        </h1>
        <p class="text-muted-foreground text-sm">Nastavení nového hesla</p>
      </div>

      <div class="bg-card border border-border rounded-2xl p-6 space-y-4">
        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <div class="relative">
              <Lock class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Nové heslo (min. 8 znaků)"
                class="w-full bg-background border border-border rounded-xl pl-10 pr-10 py-3 text-sm focus:outline-none focus:border-primary transition"
                :class="{ 'border-destructive': form.errors.password }"
              />
              <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground">
                <Eye v-if="!showPassword" class="w-4 h-4" />
                <EyeOff v-else class="w-4 h-4" />
              </button>
            </div>
            <p v-if="form.errors.password" class="text-destructive text-xs mt-1">{{ form.errors.password }}</p>
          </div>

          <div>
            <div class="relative">
              <Lock class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
              <input
                v-model="form.password_confirmation"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Zopakuj heslo"
                class="w-full bg-background border border-border rounded-xl pl-10 pr-4 py-3 text-sm focus:outline-none focus:border-primary transition"
              />
            </div>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full bg-primary text-primary-foreground rounded-xl py-3 font-semibold disabled:opacity-50 transition"
          >
            {{ form.processing ? 'Ukládám...' : 'Nastavit nové heslo' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
