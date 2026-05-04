<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 px-4">
    <div class="bg-card border border-border rounded-2xl w-full max-w-md p-6 space-y-6">

      <!-- Step indicators -->
      <div class="flex gap-2 justify-center">
        <div v-for="i in 3" :key="i"
          :class="['h-1 rounded-full flex-1 transition-all', step >= i ? 'bg-primary' : 'bg-border']"
        />
      </div>

      <!-- Step 1: Welcome -->
      <div v-if="step === 1" class="space-y-4 text-center">
        <div class="text-5xl">👋</div>
        <h2 class="text-xl font-bold">Vítej na SOCLY!</h2>
        <p class="text-sm text-muted-foreground">Platforma pro tvůrce bez hranic. Sdílej obsah, získej předplatitele a vydělávej kryptem — bez bank.</p>
        <div class="grid grid-cols-3 gap-3 text-xs text-center pt-2">
          <div class="bg-muted rounded-xl p-3">
            <div class="text-2xl mb-1">🔒</div>
            <p class="font-medium">Soukromý obsah</p>
            <p class="text-muted-foreground mt-1">Zamkni příspěvky jen pro předplatitele</p>
          </div>
          <div class="bg-muted rounded-xl p-3">
            <div class="text-2xl mb-1">💰</div>
            <p class="font-medium">Krypto výplaty</p>
            <p class="text-muted-foreground mt-1">Výplata na krypto peněženku</p>
          </div>
          <div class="bg-muted rounded-xl p-3">
            <div class="text-2xl mb-1">🌍</div>
            <p class="font-medium">Bez cenzury</p>
            <p class="text-muted-foreground mt-1">Obsah pro dospělé povolen</p>
          </div>
        </div>
      </div>

      <!-- Step 2: Creator setup -->
      <div v-if="step === 2" class="space-y-4">
        <div class="text-center">
          <div class="text-4xl mb-2">⭐</div>
          <h2 class="text-xl font-bold">Chceš být tvůrce?</h2>
          <p class="text-sm text-muted-foreground mt-1">Tvůrci mohou zamykat obsah a přijímat předplatné.</p>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <button
            @click="wantsCreator = true"
            :class="['border rounded-xl p-4 text-sm font-medium transition', wantsCreator === true ? 'border-primary bg-primary/10 text-primary' : 'border-border hover:border-primary/50']"
          >
            <div class="text-2xl mb-2">✍️</div>
            Ano, jsem tvůrce
          </button>
          <button
            @click="wantsCreator = false"
            :class="['border rounded-xl p-4 text-sm font-medium transition', wantsCreator === false ? 'border-primary bg-primary/10 text-primary' : 'border-border hover:border-primary/50']"
          >
            <div class="text-2xl mb-2">👁️</div>
            Jen sleduji
          </button>
        </div>
      </div>

      <!-- Step 3: Age & rules confirmation -->
      <div v-if="step === 3" class="space-y-4">
        <div class="text-center">
          <div class="text-4xl mb-2">✅</div>
          <h2 class="text-xl font-bold">Ještě jedna věc</h2>
        </div>
        <div class="space-y-3 text-sm">
          <label class="flex gap-3 items-start cursor-pointer">
            <input type="checkbox" v-model="confirmAge" class="mt-0.5 accent-primary" />
            <span class="text-muted-foreground">Potvrzuji, že mi je <strong class="text-foreground">18 nebo více let</strong>.</span>
          </label>
          <label class="flex gap-3 items-start cursor-pointer">
            <input type="checkbox" v-model="confirmRules" class="mt-0.5 accent-primary" />
            <span class="text-muted-foreground">Souhlasím s <a href="/terms" target="_blank" class="text-primary underline">podmínkami</a> a <a href="/content-policy" target="_blank" class="text-primary underline">pravidly obsahu</a>.</span>
          </label>
        </div>
      </div>

      <!-- Navigation -->
      <div class="flex gap-3">
        <button v-if="step > 1" @click="step--" class="flex-1 border border-border rounded-xl py-3 text-sm font-medium hover:border-primary/50 transition">
          Zpět
        </button>
        <button
          @click="next"
          :disabled="!canProceed || loading"
          class="flex-1 bg-primary text-primary-foreground rounded-xl py-3 text-sm font-semibold disabled:opacity-50 transition"
        >
          {{ step === 3 ? (loading ? 'Ukládám...' : 'Začít!') : 'Pokračovat' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'

const emit = defineEmits(['done'])

const step = ref(1)
const wantsCreator = ref(null)
const confirmAge = ref(false)
const confirmRules = ref(false)
const loading = ref(false)

const canProceed = computed(() => {
  if (step.value === 1) return true
  if (step.value === 2) return wantsCreator.value !== null
  if (step.value === 3) return confirmAge.value && confirmRules.value
  return false
})

const next = async () => {
  if (step.value < 3) {
    step.value++
    return
  }
  loading.value = true
  try {
    await axios.post('/onboarding/complete')
    emit('done', { wantsCreator: wantsCreator.value })
  } catch {
    emit('done', { wantsCreator: wantsCreator.value })
  } finally {
    loading.value = false
  }
}
</script>
