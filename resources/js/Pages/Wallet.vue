<template>
  <AuthenticatedLayout>
    <div class="max-w-2xl mx-auto px-4 py-8 space-y-8">

      <!-- Balance card -->
      <div class="bg-card border border-border rounded-2xl p-6">
        <p class="text-sm text-muted-foreground mb-1">Váš zůstatek</p>
        <p class="text-4xl font-bold text-foreground">{{ balance.toFixed(2) }} <span class="text-xl text-muted-foreground">kreditů</span></p>
        <p class="text-xs text-muted-foreground mt-1">1 kredit = 1 Kč</p>
      </div>

      <!-- Deposit -->
      <div class="bg-card border border-border rounded-2xl p-6 space-y-4">
        <h2 class="text-lg font-semibold">Dobít kredity (krypto)</h2>
        <div class="grid grid-cols-3 gap-2">
          <button
            v-for="amt in [50, 100, 200, 500, 1000, 2000]"
            :key="amt"
            @click="selectedAmount = amt"
            :class="[
              'rounded-xl border py-3 text-sm font-medium transition',
              selectedAmount === amt
                ? 'border-primary bg-primary/10 text-primary'
                : 'border-border text-foreground hover:border-primary/50'
            ]"
          >{{ amt }} Kč</button>
        </div>
        <button
          @click="deposit"
          :disabled="!selectedAmount || depositLoading"
          class="w-full bg-primary text-primary-foreground rounded-xl py-3 font-semibold disabled:opacity-50 transition"
        >
          <span v-if="depositLoading">Načítám...</span>
          <span v-else>Zaplatit kryptem {{ selectedAmount ? '(' + selectedAmount + ' Kč)' : '' }}</span>
        </button>
        <p class="text-xs text-muted-foreground text-center">Platba probíhá přes NOWPayments — BTC, ETH, USDT, LTC a další</p>
        <button @click="showGuide = true" class="w-full text-xs text-primary underline text-center">
          Nevím jak zaplatit kryptem → Průvodce pro začátečníky
        </button>
      </div>

      <!-- Withdraw -->
      <div class="bg-card border border-border rounded-2xl p-6 space-y-4">
        <h2 class="text-lg font-semibold">Výběr kreditů</h2>
        <input
          v-model="walletAddress"
          type="text"
          placeholder="Tvoje krypto adresa (USDT-TRC20, BTC...)"
          class="w-full bg-background border border-border rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-primary"
        />
        <input
          v-model.number="withdrawAmount"
          type="number"
          min="100"
          placeholder="Částka v kreditech (min. 100)"
          class="w-full bg-background border border-border rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-primary"
        />
        <button
          @click="withdraw"
          :disabled="withdrawLoading || withdrawAmount < 100"
          class="w-full bg-muted text-foreground rounded-xl py-3 font-semibold disabled:opacity-50 transition"
        >
          <span v-if="withdrawLoading">Odesílám...</span>
          <span v-else>Vybrat {{ withdrawAmount || 0 }} kreditů</span>
        </button>
        <p class="text-xs text-muted-foreground">Výplata proběhne na zadanou krypto adresu. Žádný bankovní účet.</p>
      </div>

      <!-- Transaction history -->
      <div class="bg-card border border-border rounded-2xl p-6 space-y-4">
        <h2 class="text-lg font-semibold">Historie transakcí</h2>
        <div v-if="transactions.length === 0" class="text-sm text-muted-foreground text-center py-4">
          Zatím žádné transakce.
        </div>
        <div v-else class="divide-y divide-border">
          <div v-for="tx in transactions" :key="tx.id" class="py-3 flex justify-between items-center">
            <div>
              <p class="text-sm font-medium">{{ txLabel(tx.type) }}</p>
              <p class="text-xs text-muted-foreground">{{ tx.created_at }}</p>
            </div>
            <span :class="tx.amount > 0 ? 'text-green-500' : 'text-red-400'" class="text-sm font-semibold">
              {{ tx.amount > 0 ? '+' : '' }}{{ tx.amount }} kreditů
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- CryptoGuide modal -->
    <CryptoGuide v-if="showGuide" @close="showGuide = false" />
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import CryptoGuide from '@/Components/Socly/CryptoGuide.vue'
import axios from 'axios'

const props = defineProps({
  balance: Number,
  cryptoWallet: String,
  transactions: Array,
})

const selectedAmount = ref(null)
const depositLoading = ref(false)
const walletAddress = ref(props.cryptoWallet || '')
const withdrawAmount = ref(null)
const withdrawLoading = ref(false)
const showGuide = ref(false)
const balance = ref(props.balance)

const txLabel = (type) => ({
  deposit: '💳 Dobití kreditů',
  unlock: '🔓 Odemčení obsahu',
  tip: '💸 Spropitné',
  subscription: '⭐ Předplatné',
  withdrawal: '💰 Výběr',
}[type] || type)

const deposit = async () => {
  if (!selectedAmount.value) return
  depositLoading.value = true
  try {
    const { data } = await axios.post('/wallet/deposit', { amount: selectedAmount.value })
    window.location.href = data.invoice_url
  } catch (e) {
    alert(e.response?.data?.error || 'Chyba při vytváření platby.')
  } finally {
    depositLoading.value = false
  }
}

const withdraw = async () => {
  if (!withdrawAmount.value || withdrawAmount.value < 100) return
  withdrawLoading.value = true
  try {
    const { data } = await axios.post('/wallet/withdraw', {
      amount: withdrawAmount.value,
      crypto_wallet: walletAddress.value || null,
    })
    balance.value = data.balance
    withdrawAmount.value = null
    alert('Výběr byl zadán. Zpracování může trvat 1–24 hodin.')
  } catch (e) {
    alert(e.response?.data?.error || 'Chyba při výběru.')
  } finally {
    withdrawLoading.value = false
  }
}
</script>
