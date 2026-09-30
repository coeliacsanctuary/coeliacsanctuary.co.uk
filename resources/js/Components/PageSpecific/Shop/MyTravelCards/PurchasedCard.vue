<script setup lang="ts">
import { PurchasedCardIndex } from '@/types/Shop';
import Card from '@/Components/Card.vue';
import CountryFlag from '@/Components/CountryFlag.vue';
import { ref } from 'vue';
import DigitalCardCountryList from '@/Components/PageSpecific/Shop/DigitalCards/DigitalCardCountryList.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{ card: PurchasedCardIndex }>();

const showCountryListSidebar = ref(false);
</script>

<template>
  <Card class="group relative cursor-pointer">
    <Link
      class="absolute top-0 left-0 h-full w-full"
      :href="card.link"
      prefetch
    />
    <div class="mx-auto flex max-w-3xs flex-1 flex-col space-y-3">
      <div class="p-4 opacity-50 transition group-hover:opacity-100">
        <CountryFlag
          :code="card.flag"
          :country="card.language"
          width="256"
          height="192"
        />
      </div>

      <h2
        class="text-center text-xl font-semibold text-primary-dark transition group-hover:text-black md:text-2xl"
        v-text="card.title"
      />

      <div class="flex-1" />

      <CoeliacButton
        v-if="card.countries?.length"
        as="a"
        theme="light"
        size="sm"
        label="Where can I use this card?"
        classes="justify-center text-center items-center opacity-50 transition hover:opacity-100"
        @click="showCountryListSidebar = true"
      />
    </div>
  </Card>

  <DigitalCardCountryList
    v-if="card.countries?.length"
    :title="card.title"
    :countries="card.countries"
    :show="showCountryListSidebar"
    @close="showCountryListSidebar = false"
  />
</template>
