<script setup lang="ts">
import Info from '@/Components/Info.vue';
import { computed } from 'vue';

const appEnv = computed((): string => {
  return import.meta.env.VITE_APP_ENV;
});
</script>

<template>
  <Info
    class="mt-4"
    faded
    v-if="appEnv !== 'production'"
  >
    <div class="prose">
      <p class="text-xl font-semibold">
        Coeliac Sanctuary is currently on a test environment.
      </p>
      <p>Real payments will not be taken.</p>
      <p class="font-semibold">Test Cards</p>
      <p>
        For each of these cards, the CVC can be any 3 digit number, and the
        expiry must be a date in the future
      </p>
      <ul class="p-0!">
        <li>
          <strong>4242-4242-4242-4242</strong> - Payment will go straight
          through
        </li>
        <li>
          <strong>4000-0027-6000-3184</strong> - Payment requires additional
          authentication.
        </li>
      </ul>
      <p class="font-semibold">Test Google Pay</p>
      <p>
        Any saved real Google Pay details will show on the page, but Google will
        give a warning that this is a test payment and no money will be taken.
      </p>
      <p class="font-semibold">Test Paypal</p>
      <p>Any email/password combination should go through as a test payment.</p>
    </div>
  </Info>
</template>
