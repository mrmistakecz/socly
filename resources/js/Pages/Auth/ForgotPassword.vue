<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3'
import { Mail, ArrowLeft } from 'lucide-vue-next'

const form = useForm({ email: '' })

const submit = () => form.post('/forgot-password')
</script>

<template>
  <Head title="Zapomenuté heslo" />
  <div class="min-h-dvh bg-background flex items-center justify-center p-4">
    <div class="w-full max-w-[400px]">
      <div class="flex flex-col items-center mb-8">
        <h1 class="text-4xl font-black tracking-tight mb-2">
          <span class="text-gradient-premium">SOCLY</span>
        </h1>
        <p class="text-muted-foreground text-sm">Zapomenuté heslo</p>
      </div>

      <div class="bg-card border border-border rounded-2xl p-6 space-y-4">
        <div v-if="$page.props.flash?.success" class="bg-green-500/10 border border-green-500/30 rounded-xl p-3 text-sm text-green-400">
          {{ $page.props.flash.success }}
        </div>

        <p class="text-sm text-muted-foreground">
          Zadej svůj email a pošleme ti odkaz pro reset hesla.
        </p>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <div class="relative">
              <Mail class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
              <input
                v-model="form.email"
                type="email"
                placeholder="tvuj@email.cz"
                autocomplete="email"
                class="w-full bg-background border border-border rounded-xl pl-10 pr-4 py-3 text-sm focus:outline-none focus:border-primary transition"
                :class="{ 'border-destructive': form.errors.email }"
              />
            </div>
            <p v-if="form.errors.email" class="text-destructive text-xs mt-1">{{ form.errors.email }}</p>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full bg-primary text-primary-foreground rounded-xl py-3 font-semibold disabled:opacity-50 transition"
          >
            {{ form.processing ? 'Odesílám...' : 'Odeslat odkaz pro reset' }}
          </button>
        </form>

        <Link href="/login" class="flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground transition">
          <ArrowLeft class="w-4 h-4" /> Zpět na přihlášení
        </Link>
      </div>
    </div>
  </div>
</template>
