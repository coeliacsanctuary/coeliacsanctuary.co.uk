<template>
  <div>
    <Head title="Refresh Ads Txt" />

    <Heading class="mb-6">Refresh Ads Txt</Heading>

    <Card class="p-8">
      <Button
        :loading="isLoading"
        @click="refreshAdxTxt()"
      >
        Reload ads.txt from mediavine
      </Button>

      <p
        v-if="refreshed"
        class="mt-4 text-lg font-bold text-green-700"
      >
        ads.txt refreshed!
      </p>
    </Card>
  </div>
</template>

<script>
import { Button } from 'laravel-nova-ui';

export default {
  components: { Button },

  data: () => ({
    isLoading: false,
    refreshed: false,
  }),

  methods: {
    refreshAdxTxt() {
      this.isLoading = true;
      this.refreshed = false;

      Nova.request()
        .post('/nova-vendor/refresh-ads-txt')
        .then(() => {
          this.isLoading = false;
          this.refreshed = true;
        });
    },
  },
};
</script>

<style>
/* Scoped Styles */
</style>
