<script setup lang="ts">
import CountryFlag from '@/Components/CountryFlag.vue';
import Sidebar from '@/Components/Overlays/Sidebar.vue';
import Card from '@/Components/Card.vue';
import { ShopDigitalCard } from '@/types/Shop';

defineProps<{
  countries: ShopDigitalCard['countries'];
  title: ShopDigitalCard['title'];
  show: boolean;
}>();
defineEmits(['close']);
</script>

<template>
  <Sidebar
    side="right"
    :open="show"
    @close="$emit('close')"
  >
    <Card class="flex min-h-screen flex-col space-y-3">
      <h2 class="text-lg font-semibold">
        Here's some countries where my {{ title }} can be used:
      </h2>

      <ul class="flex flex-col space-y-2">
        <li
          v-for="country in countries"
          :key="country.country"
          class="flex items-center"
        >
          <CountryFlag
            :code="country.code"
            :country="country.country"
          />

          <span
            class="font-semibold"
            v-text="country.country"
          />
        </li>
      </ul>
    </Card>
  </Sidebar>
</template>
