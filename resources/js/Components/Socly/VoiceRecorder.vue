<template>
  <div class="flex items-center gap-2">
    <!-- Record button -->
    <button
      v-if="!isRecording && !audioBlob"
      @click="startRecording"
      class="w-10 h-10 rounded-full bg-muted flex items-center justify-center hover:bg-primary/20 transition"
      title="Nahrát hlasovou zprávu"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
      </svg>
    </button>

    <!-- Recording in progress -->
    <div v-if="isRecording" class="flex items-center gap-2">
      <div class="w-3 h-3 rounded-full bg-red-500 animate-pulse" />
      <span class="text-sm text-red-400 font-mono">{{ formattedDuration }}</span>
      <button @click="stopRecording" class="text-xs text-muted-foreground hover:text-foreground px-2 py-1 rounded-lg border border-border transition">
        Zastavit
      </button>
      <button @click="cancelRecording" class="text-xs text-destructive hover:opacity-70 transition">Zrušit</button>
    </div>

    <!-- Recorded preview -->
    <div v-if="audioBlob && !isRecording" class="flex items-center gap-2">
      <audio :src="audioUrl" controls class="h-8 max-w-[180px]" />
      <button @click="sendVoice" :disabled="sending" class="text-xs bg-primary text-primary-foreground rounded-lg px-3 py-1.5 font-medium disabled:opacity-50 transition">
        {{ sending ? '...' : 'Odeslat' }}
      </button>
      <button @click="cancelRecording" class="text-xs text-muted-foreground hover:text-foreground transition">Zrušit</button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onUnmounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  receiverId: { type: Number, required: true },
})

const emit = defineEmits(['sent'])

const isRecording = ref(false)
const audioBlob = ref(null)
const audioUrl = ref(null)
const sending = ref(false)
const duration = ref(0)

let mediaRecorder = null
let chunks = []
let timer = null

const formattedDuration = computed(() => {
  const m = Math.floor(duration.value / 60).toString().padStart(2, '0')
  const s = (duration.value % 60).toString().padStart(2, '0')
  return `${m}:${s}`
})

const startRecording = async () => {
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ audio: true })
    chunks = []
    mediaRecorder = new MediaRecorder(stream, { mimeType: 'audio/webm' })
    mediaRecorder.ondataavailable = (e) => chunks.push(e.data)
    mediaRecorder.onstop = () => {
      audioBlob.value = new Blob(chunks, { type: 'audio/webm' })
      audioUrl.value = URL.createObjectURL(audioBlob.value)
      stream.getTracks().forEach(t => t.stop())
    }
    mediaRecorder.start()
    isRecording.value = true
    duration.value = 0
    timer = setInterval(() => duration.value++, 1000)
  } catch {
    alert('Nelze získat přístup k mikrofonu.')
  }
}

const stopRecording = () => {
  if (mediaRecorder && mediaRecorder.state !== 'inactive') {
    mediaRecorder.stop()
  }
  clearInterval(timer)
  isRecording.value = false
}

const cancelRecording = () => {
  stopRecording()
  audioBlob.value = null
  audioUrl.value = null
  duration.value = 0
}

const sendVoice = async () => {
  if (!audioBlob.value) return
  sending.value = true
  const form = new FormData()
  form.append('receiver_id', props.receiverId)
  form.append('file', audioBlob.value, 'voice.webm')
  try {
    const { data } = await axios.post('/messages/voice', form, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    emit('sent', data.message)
    cancelRecording()
  } catch (e) {
    alert(e.response?.data?.error || 'Nepodařilo se odeslat hlasovou zprávu.')
  } finally {
    sending.value = false
  }
}

onUnmounted(() => {
  clearInterval(timer)
  if (audioUrl.value) URL.revokeObjectURL(audioUrl.value)
})
</script>
