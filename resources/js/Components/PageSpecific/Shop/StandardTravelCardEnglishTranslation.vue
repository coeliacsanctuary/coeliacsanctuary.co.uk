<script setup lang="ts">
import { XMarkIcon, CheckIcon } from '@heroicons/vue/24/outline';
import Card from '@/Components/Card.vue';
import SubHeading from '@/Components/SubHeading.vue';

withDefaults(defineProps<{ product: string; canNotEat?: string }>(), {
  canNotEat: '',
});
</script>

<template>
  <Card class="space-y-4">
    <SubHeading classes="text-primary-dark">
      What does my travel card say?
    </SubHeading>

    <p class="prose max-w-none md:prose-lg">
      If you're wondering what exactly my <strong v-text="product" /> card says
      in English, here's the full translation so you can see precisely what
      information it shares. It's especially handy if you're giving it to
      someone else, or you just want to double-check what's being communicated
      before you travel.
    </p>

    <div class="mx-auto w-full max-w-2xl shadow">
      <div class="bg-primary py-4 text-center text-2xl">
        Gluten Free Travel Card
      </div>
      <div class="flex justify-between bg-primary-light px-4 py-[2px] text-xs">
        <span>English</span>
        <span>www.coeliacsanctuary.co.uk</span>
      </div>
      <div class="flex flex-col space-y-3 bg-white px-4 py-2">
        <p class="prose-md prose max-w-none">
          I have Coeliac Disease and follow a strict gluten free diet. I cannot
          eat any foods which contain gluten, small amounts can make me sick.
        </p>

        <div class="flex">
          <div class="w-8">
            <div
              class="size-6 rounded-full border-2 border-red bg-gradient-to-br from-red/80 to-red/40 text-white"
            >
              <XMarkIcon />
            </div>
          </div>

          <div>
            <p class="prose-md prose max-w-none">
              <span class="font-semibold text-red">I can not eat</span> Wheat,
              durum, semolina, couscous, bulgur, rye, barley, oats{{
                canNotEat ? `, ${canNotEat}` : ''
              }}
            </p>
          </div>
        </div>

        <div class="flex">
          <div class="w-8">
            <div
              class="size-6 rounded-full border-2 border-green bg-gradient-to-br from-green/80 to-green/40 text-white"
            >
              <CheckIcon />
            </div>
          </div>

          <div>
            <p class="prose-md prose max-w-none">
              <span class="font-semibold text-green">I can eat</span> Fruit and
              vegetables, meat and fish, rice, potatoes, corn, egg, cheese,
              milk, beans and nuts
            </p>
          </div>
        </div>

        <p class="prose-md prose max-w-none">
          Cross contamination is also an issue, please prepare gluten free
          separate. Cooking my food in fryers also used for gluten containing
          products, sharing toasters and preparing food on contaminated surfaces
          can all make me very sick.
        </p>

        <p class="prose-md prose max-w-none">
          Thank you for helping me eat safely.
        </p>
      </div>
    </div>
  </Card>
</template>
