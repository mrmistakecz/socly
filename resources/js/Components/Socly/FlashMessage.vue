<script setup>
import { ref, watch, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { CheckCircle, XCircle, X } from 'lucide-vue-next'

const page = usePage()
const show = ref(false)
const message = ref('')
const type = ref('success')

const showFlash = (msg, t) => {
  message.value = msg
  type.value = t
  show.value = true
  setTimeout(() => show.value = false, 4000)
}

watch(() => page.props.flash, (flash) => {
  if (flash?.success) showFlash(flash.success, 'success')
  if (flash?.error) showFlash(flash.error, 'error')
}, { deep: true })

onMounted(() => {
  if (page.props.flash?.success) showFlash(page.props.flash.success, 'success')
  if (page.props.flash?.error) showFlash(page.props.flash.error, 'error')
})
</script>

<template>
  <Transition
    enter-active-class="transition-all duration-300 ease-out"
    enter-from-class="translate-y-full opacity-0"
    enter-to-class="translate-y-0 opacity-100"
    leave-active-class="transition-all duration-200 ease-in"
    leave-from-class="translate-y-0 opacity-100"
    leave-to-class="translate-y-full opacity-0"
  >
    <div 
      v-if="show"
      class="fixed bottom-24 left-4 right-4 z-[200] flex justify-center lg:bottom-8"
    >
      <div :class="[
        'flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-xl max-w-md w-full border',
        type === 'success' 
          ? 'bg-green-500/10 border-green-500/30 text-green-400' 
          : 'bg-destructive/10 border-destructive/30 text-destructive'
      ]">
        <CheckCircle v-if="type === 'success'" class="w-5 h-5 flex-shrink-0" />
        <XCircle v-else class="w-5 h-5 flex-shrink-0" />
        <span class="text-sm font-medium flex-1">{{ message }}</span>
        <button @click="show = false" class="p-1 rounded-lg hover:bg-white/10 transition-colors">
          <X class="w-4 h-4" />
        </button>
      </div>
    </div>
  </Transition>
</template>
