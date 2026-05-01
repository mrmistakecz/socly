<script setup>
import { Head, router } from '@inertiajs/vue3'
import { Home } from 'lucide-vue-next'

const props = defineProps({
  status: { type: Number, default: 404 },
})

const titles = {
  404: 'Stránka nenalezena',
  403: 'Přístup odepřen',
  500: 'Chyba serveru',
  503: 'Služba nedostupná',
}

const descriptions = {
  404: 'Stránka, kterou hledáte, neexistuje nebo byla přesunuta.',
  403: 'Nemáte oprávnění k zobrazení této stránky.',
  500: 'Něco se pokazilo. Zkuste to prosím znovu.',
  503: 'Služba je dočasně nedostupná. Zkuste to později.',
}
</script>

<template>
  <Head :title="`${status} - ${titles[status] || 'Chyba'}`" />
  
  <div class="min-h-dvh bg-background flex items-center justify-center p-4">
    <div class="text-center max-w-md">
      <div class="inline-flex items-center justify-center w-24 h-24 rounded-3xl bg-gradient-to-br from-primary via-pink-500 to-accent mb-8 glow-primary">
        <span class="text-4xl font-black text-white">{{ status }}</span>
      </div>
      
      <h1 class="text-2xl font-bold mb-3">{{ titles[status] || 'Chyba' }}</h1>
      <p class="text-muted-foreground mb-8">{{ descriptions[status] || 'Něco se pokazilo.' }}</p>
      
      <button 
        @click="router.visit('/')"
        class="inline-flex items-center gap-2 px-8 py-4 rounded-xl bg-gradient-to-r from-primary via-pink-500 to-accent text-white font-bold btn-premium glow-primary transition-all hover:scale-[1.02] active:scale-[0.98]"
      >
        <Home class="w-5 h-5" />
        Zpět na hlavní stránku
      </button>
    </div>
  </div>
</template>
