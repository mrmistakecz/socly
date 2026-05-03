<script setup>
import { ref, computed, nextTick, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { Search, CheckCheck, Image as ImageIcon, Video, Sparkles, Crown, BadgeCheck, ChevronRight, Send, ArrowLeft } from 'lucide-vue-next'
import axios from 'axios'

const props = defineProps({
  conversations: { type: Array, default: () => [] },
  pendingChatUserId: { type: Number, default: null },
  incomingMessage: { type: Object, default: null },
})

watch(() => props.incomingMessage, async (msg) => {
  if (!msg || !selectedConv.value) return
  if (msg.sender_id === selectedConv.value.id) {
    chatMessages.value.push({
      id: msg.id,
      body: msg.body,
      isOwn: false,
      time: new Date(msg.created_at).toLocaleTimeString('cs', { hour: '2-digit', minute: '2-digit' }),
      date: '',
    })
    await nextTick()
    scrollToBottom()
    axios.post(`/messages/${selectedConv.value.id}/read`)
  }
})

onMounted(() => {
  if (props.pendingChatUserId) {
    const conv = props.conversations.find(c => c.id === props.pendingChatUserId)
    if (conv) {
      openConversation(conv)
    } else {
      openConversation({
        id: props.pendingChatUserId,
        name: 'Nová konverzace',
        username: '',
        avatar: null,
        verified: false,
      })
    }
  }
})

const searchFilter = ref('')
const selectedConv = ref(null)
const chatMessages = ref([])
const chatLoading = ref(false)
const newMessage = ref('')
const chatContainer = ref(null)

const filteredConversations = computed(() => {
  if (!searchFilter.value) return props.conversations
  const q = searchFilter.value.toLowerCase()
  return props.conversations.filter(c => c.name.toLowerCase().includes(q))
})

const totalUnread = computed(() => props.conversations.reduce((sum, c) => sum + c.unread, 0))

const onlineConversations = computed(() => props.conversations.filter(c => c.isOnline))

const openConversation = async (conv) => {
  selectedConv.value = conv
  chatLoading.value = true
  try {
    const { data } = await axios.get(`/messages/${conv.id}`)
    chatMessages.value = data.messages || []
    await nextTick()
    scrollToBottom()
  } catch { chatMessages.value = [] }
  chatLoading.value = false
}

const closeChat = () => {
  selectedConv.value = null
  chatMessages.value = []
  router.reload({ only: ['conversations'], preserveScroll: true })
}

const scrollToBottom = () => {
  if (chatContainer.value) {
    chatContainer.value.scrollTop = chatContainer.value.scrollHeight
  }
}

const sendMessage = async () => {
  if (!newMessage.value.trim() || !selectedConv.value) return
  const body = newMessage.value.trim()
  newMessage.value = ''
  
  chatMessages.value.push({
    id: Date.now(),
    body,
    isOwn: true,
    time: new Date().toLocaleTimeString('cs', { hour: '2-digit', minute: '2-digit' }),
    date: '',
  })
  await nextTick()
  scrollToBottom()

  router.post('/messages', {
    receiver_id: selectedConv.value.id,
    body,
  }, {
    preserveScroll: true,
    preserveState: true,
  })
}
</script>

<template>
  <div class="min-h-dvh pb-32 lg:pb-8">
    <!-- Desktop Header -->
    <div class="hidden lg:block sticky top-0 z-30 bg-background/80 backdrop-blur-xl border-b border-border/50 px-6 py-4">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold">Zprávy</h1>
          <p class="text-sm text-muted-foreground">Vaše konverzace s tvůrci</p>
        </div>
        <div v-if="totalUnread > 0" class="flex items-center gap-1 px-3 py-1.5 rounded-full bg-primary/10">
          <span class="text-xs font-semibold text-primary">{{ totalUnread }} {{ totalUnread === 1 ? 'nová' : 'nové' }}</span>
        </div>
      </div>
    </div>

    <!-- Mobile Header -->
    <div class="lg:hidden pt-20 px-4 mb-6">
      <div class="flex items-center justify-between mb-1">
        <h1 class="text-2xl font-bold text-foreground">Zprávy</h1>
        <div v-if="totalUnread > 0" class="flex items-center gap-1 px-2.5 py-1 rounded-full bg-primary/10">
          <span class="text-xs font-semibold text-primary">{{ totalUnread }} {{ totalUnread === 1 ? 'nová' : 'nové' }}</span>
        </div>
      </div>
      <p class="text-sm text-muted-foreground">Vaše konverzace s tvůrci</p>
    </div>

    <div class="px-4 lg:px-6">

    <!-- Search -->
    <div class="relative mb-6">
      <div class="flex items-center glass-card rounded-2xl px-4 py-3.5 focus-within:ring-2 focus-within:ring-primary/50 transition-all">
        <Search class="w-5 h-5 text-muted-foreground mr-3" />
        <input
          v-model="searchFilter"
          type="text"
          placeholder="Hledat v konverzacích..."
          class="flex-1 bg-transparent text-foreground placeholder-muted-foreground text-sm outline-none"
        />
      </div>
    </div>

    <!-- VIP Banner -->
    <div class="relative overflow-hidden rounded-2xl mb-6 touch-feedback group">
      <div class="absolute inset-0 bg-gradient-to-r from-primary/20 via-purple-500/20 to-accent/20" />
      <div class="absolute inset-0 glass-card" />
      <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-primary/40 to-accent/40 rounded-full blur-3xl animate-pulse" />
      
      <div class="relative p-5">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <div class="flex items-center gap-2 mb-2">
              <Crown class="w-5 h-5 text-gold" />
              <h3 class="font-bold text-foreground">VIP Zprávy</h3>
            </div>
            <p class="text-sm text-muted-foreground mb-4 max-w-[200px]">
              Odemkněte prioritní přístup k odpovědím tvůrců
            </p>
            <button class="flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-primary to-accent rounded-xl text-sm font-bold text-white neon-glow hover:scale-105 transition-transform">
              <Sparkles class="w-4 h-4" />
              Upgradovat
            </button>
          </div>
          
          <div class="flex -space-x-3">
            <div 
              v-for="(conv, i) in conversations.slice(0, 3)" 
              :key="conv.id"
              class="w-12 h-12 rounded-full border-2 border-background overflow-hidden"
              :style="{ zIndex: 3 - i }"
            >
              <img
                :src="conv.avatar || '/images/default-avatar.svg'"
                alt=""
                class="w-full h-full object-cover"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Online Now -->
    <div class="mb-6">
      <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
          <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse" />
          <span class="text-sm font-medium text-foreground">Online</span>
        </div>
        <button class="flex items-center gap-1 text-xs text-primary touch-feedback">
          Všichni
          <ChevronRight class="w-3 h-3" />
        </button>
      </div>
      
      <div class="flex gap-3 overflow-x-auto hide-scrollbar -mx-4 px-4">
        <button 
          v-for="conv in onlineConversations" 
          :key="conv.id"
          class="flex flex-col items-center gap-2 flex-shrink-0 touch-feedback"
        >
          <div class="relative">
            <div :class="[
              'w-16 h-16 rounded-full p-[2px]',
              conv.unread > 0 
                ? 'bg-gradient-to-br from-primary via-purple-500 to-accent animate-pulse-glow' 
                : 'bg-gradient-to-br from-primary/30 to-accent/30'
            ]">
              <div class="w-full h-full rounded-full overflow-hidden bg-background p-[2px]">
                <div class="w-full h-full rounded-full overflow-hidden">
                  <img
                    :src="conv.avatar || '/images/default-avatar.svg'"
                    :alt="conv.name"
                    class="w-full h-full object-cover"
                  />
                </div>
              </div>
            </div>
            <div class="absolute bottom-0.5 right-0.5 w-4 h-4 bg-green-500 rounded-full border-2 border-background" />
            <div v-if="conv.unread > 0" class="absolute -top-1 -right-1 min-w-[20px] h-5 px-1.5 rounded-full bg-gradient-to-br from-primary to-accent flex items-center justify-center">
              <span class="text-[10px] font-bold text-white">{{ conv.unread }}</span>
            </div>
          </div>
          <span class="text-xs text-muted-foreground truncate w-16 text-center">
            {{ conv.name.split(' ')[0] }}
          </span>
        </button>
      </div>
    </div>

    <!-- Conversations List -->
    <div>
      <h3 class="text-sm font-medium text-muted-foreground mb-3">Všechny zprávy</h3>
      <div class="flex flex-col gap-1">
        <button
          v-for="conv in filteredConversations"
          :key="conv.id"
          @click="openConversation(conv)"
          class="flex items-center gap-3 p-3 rounded-xl hover:bg-secondary/30 transition-all touch-feedback text-left group"
        >
          <div class="relative flex-shrink-0">
            <div :class="[
              'w-14 h-14 rounded-xl p-[2px]',
              conv.unread > 0 
                ? 'bg-gradient-to-br from-primary to-accent' 
                : 'bg-secondary'
            ]">
              <div class="w-full h-full rounded-xl overflow-hidden bg-background p-[1px]">
                <div class="w-full h-full rounded-xl overflow-hidden">
                  <img
                    :src="conv.avatar || '/images/default-avatar.svg'"
                    :alt="conv.name"
                    class="w-full h-full object-cover"
                  />
                </div>
              </div>
            </div>
            <div v-if="conv.isOnline" class="absolute bottom-0 right-0 w-4 h-4 bg-green-500 rounded-full border-2 border-background" />
          </div>

          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-1.5 mb-1">
              <Crown v-if="conv.isVIP" class="w-3.5 h-3.5 text-gold flex-shrink-0" />
              <span :class="[
                'font-semibold truncate',
                conv.unread > 0 ? 'text-foreground' : 'text-foreground/90'
              ]">
                {{ conv.name }}
              </span>
              <BadgeCheck v-if="conv.verified" class="w-4 h-4 text-primary fill-primary/20 flex-shrink-0" />
            </div>
            <div class="flex items-center gap-1.5">
              <ImageIcon v-if="conv.hasMedia" class="w-3.5 h-3.5 text-muted-foreground flex-shrink-0" />
              <Video v-if="conv.hasVideo" class="w-3.5 h-3.5 text-muted-foreground flex-shrink-0" />
              <p :class="[
                'text-sm truncate',
                conv.unread > 0 ? 'text-foreground font-medium' : 'text-muted-foreground'
              ]">
                {{ conv.lastMessage }}
              </p>
            </div>
          </div>

          <div class="flex flex-col items-end gap-1.5 flex-shrink-0">
            <span :class="[
              'text-xs',
              conv.unread > 0 ? 'text-primary font-medium' : 'text-muted-foreground'
            ]">
              {{ conv.time }}
            </span>
            <div v-if="conv.unread > 0" class="min-w-[20px] h-5 px-1.5 rounded-full bg-gradient-to-r from-primary to-accent flex items-center justify-center">
              <span class="text-[10px] font-bold text-white">{{ conv.unread }}</span>
            </div>
            <CheckCheck v-else class="w-4 h-4 text-primary" />
          </div>
        </button>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="!filteredConversations.length" class="flex flex-col items-center justify-center py-16 text-center">
      <div class="w-16 h-16 rounded-2xl bg-secondary/50 flex items-center justify-center mb-4">
        <Search class="w-8 h-8 text-muted-foreground" />
      </div>
      <p class="text-lg font-semibold mb-1">Žádné konverzace</p>
      <p class="text-sm text-muted-foreground">Sledujte tvůrce a začněte konverzaci</p>
    </div>

    </div>

    <!-- Chat View (Full Screen Overlay) -->
    <Transition
      enter-active-class="transition-all duration-200 ease-out"
      enter-from-class="translate-x-full"
      enter-to-class="translate-x-0"
      leave-active-class="transition-all duration-200 ease-in"
      leave-from-class="translate-x-0"
      leave-to-class="translate-x-full"
    >
    <div v-if="selectedConv" class="fixed inset-0 z-50 bg-background flex flex-col lg:static lg:inset-auto lg:z-auto">
      <!-- Chat Header -->
      <div class="flex items-center gap-3 px-4 py-3 border-b border-border/50 bg-background/80 backdrop-blur-xl">
        <button @click="closeChat" class="p-2 rounded-xl hover:bg-secondary/50 transition-colors">
          <ArrowLeft class="w-5 h-5" />
        </button>
        <img :src="selectedConv.avatar || '/images/default-avatar.svg'" class="w-10 h-10 rounded-xl object-cover" />
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-1.5">
            <span class="font-semibold text-sm truncate">{{ selectedConv.name }}</span>
            <BadgeCheck v-if="selectedConv.verified" class="w-4 h-4 text-primary fill-primary/20 flex-shrink-0" />
          </div>
          <p class="text-xs text-muted-foreground">@{{ selectedConv.username }}</p>
        </div>
      </div>

      <!-- Messages -->
      <div ref="chatContainer" class="flex-1 overflow-y-auto px-4 py-4 space-y-3 hide-scrollbar">
        <div v-if="chatLoading" class="flex justify-center py-8">
          <div class="w-6 h-6 border-2 border-primary border-t-transparent rounded-full animate-spin" />
        </div>

        <div v-if="!chatLoading && chatMessages.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
          <p class="text-sm text-muted-foreground">Zatím žádné zprávy. Napište první!</p>
        </div>

        <div 
          v-for="msg in chatMessages" 
          :key="msg.id"
          :class="['flex', msg.isOwn ? 'justify-end' : 'justify-start']"
        >
          <div :class="[
            'max-w-[75%] px-4 py-2.5 rounded-2xl text-sm leading-relaxed',
            msg.isOwn 
              ? 'bg-primary text-white rounded-br-md' 
              : 'bg-secondary/50 border border-border/30 rounded-bl-md'
          ]">
            <p>{{ msg.body }}</p>
            <p :class="['text-[10px] mt-1', msg.isOwn ? 'text-white/60' : 'text-muted-foreground']">{{ msg.time }}</p>
          </div>
        </div>
      </div>

      <!-- Input -->
      <div class="px-4 py-3 border-t border-border/50 bg-background pb-safe">
        <div class="flex items-center gap-3">
          <input
            v-model="newMessage"
            type="text"
            :placeholder="`Napište zprávu...`"
            class="flex-1 px-4 py-3 bg-secondary/50 border border-border/50 rounded-xl text-sm placeholder:text-muted-foreground focus:outline-none focus:border-primary/50 transition-all"
            @keydown.enter="sendMessage"
          />
          <button 
            @click="sendMessage" 
            :disabled="!newMessage.trim()"
            class="p-3 rounded-xl bg-primary text-white hover:bg-primary/90 transition-all disabled:opacity-40"
          >
            <Send class="w-5 h-5" />
          </button>
        </div>
      </div>
    </div>
    </Transition>
  </div>
</template>
