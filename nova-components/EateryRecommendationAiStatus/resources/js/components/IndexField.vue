<template>
  <div class="flex items-center">
    <span v-if="currentStatus === 'none'" class="text-gray-400">—</span>

    <Loader v-else-if="currentStatus === 'pending'" :width="20" />

    <IconBoolean v-else-if="currentStatus === 'completed'" :value="true" />

    <IconBoolean v-else-if="currentStatus === 'failed'" :value="false" />
  </div>
</template>

<script>
export default {
  props: ['resourceName', 'field'],

  data() {
    return {
      currentStatus: this.field.status,
      pollInterval: null,
    }
  },

  mounted() {
    if (this.currentStatus === 'pending') {
      this.startPolling()
    }
  },

  beforeUnmount() {
    this.stopPolling()
  },

  watch: {
    'field.status'(newStatus) {
      this.currentStatus = newStatus

      if (newStatus !== 'pending') {
        this.stopPolling()
      }
    },
  },

  methods: {
    startPolling() {
      this.pollInterval = setInterval(this.checkStatus, 5000)
    },

    stopPolling() {
      if (this.pollInterval) {
        clearInterval(this.pollInterval)
        this.pollInterval = null
      }
    },

    async checkStatus() {
      try {
        const response = await fetch(`/nova-vendor/eatery-recommendation/ai-status/${this.field.resourceId}`)
        const data = await response.json()

        if (data.status !== 'pending') {
          this.stopPolling()
          Nova.$emit('refresh-resources')
        }
      } catch {
        // Silently ignore transient polling errors
      }
    },
  },
}
</script>
