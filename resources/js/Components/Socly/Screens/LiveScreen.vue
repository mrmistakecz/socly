<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { X, Heart, Users, Gift, Send, Sparkles, Crown, Star, Zap, ChevronDown } from 'lucide-vue-next'

const props = defineProps({
  streamImage: { type: String, default: null },
  creatorAvatar: { type: String, default: null },
  creatorName: { type: String, default: 'Tvůrce' },
})

const emit = defineEmits(['close'])

const initialMessages = [
  { id: 1, user: 'Petr_M', message: 'Ahoj!' },
  { id: 2, user: 'Jana123', message: 'Super stream!' },
  { id: 3, user: 'VIP_Tom', message: 'Poslal jsem dysko!', isTip: true, tipAmount: 500, isVIP: true },
  { id: 4, user: 'MarekK', message: 'Krasna' },
  { id: 5, user: 'Anna_CZ', message: 'Moc se mi libi!' },
]

const newMessagesPool = [
  { user: 'David99', message: 'Wow!' },
  { user: 'Lucie_P', message: 'Super kvalita' },
  { user: 'TOP_Fan', message: 'Dysko leti!', isTip: true, tipAmount: 200, isVIP: true },
  { user: 'Kristyna', message: 'Pozdrav z Prahy!' },
  { user: 'Martin_VIP', message: 'Nejlepsi tvurkyne', isVIP: true },
  { user: 'Karolina M.', message: 'Dekuji vsem za podporu!', isCreator: true },
]

const tipAmounts = [
  { amount: 50, icon: Heart, color: 'from-pink-500 to-destructive' },
  { amount: 100, icon: Star, color: 'from-primary to-purple-500' },
  { amount: 200, icon: Zap, color: 'from-accent to-blue-500' },
  { amount: 500, icon: Crown, color: 'from-gold to-amber-500' },
  { amount: 1000, icon: Sparkles, color: 'from-primary via-purple-500 to-accent' },
  { amount: 2000, icon: Gift, color: 'from-gold via-amber-400 to-orange-500' },
]

const messages = ref(initialMessages)
const viewerCount = ref(1247)
const showTipModal = ref(false)
const selectedTip = ref(null)
const tipSent = ref(false)
const likeCount = ref(0)
const showFloatingHearts = ref([])
const inputMessage = ref('')
const chatRef = ref(null)

let messageInterval, viewerInterval

onMounted(() => {
  let messageIndex = 0
  messageInterval = setInterval(() => {
    if (messageIndex < newMessagesPool.length) {
      messages.value = [...messages.value.slice(-10), { ...newMessagesPool[messageIndex], id: Date.now() }]
      messageIndex++
      nextTick(() => {
        if (chatRef.value) {
          chatRef.value.scrollTop = chatRef.value.scrollHeight
        }
      })
    }
  }, 2500)

  viewerInterval = setInterval(() => {
    viewerCount.value += Math.floor(Math.random() * 15) - 5
  }, 4000)
})

onUnmounted(() => {
  clearInterval(messageInterval)
  clearInterval(viewerInterval)
})

const handleSendTip = () => {
  if (!selectedTip.value) return
  tipSent.value = true
  showTipModal.value = false
  messages.value = [...messages.value.slice(-10), { 
    id: Date.now(), 
    user: 'Ty', 
    message: `Poslal/a jsi ${selectedTip.value} Kc!`, 
    isTip: true, 
    tipAmount: selectedTip.value 
  }]
  selectedTip.value = null
  setTimeout(() => tipSent.value = false, 2500)
}

const handleLike = () => {
  likeCount.value++
  showFloatingHearts.value = [...showFloatingHearts.value, Date.now()]
  setTimeout(() => {
    showFloatingHearts.value = showFloatingHearts.value.slice(1)
  }, 1500)
}

const handleSendMessage = (e) => {
  e.preventDefault()
  if (!inputMessage.value.trim()) return
  messages.value = [...messages.value.slice(-10), {
    id: Date.now(),
    user: 'Ty',
    message: inputMessage.value.trim()
  }]
  inputMessage.value = ''
}
</script>

<template>
  <div class="fixed inset-0 z-50 bg-black">
    <!-- Video Background -->
    <div class="absolute inset-0">
      <img
        :src="streamImage || '/images/default-cover.svg'"
        alt="Live stream"
        class="w-full h-full object-cover"
      />
      <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-transparent to-black/80" />
      <div class="absolute inset-0 bg-gradient-to-r from-black/40 via-transparent to-transparent" />
    </div>

    <!-- Floating Hearts -->
    <div class="absolute right-20 bottom-48 pointer-events-none">
      <Heart 
        v-for="id in showFloatingHearts" 
        :key="id"
        class="absolute w-8 h-8 text-destructive fill-destructive animate-float-up"
        :style="{ left: `${Math.random() * 40}px` }"
      />
    </div>

    <!-- Top Bar -->
    <div class="absolute top-0 left-0 right-0 pt-safe z-10">
      <div class="flex items-center justify-between p-4">
        <div class="flex items-center gap-3">
          <div class="relative">
            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-destructive via-primary to-accent p-[2px] animate-pulse-glow">
              <div class="w-full h-full rounded-full overflow-hidden bg-black p-[2px]">
                <img
                  :src="creatorAvatar || '/images/default-avatar.svg'"
                  alt="Creator"
                  class="w-full h-full rounded-full object-cover"
                />
              </div>
            </div>
            <div class="absolute -bottom-0.5 -right-0.5 w-5 h-5 bg-primary rounded-full flex items-center justify-center border-2 border-black">
              <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
          
          <div>
            <div class="flex items-center gap-2 mb-0.5">
              <span class="font-bold text-white text-base">Karolina M.</span>
              <div class="flex items-center gap-1 px-2 py-1 rounded-full bg-destructive glow-live">
                <span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse" />
                <span class="text-[10px] font-bold text-white uppercase">ZIVE</span>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <div class="flex items-center gap-1 text-white/80 text-sm">
                <Users class="w-4 h-4" />
                <span class="font-medium">{{ viewerCount.toLocaleString() }}</span>
              </div>
              <div class="flex items-center gap-1 text-destructive text-sm">
                <Heart class="w-4 h-4 fill-current" />
                <span class="font-medium">{{ likeCount }}</span>
              </div>
            </div>
          </div>
        </div>

        <button 
          @click="$emit('close')"
          class="w-11 h-11 rounded-full glass flex items-center justify-center hover:bg-white/20 transition-all"
        >
          <X class="w-5 h-5 text-white" />
        </button>
      </div>
    </div>

    <!-- Chat Messages -->
    <div 
      ref="chatRef"
      class="absolute left-0 right-24 bottom-36 max-h-52 overflow-hidden p-4"
    >
      <div class="flex flex-col gap-2">
        <div 
          v-for="(msg, index) in messages.slice(-8)" 
          :key="msg.id" 
          class="flex items-start gap-2"
          :style="{ opacity: index === messages.slice(-8).length - 1 ? 1 : index === messages.slice(-8).length - 2 ? 0.85 : index === messages.slice(-8).length - 3 ? 0.65 : index === messages.slice(-8).length - 4 ? 0.45 : 0.3 }"
        >
          <div :class="[
            'px-3 py-2 rounded-2xl max-w-[85%]',
            msg.isCreator ? 'bg-primary/30 border border-primary/30' : '',
            msg.isTip && !msg.isCreator ? 'bg-gold/20 border border-gold/30' : '',
            !msg.isTip && !msg.isCreator ? 'glass' : ''
          ]">
            <div class="flex items-center gap-1.5 flex-wrap">
              <Crown v-if="msg.isVIP" class="w-3 h-3 text-gold" />
              <Star v-if="msg.isCreator" class="w-3 h-3 text-primary fill-primary" />
              <span :class="['text-xs font-bold', msg.isCreator ? 'text-primary' : msg.isTip ? 'text-gold' : msg.isVIP ? 'text-gold' : 'text-accent']">
                {{ msg.user }}
              </span>
              <span v-if="msg.isTip" class="text-xs text-gold flex items-center gap-1">
                <Gift class="w-3 h-3" />
                {{ msg.tipAmount }} Kc
              </span>
            </div>
            <p class="text-sm text-white/95 mt-0.5">{{ msg.message }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Side Actions -->
    <div class="absolute right-4 bottom-36 flex flex-col items-center gap-4">
      <button 
        @click="handleLike"
        class="w-14 h-14 rounded-full glass flex items-center justify-center hover:scale-110 transition-all group"
      >
        <Heart class="w-7 h-7 text-destructive group-active:fill-destructive transition-all" />
      </button>
      
      <button 
        @click="showTipModal = true"
        class="relative group"
      >
        <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-primary via-pink-500 to-accent blur-lg opacity-70 group-hover:opacity-100 transition-opacity" />
        <div class="relative w-20 h-20 rounded-2xl bg-gradient-to-br from-primary via-pink-500 to-accent flex flex-col items-center justify-center glow-primary-intense btn-premium">
          <Gift class="w-8 h-8 text-white mb-0.5" />
          <span class="text-[10px] font-bold text-white uppercase">Dysko</span>
          <Sparkles class="absolute -top-1 -right-1 w-5 h-5 text-gold animate-pulse" />
        </div>
      </button>
    </div>

    <!-- Bottom Input -->
    <div class="absolute bottom-0 left-0 right-0 pb-safe">
      <form @submit="handleSendMessage" class="p-4">
        <div class="flex items-center gap-3">
          <div class="flex-1 glass rounded-2xl px-4 py-3">
            <input
              v-model="inputMessage"
              type="text"
              placeholder="Napsat zpravu..."
              class="w-full bg-transparent text-white placeholder-white/50 text-sm outline-none"
            />
          </div>
          <button 
            type="submit"
            class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-accent flex items-center justify-center glow-primary btn-premium"
          >
            <Send class="w-5 h-5 text-white" />
          </button>
        </div>
      </form>
    </div>

    <!-- Tip Success Animation -->
    <div v-if="tipSent" class="absolute inset-0 flex items-center justify-center pointer-events-none z-60">
      <div class="flex flex-col items-center gap-4 animate-scale-in">
        <div class="relative">
          <Sparkles class="w-24 h-24 text-gold animate-pulse" />
          <div class="absolute inset-0 w-24 h-24 bg-gold/30 rounded-full blur-2xl" />
        </div>
        <span class="text-3xl font-black text-gold text-glow-gold">Dekujeme!</span>
      </div>
    </div>

    <!-- Tip Modal -->
    <div 
      v-if="showTipModal"
      class="absolute inset-0 flex items-end justify-center bg-black/70 backdrop-blur-sm z-60"
      @click="showTipModal = false"
    >
      <div 
        class="w-full max-w-md bg-background border-t border-border/50 rounded-t-3xl p-6 pb-safe animate-slide-up"
        @click.stop
      >
        <div class="w-12 h-1.5 bg-muted rounded-full mx-auto mb-6" />
        
        <div class="text-center mb-6">
          <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-primary/10 mb-3">
            <Gift class="w-8 h-8 text-primary" />
          </div>
          <h3 class="text-xl font-bold mb-1">Poslat Dysko</h3>
          <p class="text-sm text-muted-foreground">Podporte Karolinu svym prispevkem</p>
        </div>

        <div class="grid grid-cols-3 gap-3 mb-6">
          <button
            v-for="tip in tipAmounts"
            :key="tip.amount"
            @click="selectedTip = tip.amount"
            :class="[
              'relative flex flex-col items-center gap-2 py-4 px-3 rounded-xl border transition-all',
              selectedTip === tip.amount
                ? `bg-gradient-to-br ${tip.color} border-transparent glow-primary`
                : 'border-border hover:border-primary/50 hover:bg-secondary/50'
            ]"
          >
            <component :is="tip.icon" :class="['w-6 h-6', selectedTip === tip.amount ? 'text-white' : 'text-muted-foreground']" />
            <span :class="['font-bold', selectedTip === tip.amount ? 'text-white' : '']">{{ tip.amount }} Kc</span>
          </button>
        </div>

        <button
          @click="handleSendTip"
          :disabled="!selectedTip"
          :class="[
            'w-full py-4 rounded-xl font-bold text-white transition-all flex items-center justify-center gap-2',
            selectedTip 
              ? 'bg-gradient-to-r from-primary via-pink-500 to-accent glow-primary-intense btn-premium' 
              : 'bg-secondary text-muted-foreground cursor-not-allowed'
          ]"
        >
          <Gift class="w-5 h-5" />
          {{ selectedTip ? `Poslat ${selectedTip} Kc` : 'Vyberte castku' }}
        </button>

        <button
          @click="showTipModal = false"
          class="w-full py-3 mt-3 text-muted-foreground hover:text-foreground transition-colors flex items-center justify-center gap-1"
        >
          <ChevronDown class="w-4 h-4" />
          Zavrit
        </button>
      </div>
    </div>
  </div>
</template>
