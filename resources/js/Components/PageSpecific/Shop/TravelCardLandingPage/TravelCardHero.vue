<script setup lang="ts">
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import TravelCardSearchLookup from '@/Components/PageSpecific/Shop/TravelCardSearchLookup.vue';
import TravelCardDestinationChips from '@/Components/PageSpecific/Shop/TravelCardLandingPage/TravelCardDestinationChips.vue';
import { TravelCardDestinationChip } from '@/types/Shop';

defineProps<{
  destinations: TravelCardDestinationChip[];
  searchTerm: string | null;
}>();

const searching = defineModel<boolean>('searching', { default: false });
</script>

<template>
  <Card
    class="mt-3 items-center justify-center"
    theme="primary"
    faded
  >
    <div class="flex w-full flex-col items-center space-y-4 sm:w-3/4 xl:w-2/3">
      <Heading
        :back-link="{
          label: 'Back to Shop',
          href: '/shop',
          position: 'top',
          direction: 'left',
        }"
        :border="false"
        class="w-full"
      >
        Gluten Free Travel and Translation Cards
      </Heading>

      <p class="prose max-w-none text-center md:prose-lg">
        Business card sized, professionally translated, and made to explain
        coeliac disease clearly in the local language. Tell me where you're
        heading and I'll find the right card for you.
      </p>

      <TravelCardSearchLookup
        v-model:searching="searching"
        :search-term="searchTerm"
      />

      <TravelCardDestinationChips
        :destinations="destinations"
        :current="searchTerm"
      />
    </div>
  </Card>
</template>
