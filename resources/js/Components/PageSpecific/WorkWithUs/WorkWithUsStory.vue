<script setup lang="ts">
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';

withDefaults(
  defineProps<{
    title: string;
    image: string;
    links: { href: string; label: string }[];
    noSmallImage?: boolean;
  }>(),
  {
    noSmallImage: false,
  },
);
</script>

<template>
  <div class="@container">
    <Card class="flex h-full flex-col space-y-5 @xl:p-8">
      <Heading
        classes="text-primary-dark text-left"
        as="h2"
        :border="false"
      >
        {{ title }}
      </Heading>

      <div class="flex h-full w-full flex-col @sm:flex-row @sm:space-x-5">
        <div class="flex h-full flex-1 flex-col space-y-5">
          <p class="prose prose-lg flex inline max-w-none flex-1 @xl:prose-xl">
            <img
              v-if="!noSmallImage"
              :src="image"
              :alt="title"
              class="float-right mb-2 ml-2 w-1/2 @sm:hidden"
              loading="lazy"
            />
            <slot />
          </p>

          <ul
            v-if="links.length > 0"
            class="prose prose-lg @xl:prose-xl"
          >
            <li
              v-for="link in links"
              :key="link.href"
              class="my-0 ps-0! pl-0 font-semibold text-primary-dark hover:text-black"
            >
              <a
                :href="link.href"
                target="_blank"
                v-text="link.label"
              />
            </li>
          </ul>
        </div>

        <div class="hidden @sm:block @sm:w-1/4 @sm:max-w-xs @-sm:shrink-0">
          <img
            :src="image"
            :alt="title"
            class="float-right w-full"
            loading="lazy"
          />
        </div>
      </div>
    </Card>
  </div>
</template>
