<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { CheckCircleIcon } from '@heroicons/vue/24/outline';
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <Card class="mt-3 flex flex-col space-y-4">
    <Heading>Thanks for reviewing your order!</Heading>

    <div class="flex items-center justify-center text-center text-green">
      <CheckCircleIcon class="h-24 w-24" />
    </div>

    <p class="prose max-w-none md:prose-lg">
      Thank you for sending your feedback, it really does mean a lot and it
      genuinely helps me keep improving Coeliac Sanctuary!
    </p>

    <div class="flex justify-center pt-2">
      <CoeliacButton
        label="Back to the shop"
        :as="Link"
        href="/shop"
        theme="secondary"
        size="xxl"
      />
    </div>
  </Card>
</template>
