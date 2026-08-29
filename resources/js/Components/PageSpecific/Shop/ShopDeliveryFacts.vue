<script setup lang="ts">
import Card from '@/Components/Card.vue';
import {
  ClockIcon,
  EnvelopeIcon,
  GlobeEuropeAfricaIcon,
} from '@heroicons/vue/24/outline';
</script>

<template>
  <Card class="space-y-4">
    <div class="grid gap-4 sm:grid-cols-3">
      <div class="flex items-start space-x-3">
        <ClockIcon class="mt-1 size-8 shrink-0 text-primary-dark" />

        <div class="flex flex-col">
          <h2 class="font-semibold">Dispatched within 2 working days</h2>

          <p class="text-sm text-grey-dark">
            Orders are processed on normal UK working days, and you'll get an
            email as soon as yours is on its way.
          </p>
        </div>
      </div>

      <div class="flex items-start space-x-3">
        <EnvelopeIcon class="mt-1 size-8 shrink-0 text-primary-dark" />

        <div class="flex flex-col">
          <h2 class="font-semibold">Royal Mail First Class</h2>

          <p class="text-sm text-grey-dark">
            All UK orders are sent First Class, and Royal Mail aim to deliver
            90% of them the next working day.
          </p>
        </div>
      </div>

      <div class="flex items-start space-x-3">
        <GlobeEuropeAfricaIcon class="mt-1 size-8 shrink-0 text-primary-dark" />

        <div class="flex flex-col">
          <h2 class="font-semibold">Worldwide delivery</h2>

          <p class="text-sm text-grey-dark">
            I post internationally by Royal Mail International Standard, which
            can take up to three or four weeks depending on the country.
          </p>
        </div>
      </div>
    </div>
  </Card>
</template>
