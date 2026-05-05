<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/70 px-4" @click.self="$emit('cancel')">
        <div class="bg-card border border-border rounded-2xl w-full max-w-sm p-6 space-y-4" role="dialog" aria-modal="true" :aria-label="title">
          <h3 class="text-lg font-bold">{{ title }}</h3>
          <p class="text-sm text-muted-foreground">{{ message }}</p>
          <div class="flex gap-3">
            <button @click="$emit('cancel')" class="flex-1 py-3 rounded-xl border border-border text-sm font-medium hover:bg-secondary/50 transition-all">
              Zrušit
            </button>
            <button @click="$emit('confirm')" :class="['flex-1 py-3 rounded-xl text-sm font-semibold text-white transition-all', danger ? 'bg-destructive hover:bg-destructive/90' : 'bg-primary hover:bg-primary/90']">
              {{ confirmLabel }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
defineProps({
  show: { type: Boolean, default: false },
  title: { type: String, default: 'Potvrzení' },
  message: { type: String, default: 'Opravdu chcete pokračovat?' },
  confirmLabel: { type: String, default: 'Potvrdit' },
  danger: { type: Boolean, default: false },
})
defineEmits(['confirm', 'cancel'])
</script>

<style scoped>
.modal-enter-active, .modal-leave-active {
  transition: all 0.2s ease;
}
.modal-enter-from, .modal-leave-to {
  opacity: 0;
}
.modal-enter-from > div, .modal-leave-to > div {
  transform: scale(0.95);
}
</style>
