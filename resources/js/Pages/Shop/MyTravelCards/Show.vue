<script setup lang="ts">
import CoeliacTravelCards from '@/Layouts/CoeliacTravelCards.vue';
import Card from '@/Components/Card.vue';
import { PurchasedCard } from '@/types/Shop';
import PurchasedCardEatSection from '@/Components/PageSpecific/Shop/MyTravelCards/PurchasedCardEatSection.vue';

defineOptions({
  layout: [
    CoeliacTravelCards,
    {
      showBack: true,
      hideFooter: true,
    },
  ],
});

defineProps<{ card: PurchasedCard; id: string }>();
</script>

<template>
  <Card
    no-padding
    :shadow="false"
    class="mx-auto min-h-[calc(100vh-5.5rem)] w-full max-w-xl overflow-hidden rounded-xl select-none"
  >
    <div class="bg-primary px-2 py-1 xs:px-3 sm:py-2 lg:py-4">
      <h1
        class="text-center text-xl xs:text-2xl lg:text-3xl"
        v-text="card.content.title"
      />
    </div>

    <div
      class="flex flex-col justify-between bg-primary-light px-2 py-1 xs:flex-row xs:items-center sm:px-4 lg:py-2"
    >
      <div class="lg:text-lg">
        {{ card.native_language }} ({{ card.language }})
      </div>
      <div
        class="text-xs opacity-20"
        v-text="`Ref: ${id}`"
      />
    </div>

    <div class="flex flex-1 flex-col space-y-2 p-2 sm:px-4 md:space-y-4">
      <p
        class="prose-sm max-w-none xxs:prose xs:prose-lg lg:prose-xl"
        v-text="card.content.intro"
      />

      <PurchasedCardEatSection :details="card.content.cant_eat" />

      <PurchasedCardEatSection :details="card.content.can_eat" />

      <p
        class="prose-sm max-w-none xxs:prose xs:prose-lg lg:prose-xl"
        v-text="card.content.outro"
      />

      <p
        class="prose-sm max-w-none xxs:prose xs:prose-lg lg:prose-xl"
        v-text="card.content.thank_you"
      />
    </div>

    <div class="bg-primary-light px-2 py-1 text-center">
      <p class="prose max-w-none">
        Copyright &copy; {{ new Date().getFullYear() }} Coeliac Sanctuary
      </p>

      <p class="prose max-w-none text-xs!">
        This travel card may not be modified or shared without express
        permission.
      </p>
    </div>
  </Card>
</template>
