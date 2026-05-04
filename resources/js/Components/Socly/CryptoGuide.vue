<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4" @click.self="$emit('close')">
    <div class="bg-card border border-border rounded-2xl w-full max-w-md max-h-[90vh] overflow-y-auto p-6 space-y-6">
      <div class="flex justify-between items-center">
        <h2 class="text-lg font-bold">🪙 Jak koupit krypto a dobít kredity</h2>
        <button @click="$emit('close')" class="text-muted-foreground hover:text-foreground text-xl leading-none">✕</button>
      </div>

      <div class="space-y-5 text-sm">
        <div class="flex gap-3">
          <div class="w-8 h-8 rounded-full bg-primary/20 text-primary flex items-center justify-center font-bold shrink-0">1</div>
          <div>
            <p class="font-semibold">Stáhni Revolut, Binance nebo MoonPay</p>
            <p class="text-muted-foreground mt-1">Doporučujeme <strong>Revolut</strong> (česky, CZK) nebo <strong>MoonPay</strong> (karta bez registrace).</p>
          </div>
        </div>

        <div class="flex gap-3">
          <div class="w-8 h-8 rounded-full bg-primary/20 text-primary flex items-center justify-center font-bold shrink-0">2</div>
          <div>
            <p class="font-semibold">Kup USDT nebo LTC kartou</p>
            <p class="text-muted-foreground mt-1">V aplikaci vyber "Koupit krypto", zadej částku v Kč a zaplať kartou. Doporučujeme <strong>USDT (TRC-20)</strong> — nízké poplatky.</p>
          </div>
        </div>

        <div class="flex gap-3">
          <div class="w-8 h-8 rounded-full bg-primary/20 text-primary flex items-center justify-center font-bold shrink-0">3</div>
          <div>
            <p class="font-semibold">Klikni "Dobít kredity" na SOCLY</p>
            <p class="text-muted-foreground mt-1">Vyber částku a klikni na tlačítko. Přesměrujeme tě na platební stránku NOWPayments.</p>
          </div>
        </div>

        <div class="flex gap-3">
          <div class="w-8 h-8 rounded-full bg-primary/20 text-primary flex items-center justify-center font-bold shrink-0">4</div>
          <div>
            <p class="font-semibold">Pošli krypto na vygenerovanou adresu</p>
            <p class="text-muted-foreground mt-1">NOWPayments ti ukáže adresu peněženky — zkopíruj ji do Revolut/Binance a odešli.</p>
          </div>
        </div>

        <div class="flex gap-3">
          <div class="w-8 h-8 rounded-full bg-green-500/20 text-green-500 flex items-center justify-center font-bold shrink-0">✓</div>
          <div>
            <p class="font-semibold text-green-500">Kredity se připíšou automaticky</p>
            <p class="text-muted-foreground mt-1">Obvykle do 1–15 minut po potvrzení transakce. Uvidíš notifikaci a zůstatek se aktualizuje.</p>
          </div>
        </div>
      </div>

      <div class="bg-muted rounded-xl p-4 text-xs text-muted-foreground">
        <strong>Tip:</strong> Pokud používáš Revolut, jdi do Crypto sekce → Koupit → USDT → vyber TRC-20 síť při odesílání (nejlevnější).
      </div>

      <button @click="$emit('close')" class="w-full bg-primary text-primary-foreground rounded-xl py-3 font-semibold">
        Rozumím, jdu dobít kredity
      </button>
    </div>
  </div>
</template>

<script setup>
defineEmits(['close'])
</script>
