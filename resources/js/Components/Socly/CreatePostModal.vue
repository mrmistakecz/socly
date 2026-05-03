<script setup>
import { ref } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { X, Image as ImageIcon, Lock, Sparkles, Upload } from 'lucide-vue-next'

import axios from 'axios'

const emit = defineEmits(['close', 'created'])
const page = usePage()

const form = useForm({
  caption: '',
  image: null,
  is_locked: false,
  price: 100,
})

const imagePreview = ref(null)
const dragOver = ref(false)

const handleFileSelect = (e) => {
  const file = e.target.files[0]
  if (file) {
    form.image = file
    imagePreview.value = URL.createObjectURL(file)
  }
}

const handleDrop = (e) => {
  e.preventDefault()
  dragOver.value = false
  const file = e.dataTransfer.files[0]
  if (file && file.type.startsWith('image/')) {
    form.image = file
    imagePreview.value = URL.createObjectURL(file)
  }
}

const processing = ref(false)

const submit = async () => {
  if (!form.image || processing.value) return
  processing.value = true

  const formData = new FormData()
  formData.append('caption', form.caption)
  formData.append('image', form.image)
  formData.append('is_locked', form.is_locked ? 1 : 0)
  formData.append('price', form.price)

  try {
    const { data } = await axios.post('/posts', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    if (data.success && data.post) {
      emit('created', data.post)
      emit('close')
    }
  } catch (e) {
    if (e.response?.data?.errors) {
      form.errors = e.response.data.errors
    }
  }
  
  processing.value = false
}
</script>

<template>
  <div class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center bg-black/70 backdrop-blur-sm" @click.self="$emit('close')">
    <div class="w-full max-w-lg bg-background border-t sm:border border-border/50 sm:rounded-2xl overflow-hidden animate-slide-up">
      <!-- Header -->
      <div class="flex items-center justify-between p-4 border-b border-border/50">
        <h2 class="text-lg font-bold">Nový příspěvek</h2>
        <button @click="$emit('close')" class="p-2 rounded-xl hover:bg-secondary/50 transition-colors">
          <X class="w-5 h-5" />
        </button>
      </div>

      <form @submit.prevent="submit" class="p-4 space-y-4">
        <!-- Image Upload -->
        <div 
          @dragover.prevent="dragOver = true"
          @dragleave="dragOver = false"
          @drop="handleDrop"
          :class="[
            'relative border-2 border-dashed rounded-2xl transition-all overflow-hidden',
            dragOver ? 'border-primary bg-primary/5' : 'border-border/50',
            imagePreview ? 'aspect-[4/5]' : 'aspect-video'
          ]"
        >
          <img 
            v-if="imagePreview" 
            :src="imagePreview" 
            class="w-full h-full object-cover"
          />
          <div v-else class="flex flex-col items-center justify-center h-full gap-3 p-6">
            <div class="w-16 h-16 rounded-2xl bg-primary/10 flex items-center justify-center">
              <Upload class="w-8 h-8 text-primary" />
            </div>
            <p class="text-sm text-muted-foreground text-center">Přetáhněte obrázek nebo klikněte</p>
          </div>
          <input 
            type="file" 
            accept="image/*" 
            @change="handleFileSelect" 
            class="absolute inset-0 opacity-0 cursor-pointer"
          />
        </div>
        <p v-if="form.errors.image" class="text-xs text-destructive">{{ form.errors.image }}</p>

        <!-- Caption -->
        <div>
          <textarea
            v-model="form.caption"
            rows="3"
            placeholder="Napište popis..."
            class="w-full px-4 py-3 bg-secondary/50 border border-border/50 rounded-xl text-sm resize-none focus:outline-none focus:border-primary/50 focus:ring-2 focus:ring-primary/20 transition-all"
          />
          <p v-if="form.errors.caption" class="text-xs text-destructive mt-1">{{ form.errors.caption }}</p>
        </div>

        <!-- Locked Content Toggle -->
        <div class="flex items-center justify-between p-4 rounded-xl bg-secondary/30 border border-border/30">
          <div class="flex items-center gap-3">
            <Lock class="w-5 h-5 text-primary" />
            <div>
              <p class="text-sm font-medium">Exkluzivní obsah</p>
              <p class="text-xs text-muted-foreground">Pouze pro odběratele</p>
            </div>
          </div>
          <button 
            type="button"
            @click="form.is_locked = !form.is_locked"
            :class="[
              'relative w-12 h-7 rounded-full transition-colors',
              form.is_locked ? 'bg-primary' : 'bg-secondary'
            ]"
          >
            <span :class="[
              'absolute top-0.5 w-6 h-6 rounded-full bg-white shadow transition-transform',
              form.is_locked ? 'translate-x-5' : 'translate-x-0.5'
            ]" />
          </button>
        </div>

        <!-- Price (if locked) -->
        <div v-if="form.is_locked" class="flex items-center gap-3">
          <span class="text-sm text-muted-foreground">Cena:</span>
          <div class="flex gap-2">
            <button 
              v-for="p in [50, 100, 150, 200, 300]" 
              :key="p" 
              type="button"
              @click="form.price = p"
              :class="[
                'px-3 py-1.5 rounded-lg text-sm font-medium transition-all',
                form.price === p 
                  ? 'bg-primary text-white' 
                  : 'bg-secondary/50 text-muted-foreground hover:bg-secondary'
              ]"
            >
              {{ p }} Kč
            </button>
          </div>
        </div>

        <!-- Submit -->
        <button
          type="submit"
          :disabled="processing || !form.image"
          class="w-full py-4 rounded-xl bg-gradient-to-r from-primary via-pink-500 to-accent text-white font-bold btn-premium glow-primary transition-all hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="processing">Nahrávání...</span>
          <span v-else class="flex items-center justify-center gap-2">
            <Sparkles class="w-5 h-5" />
            Publikovat
          </span>
        </button>
      </form>
    </div>
  </div>
</template>
