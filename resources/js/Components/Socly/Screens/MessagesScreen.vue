<script setup>
import { ref, computed, nextTick, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { Search, CheckCheck, Image as ImageIcon, Video, Sparkles, Crown, BadgeCheck, ChevronRight, Send, ArrowLeft, MoreVertical, Edit3, Trash2, Smile, Paperclip, Mic } from 'lucide-vue-next'
import axios from 'axios'
import { useRealtime } from '@/composables/useRealtime'

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
  
  // Add click outside listener for emoji picker
  document.addEventListener('click', handleClickOutside)
})

const handleClickOutside = (event) => {
  if (showEmojiPicker.value && !event.target.closest('.emoji-picker-container')) {
    showEmojiPicker.value = false
  }
}

const { sendTyping, stopTyping, isUserTyping, onlineUsers } = useRealtime()

const searchFilter = ref('')
const selectedConv = ref(null)
const chatMessages = ref([])
const chatLoading = ref(false)
const newMessage = ref('')
const chatContainer = ref(null)
const isTyping = ref(false)
const showEmojiPicker = ref(false)
const editingMessage = ref(null)
const messageActions = ref(null)
const uploadProgress = ref(0)
const isUploading = ref(false)
const fileInput = ref(null)
const messageSearch = ref('')
const showSearchResults = ref(false)
const searchResults = ref([])

const filteredConversations = computed(() => {
  if (!searchFilter.value) return props.conversations
  const q = searchFilter.value.toLowerCase()
  return props.conversations.filter(c => c.name.toLowerCase().includes(q))
})

const totalUnread = computed(() => props.conversations.reduce((sum, c) => sum + c.unread, 0))

const onlineConversations = computed(() => props.conversations.filter(c => onlineUsers.value.includes(c.id)))

const isCurrentlyTyping = computed(() => {
  return selectedConv.value && isUserTyping(selectedConv.value.id, selectedConv.value.id)
})

const filteredMessages = computed(() => {
  if (!messageSearch.value.trim()) return chatMessages.value
  
  const searchQuery = messageSearch.value.toLowerCase()
  return chatMessages.value.filter(msg => 
    msg.body.toLowerCase().includes(searchQuery)
  )
})

const searchMessages = () => {
  if (!messageSearch.value.trim()) {
    showSearchResults.value = false
    searchResults.value = []
    return
  }
  
  const searchQuery = messageSearch.value.toLowerCase()
  searchResults.value = chatMessages.value.filter(msg => 
    msg.body.toLowerCase().includes(searchQuery)
  )
  showSearchResults.value = true
}

const clearSearch = () => {
  messageSearch.value = ''
  showSearchResults.value = false
  searchResults.value = []
}

const lastSeenStatus = computed(() => {
  if (!selectedConv.value) return null
  if (onlineUsers.value.includes(selectedConv.value.id)) {
    return 'Online'
  }
  
  // For now, show a generic last seen message
  // In a real implementation, you'd fetch this from the API
  return 'Nedávno online'
})

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
  editingMessage.value = null
  stopTyping(selectedConv.value?.id)
  // Clear the search param ?chat=... if present
  if (window.location.search.includes('chat=')) {
    router.replace('/?tab=messages')
  }
}

const scrollToBottom = () => {
  if (chatContainer.value) {
    chatContainer.value.scrollTop = chatContainer.value.scrollHeight
  }
}

const handleTyping = () => {
  if (!selectedConv.value || !newMessage.value.trim()) return
  
  if (!isTyping.value) {
    isTyping.value = true
    sendTyping(selectedConv.value.id, selectedConv.value.id)
  }
  
  // Reset typing timeout
  clearTimeout(window.typingTimeout)
  window.typingTimeout = setTimeout(() => {
    isTyping.value = false
    stopTyping(selectedConv.value.id)
  }, 1000)
}

const sendMessage = async () => {
  if (!newMessage.value.trim() || !selectedConv.value) return
  const body = newMessage.value.trim()
  newMessage.value = ''
  isTyping.value = false
  stopTyping(selectedConv.value.id)
  
  if (editingMessage.value) {
    // Edit existing message
    const msgIndex = chatMessages.value.findIndex(m => m.id === editingMessage.value.id)
    if (msgIndex !== -1) {
      chatMessages.value[msgIndex].body = body
      chatMessages.value[msgIndex].edited = true
    }
    
    try {
      await axios.put(`/messages/${editingMessage.value.id}`, { body })
    } catch {}
    
    editingMessage.value = null
    return
  }
  
  // Send new message
  const tempId = Date.now()
  chatMessages.value.push({
    id: tempId,
    body,
    isOwn: true,
    time: new Date().toLocaleTimeString('cs', { hour: '2-digit', minute: '2-digit' }),
    date: '',
    status: 'sending',
  })
  await nextTick()
  scrollToBottom()

  try {
    const { data } = await axios.post('/messages', {
      receiver_id: selectedConv.value.id,
      body,
    })
    
    // Update message with real ID and delivered status
    const msgIndex = chatMessages.value.findIndex(m => m.id === tempId)
    if (msgIndex !== -1) {
      chatMessages.value[msgIndex] = {
        ...chatMessages.value[msgIndex],
        id: data.message.id,
        status: 'delivered'
      }
    }
  } catch {
    // Mark as failed
    const msgIndex = chatMessages.value.findIndex(m => m.id === tempId)
    if (msgIndex !== -1) {
      chatMessages.value[msgIndex].status = 'failed'
    }
  }
}

const startEditMessage = (msg) => {
  if (!msg.isOwn) return
  editingMessage.value = msg
  newMessage.value = msg.body
  nextTick(() => {
    // Focus input
    const input = document.querySelector('input[type="text"]')
    if (input) input.focus()
  })
}

const deleteMessage = async (msg) => {
  if (!msg.isOwn) return
  
  try {
    await axios.delete(`/messages/${msg.id}`)
    chatMessages.value = chatMessages.value.filter(m => m.id !== msg.id)
  } catch {}
}

const cancelEdit = () => {
  editingMessage.value = null
  newMessage.value = ''
}

const handleFileUpload = async (event) => {
  const file = event.target.files[0]
  if (!file || !selectedConv.value) return
  
  // Validate file size (max 10MB)
  if (file.size > 10 * 1024 * 1024) {
    alert('Soubor je příliš velký. Maximální velikost je 10MB.')
    return
  }
  
  // Validate file type
  const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/webm', 'application/pdf']
  if (!allowedTypes.includes(file.type)) {
    alert('Nepodporovaný typ souboru.')
    return
  }
  
  isUploading.value = true
  uploadProgress.value = 0
  
  const formData = new FormData()
  formData.append('file', file)
  formData.append('receiver_id', selectedConv.value.id)
  if (newMessage.value.trim()) {
    formData.append('body', newMessage.value.trim())
    newMessage.value = ''
  }
  
  try {
    const response = await axios.post('/messages/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
      onUploadProgress: (progressEvent) => {
        uploadProgress.value = Math.round(
          (progressEvent.loaded * 100) / progressEvent.total
        )
      },
    })
    
    // Add the uploaded message to chat
    chatMessages.value.push({
      id: response.data.message.id,
      body: response.data.message.body,
      media: response.data.message.media,
      mediaType: file.type.startsWith('image/') ? 'image' : file.type.startsWith('video/') ? 'video' : 'file',
      isOwn: true,
      time: new Date().toLocaleTimeString('cs', { hour: '2-digit', minute: '2-digit' }),
      date: '',
      status: 'delivered',
    })
    
    await nextTick()
    scrollToBottom()
  } catch (error) {
    console.error('Upload failed:', error)
    alert('Nahrávání selhalo. Zkuste to prosím znovu.')
  } finally {
    isUploading.value = false
    uploadProgress.value = 0
    // Clear file input
    if (fileInput.value) {
      fileInput.value.value = ''
    }
  }
}

const openFileDialog = () => {
  fileInput.value?.click()
}

// Emoji picker functionality
const commonEmojis = [
  '😀', '😃', '😄', '😁', '😅', '😂', '🤣', '😊', '😇', '🙂', '😉', '😌', '😍', '🥰', '😘', '😗', '😙', '😚', '😋', '😛',
  '😜', '🤪', '😝', '🤗', '🤭', '🤫', '🤔', '🤐', '🤨', '😐', '😑', '😶', '😏', '😒', '🙄', '😬', '🤥', '😌', '😔', '😪',
  '🤤', '😴', '😷', '🤒', '🤕', '🤢', '🤮', '🤧', '🥵', '🥶', '🥴', '😵', '🤯', '🤠', '🥳', '😎', '🤓', '🧐', '😕', '😟',
  '🙁', '☹️', '😮', '😯', '😲', '😳', '🥺', '😦', '😧', '😨', '😰', '😥', '😢', '😭', '😱', '😖', '😣', '😞', '😓', '😩',
  '😫', '🥱', '😤', '😡', '😠', '🤬', '😈', '👿', '💀', '☠️', '💩', '🤡', '👹', '👺', '👻', '👽', '👾', '🤖', '❤️', '🧡',
  '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔', '❣️', '💕', '💞', '💓', '💗', '💖', '💘', '💝', '👍', '👎', '👌', '✌️',
  '🤞', '🤟', '🤘', '🤙', '👈', '👉', '👆', '👇', '☝️', '✋', '🤚', '🖐️', '🖖', '👋', '🤙', '💪', '🙏', '🎉', '🎊', '🎈'
]

const toggleEmojiPicker = () => {
  showEmojiPicker.value = !showEmojiPicker.value
}

const insertEmoji = (emoji) => {
  newMessage.value += emoji
  showEmojiPicker.value = false
  nextTick(() => {
    const input = document.querySelector('input[type="text"]')
    if (input) {
      input.focus()
      input.setSelectionRange(input.value.length, input.value.length)
    }
  })
}

// Message reactions functionality
const reactionEmojis = ['❤️', '👍', '😂', '😮', '😢', '😡']

const addReaction = async (messageId, emoji) => {
  try {
    await axios.post(`/messages/${messageId}/react`, { emoji })
    
    // Update local message reactions
    const msgIndex = chatMessages.value.findIndex(m => m.id === messageId)
    if (msgIndex !== -1) {
      const message = chatMessages.value[msgIndex]
      const existingReaction = message.reactions?.find(r => r.emoji === emoji)
      
      if (existingReaction) {
        if (existingReaction.hasReacted) {
          // Remove reaction
          await removeReaction(messageId)
        } else {
          // Update reaction count and user reaction
          existingReaction.count++
          existingReaction.hasReacted = true
          existingReaction.users.push({ id: 'current', name: 'You' })
        }
      } else {
        // Add new reaction
        if (!message.reactions) message.reactions = []
        message.reactions.push({
          emoji,
          count: 1,
          users: [{ id: 'current', name: 'You' }],
          hasReacted: true
        })
      }
    }
  } catch (error) {
    console.error('Failed to add reaction:', error)
  }
}

const removeReaction = async (messageId) => {
  try {
    await axios.delete(`/messages/${messageId}/react`)
    
    // Update local message reactions
    const msgIndex = chatMessages.value.findIndex(m => m.id === messageId)
    if (msgIndex !== -1) {
      const message = chatMessages.value[msgIndex]
      const reactionIndex = message.reactions?.findIndex(r => r.hasReacted)
      
      if (reactionIndex !== -1) {
        const reaction = message.reactions[reactionIndex]
        reaction.count--
        reaction.hasReacted = false
        reaction.users = reaction.users.filter(u => u.id !== 'current')
        
        // Remove reaction if count is 0
        if (reaction.count === 0) {
          message.reactions.splice(reactionIndex, 1)
        }
      }
    }
  } catch (error) {
    console.error('Failed to remove reaction:', error)
  }
}
</script>

<template>
  <div class="min-h-dvh flex flex-col lg:flex-row pb-32 lg:pb-0 h-dvh lg:h-screen overflow-hidden">
    <!-- Sidebar / Conversations List -->
    <div :class="['w-full lg:w-[360px] lg:border-r border-border/50 flex flex-col h-full', selectedConv ? 'hidden lg:flex' : 'flex']">
      <!-- Desktop Header -->
      <div class="hidden lg:flex items-center justify-between sticky top-0 z-30 bg-background/80 backdrop-blur-xl border-b border-border/50 px-6 py-4 flex-shrink-0">
        <div>
          <h1 class="text-2xl font-bold">Zprávy</h1>
          <p class="text-sm text-muted-foreground">Vaše konverzace s tvůrci</p>
        </div>
        <div v-if="totalUnread > 0" class="flex items-center gap-1 px-3 py-1.5 rounded-full bg-primary/10">
          <span class="text-xs font-semibold text-primary">{{ totalUnread }} {{ totalUnread === 1 ? 'nová' : 'nové' }}</span>
        </div>
      </div>

      <!-- Mobile Header -->
      <div class="lg:hidden pt-4 px-4 mb-4 flex-shrink-0">
        <div class="flex items-center justify-between mb-1">
          <h1 class="text-2xl font-bold text-foreground">Zprávy</h1>
          <div v-if="totalUnread > 0" class="flex items-center gap-1 px-2.5 py-1 rounded-full bg-primary/10">
            <span class="text-xs font-semibold text-primary">{{ totalUnread }}</span>
          </div>
        </div>
      </div>

      <!-- Scrollable List Area -->
      <div class="flex-1 overflow-y-auto px-4 lg:px-6 hide-scrollbar pb-safe">
        <!-- Search -->
        <div class="relative mb-6 mt-2">
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
            </div>
          </div>
        </div>

        <!-- Online Now -->
        <div v-if="onlineConversations.length > 0" class="mb-6">
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
              <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse" />
              <span class="text-sm font-medium text-foreground">Online</span>
            </div>
          </div>
          
          <div class="flex gap-3 overflow-x-auto hide-scrollbar -mx-4 px-4">
            <button 
              v-for="conv in onlineConversations" 
              :key="conv.id"
              @click="openConversation(conv)"
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
              :class="[
                'flex items-center gap-3 p-3 rounded-xl transition-all touch-feedback text-left group',
                selectedConv?.id === conv.id ? 'bg-secondary' : 'hover:bg-secondary/30'
              ]"
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
                <div v-if="onlineUsers.includes(conv.id)" class="absolute bottom-0 right-0 w-4 h-4 bg-green-500 rounded-full border-2 border-background" />
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
    </div>

    <!-- Chat View (Right Pane on Desktop / Full overlay on Mobile) -->
    <div :class="['flex-1 flex flex-col h-full bg-background z-50', selectedConv ? 'fixed inset-0 lg:relative' : 'hidden lg:flex']">
      
      <!-- No Selection State (Desktop) -->
      <div v-if="!selectedConv" class="hidden lg:flex flex-col items-center justify-center h-full text-center">
        <div class="w-24 h-24 rounded-full bg-secondary/50 flex items-center justify-center mb-6">
          <MessageCircle class="w-10 h-10 text-muted-foreground" />
        </div>
        <h2 class="text-xl font-bold mb-2">Vaše zprávy</h2>
        <p class="text-muted-foreground max-w-sm">Vyberte konverzaci z levého panelu nebo vytvořte novou a spojte se s tvůrci.</p>
      </div>

      <!-- Active Chat State -->
      <template v-else>
        <!-- Chat Header -->
        <div class="flex items-center gap-3 px-4 py-3 border-b border-border/50 bg-background/80 backdrop-blur-xl flex-shrink-0 pt-safe">
          <button @click="closeChat" class="lg:hidden p-2 rounded-xl hover:bg-secondary/50 transition-colors">
            <ArrowLeft class="w-5 h-5" />
          </button>
          <img :src="selectedConv.avatar || '/images/default-avatar.svg'" class="w-10 h-10 rounded-xl object-cover" />
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-1.5">
              <span class="font-semibold text-sm truncate">{{ selectedConv.name }}</span>
              <BadgeCheck v-if="selectedConv.verified" class="w-4 h-4 text-primary fill-primary/20 flex-shrink-0" />
              <div v-if="onlineUsers.includes(selectedConv.id)" class="w-2 h-2 bg-green-500 rounded-full" />
            </div>
            <p class="text-xs text-muted-foreground">
              <span v-if="onlineUsers.includes(selectedConv.id)" class="text-green-500">Online</span>
              <span v-else>{{ lastSeenStatus }}</span>
            </p>
          </div>
          <button 
            @click="showSearchResults = !showSearchResults"
            class="p-2 rounded-xl text-muted-foreground hover:bg-secondary/50 transition-colors"
          >
            <Search class="w-5 h-5" />
          </button>
        </div>
        
        <!-- Search Overlay -->
        <div v-if="showSearchResults" class="px-4 py-3 border-b border-border/50 bg-background/50 flex-shrink-0">
          <div class="flex items-center gap-2">
            <Search class="w-4 h-4 text-muted-foreground" />
            <input
              v-model="messageSearch"
              type="text"
              placeholder="Hledat v konverzaci..."
              class="flex-1 bg-transparent text-sm placeholder:text-muted-foreground outline-none"
              @input="searchMessages"
            />
            <button 
              v-if="messageSearch"
              @click="clearSearch"
              class="p-1 rounded-lg text-muted-foreground hover:bg-secondary/50 transition-colors"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          
          <!-- Search Results Count -->
          <div v-if="messageSearch" class="mt-2 text-xs text-muted-foreground">
            {{ searchResults.length }} {{ searchResults.length === 1 ? 'výsledek' : 'výsledky' }}
          </div>
        </div>

        <!-- Messages Area -->
        <div ref="chatContainer" class="flex-1 overflow-y-auto px-4 py-4 space-y-4 hide-scrollbar">
          <div v-if="chatLoading" class="flex justify-center py-8">
            <div class="w-6 h-6 border-2 border-primary border-t-transparent rounded-full animate-spin" />
          </div>

          <div v-if="!chatLoading && chatMessages.length === 0" class="flex flex-col items-center justify-center h-full text-center">
            <p class="text-sm text-muted-foreground">Zatím žádné zprávy. Napište první!</p>
          </div>

          <!-- Search Results -->
          <div v-if="showSearchResults && messageSearch">
            <div v-if="searchResults.length === 0" class="flex flex-col items-center justify-center py-8 text-center">
              <p class="text-sm text-muted-foreground">Žádné výsledky pro "{{ messageSearch }}"</p>
            </div>
            <div 
              v-for="msg in searchResults" 
              :key="msg.id"
              :class="['flex', msg.isOwn ? 'justify-end' : 'justify-start']"
            >
              <div class="flex flex-col max-w-[75%]">
                <div :class="[
                  'px-3 py-2 text-[15px] leading-relaxed shadow-sm rounded-lg',
                  msg.isOwn 
                    ? 'bg-gradient-to-br from-primary to-accent text-white' 
                    : 'bg-secondary border border-border/50'
                ]">
                  <p class="break-words">{{ msg.body }}</p>
                </div>
                <p :class="['text-[10px] mt-1 px-1', msg.isOwn ? 'text-right text-muted-foreground' : 'text-left text-muted-foreground']">
                  {{ msg.time }}
                </p>
              </div>
            </div>
          </div>

          <!-- Regular Messages -->
          <div v-else
            v-for="(msg, index) in chatMessages" 
            :key="msg.id"
            :class="['flex', msg.isOwn ? 'justify-end' : 'justify-start']"
          >
            <div class="flex flex-col max-w-[75%] group">
              <div :class="[
                'px-4 py-3 text-[15px] leading-relaxed shadow-sm relative',
                msg.isOwn 
                  ? 'bg-gradient-to-br from-primary to-accent text-white rounded-2xl rounded-br-sm' 
                  : 'bg-secondary border border-border/50 rounded-2xl rounded-bl-sm'
              ]">
                <!-- Media Content -->
                <div v-if="msg.media" class="mb-3">
                  <!-- Image -->
                  <div v-if="msg.mediaType === 'image'" class="rounded-lg overflow-hidden">
                    <img 
                      :src="msg.media" 
                      :alt="msg.body || 'Obrázek'"
                      class="w-full max-w-sm object-cover cursor-pointer hover:opacity-90 transition-opacity"
                      @click="$emit('openImage', msg.media)"
                    />
                  </div>
                  
                  <!-- Video -->
                  <div v-else-if="msg.mediaType === 'video'" class="rounded-lg overflow-hidden">
                    <video 
                      :src="msg.media" 
                      controls
                      class="w-full max-w-sm rounded-lg"
                      preload="metadata"
                    />
                  </div>
                  
                  <!-- File -->
                  <div v-else class="flex items-center gap-3 p-3 bg-background/10 rounded-lg">
                    <div class="w-10 h-10 bg-background/20 rounded-lg flex items-center justify-center">
                      <Paperclip class="w-5 h-5" />
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-medium truncate">{{ msg.body || 'Soubor' }}</p>
                      <p class="text-xs opacity-70">Klikněte pro stažení</p>
                    </div>
                    <a 
                      :href="msg.media" 
                      download
                      class="p-2 rounded-lg hover:bg-background/20 transition-colors"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                      </svg>
                    </a>
                  </div>
                </div>
                
                <!-- Text Content -->
                <p v-if="msg.body && !msg.media" class="break-words">{{ msg.body }}</p>
                <p v-else-if="msg.body && msg.media" class="break-words text-sm opacity-90 mt-2">{{ msg.body }}</p>
                
                <!-- Message Actions -->
                <div v-if="msg.isOwn" class="absolute -top-2 -right-2 opacity-0 group-hover:opacity-100 transition-opacity flex gap-1">
                  <button 
                    @click="startEditMessage(msg)"
                    class="p-1.5 bg-background border border-border/50 rounded-lg shadow-sm hover:bg-secondary/50 transition-colors"
                  >
                    <Edit3 class="w-3 h-3" />
                  </button>
                  <button 
                    @click="deleteMessage(msg)"
                    class="p-1.5 bg-background border border-border/50 rounded-lg shadow-sm hover:bg-destructive/10 text-destructive transition-colors"
                  >
                    <Trash2 class="w-3 h-3" />
                  </button>
                </div>
              </div>
              
              <!-- Message Status and Time -->
              <div :class="['flex items-center gap-1.5 mt-1.5 px-1', msg.isOwn ? 'justify-end' : 'justify-start']">
                <span class="text-[10px] text-muted-foreground">{{ msg.time }}</span>
                
                <!-- Delivery Status for Own Messages -->
                <div v-if="msg.isOwn && msg.status" class="flex items-center gap-1">
                  <div v-if="msg.status === 'sending'" class="w-3 h-3">
                    <div class="w-2 h-2 border border-current border-t-transparent rounded-full animate-spin" />
                  </div>
                  <div v-else-if="msg.status === 'delivered'" class="w-3 h-3 text-muted-foreground">
                    <CheckCheck class="w-full h-full" />
                  </div>
                  <div v-else-if="msg.status === 'read'" class="w-3 h-3 text-primary">
                    <CheckCheck class="w-full h-full" />
                  </div>
                  <div v-else-if="msg.status === 'failed'" class="w-3 h-3 text-destructive">
                    <div class="w-full h-full rounded-full bg-current" />
                  </div>
                </div>
                
                <!-- Edited Indicator -->
                <span v-if="msg.edited" class="text-[10px] text-muted-foreground italic">upraveno</span>
              </div>
              
              <!-- Reactions -->
              <div v-if="msg.reactions && msg.reactions.length > 0" class="flex flex-wrap gap-1 mt-2 px-1">
                <div
                  v-for="reaction in msg.reactions"
                  :key="reaction.emoji"
                  :class="[
                    'flex items-center gap-1 px-2 py-1 rounded-full text-xs transition-all cursor-pointer',
                    reaction.hasReacted
                      ? 'bg-primary/20 text-primary border border-primary/30'
                      : 'bg-secondary/50 hover:bg-secondary/70 border border-border/30'
                  ]"
                  @click="reaction.hasReacted ? removeReaction(msg.id) : addReaction(msg.id, reaction.emoji)"
                >
                  <span>{{ reaction.emoji }}</span>
                  <span>{{ reaction.count }}</span>
                </div>
              </div>
              
              <!-- Reaction Bar -->
              <div v-if="!msg.isOwn" class="flex items-center gap-1 mt-2 px-1 opacity-0 group-hover:opacity-100 transition-opacity">
                <button
                  v-for="emoji in reactionEmojis"
                  :key="emoji"
                  @click="addReaction(msg.id, emoji)"
                  class="p-1.5 rounded-lg hover:bg-secondary/50 transition-colors text-sm"
                  :title="'Reagovat s ' + emoji"
                >
                  {{ emoji }}
                </button>
              </div>
            </div>
          </div>
          
          <!-- Typing Indicator -->
          <div v-if="isCurrentlyTyping" class="flex justify-start">
            <div class="flex flex-col max-w-[75%]">
              <div class="px-4 py-3 bg-secondary border border-border/50 rounded-2xl rounded-bl-sm">
                <div class="flex items-center gap-1">
                  <div class="flex gap-1">
                    <div class="w-2 h-2 bg-muted-foreground rounded-full animate-bounce" style="animation-delay: 0ms" />
                    <div class="w-2 h-2 bg-muted-foreground rounded-full animate-bounce" style="animation-delay: 150ms" />
                    <div class="w-2 h-2 bg-muted-foreground rounded-full animate-bounce" style="animation-delay: 300ms" />
                  </div>
                  <span class="text-xs text-muted-foreground ml-2">píše...</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Input Area -->
        <div class="px-4 py-3 border-t border-border/50 bg-background flex-shrink-0 pb-safe">
          <!-- Edit Mode Indicator -->
          <div v-if="editingMessage" class="flex items-center justify-between mb-2 px-2">
            <span class="text-xs text-muted-foreground">Upravujete zprávu</span>
            <button @click="cancelEdit" class="text-xs text-muted-foreground hover:text-foreground">
              Zrušit
            </button>
          </div>
          
          <div class="flex items-center gap-2">
            <button 
              @click="openFileDialog"
              :disabled="isUploading"
              class="p-2.5 rounded-xl text-muted-foreground hover:bg-secondary/50 hover:text-foreground transition-all disabled:opacity-50"
            >
              <Paperclip class="w-5 h-5" />
            </button>
            <input
              ref="fileInput"
              type="file"
              accept="image/*,video/*,.pdf"
              @change="handleFileUpload"
              class="hidden"
            />
            <div class="flex-1 relative emoji-picker-container">
              <input
                v-model="newMessage"
                type="text"
                :placeholder="editingMessage ? 'Upravit zprávu...' : 'Napište zprávu...'"
                :disabled="isUploading"
                class="w-full pl-4 pr-10 py-3 bg-secondary/50 border border-border/50 rounded-xl text-sm placeholder:text-muted-foreground focus:outline-none focus:border-primary/50 focus:bg-secondary/80 transition-all disabled:opacity-50"
                @input="handleTyping"
                @keydown.enter="sendMessage"
              />
              <button 
                @click="toggleEmojiPicker"
                class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 rounded-lg text-muted-foreground hover:bg-secondary/50 transition-colors"
              >
                <Smile class="w-4 h-4" />
              </button>
            </div>
            <button class="p-2.5 rounded-xl text-muted-foreground hover:bg-secondary/50 hover:text-foreground transition-all">
              <Mic class="w-5 h-5" />
            </button>
            <button 
              @click="sendMessage" 
              :disabled="!newMessage.trim() || isUploading"
              class="p-3 rounded-xl bg-primary text-white hover:bg-primary/90 transition-all disabled:opacity-40 disabled:scale-95 active:scale-95"
            >
              <Send class="w-5 h-5" />
            </button>
          </div>
          
          <!-- Upload Progress -->
          <div v-if="isUploading" class="mt-2">
            <div class="flex items-center gap-2">
              <div class="flex-1 bg-secondary rounded-full h-2 overflow-hidden">
                <div 
                  class="bg-primary h-full transition-all duration-300 ease-out"
                  :style="{ width: uploadProgress + '%' }"
                />
              </div>
              <span class="text-xs text-muted-foreground">{{ uploadProgress }}%</span>
            </div>
            <p class="text-xs text-muted-foreground mt-1">Nahrávám soubor...</p>
          </div>
          
          <!-- Emoji Picker -->
          <div v-if="showEmojiPicker" class="mt-2 p-3 bg-background border border-border/50 rounded-xl shadow-lg">
            <div class="grid grid-cols-8 gap-1 max-h-48 overflow-y-auto">
              <button
                v-for="emoji in commonEmojis"
                :key="emoji"
                @click="insertEmoji(emoji)"
                class="p-2 text-lg hover:bg-secondary/50 rounded-lg transition-colors touch-feedback"
              >
                {{ emoji }}
              </button>
            </div>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>
