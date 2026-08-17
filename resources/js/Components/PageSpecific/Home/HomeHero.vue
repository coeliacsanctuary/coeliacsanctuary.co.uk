<script setup lang="ts">
import CoeliacButton from '@/Components/CoeliacButton.vue';
</script>

<template>
  <div class="relative bg-primary-light/50">
    <div class="mx-auto w-full max-w-8xl">
      <div class="relative z-10 h-[490px]">
        <div class="relative h-full">
          <div
            class="mx-auto flex h-full max-w-5xl flex-col justify-between px-4 py-8 xs:px-8 xs:py-16 sm:px-6 sm:py-8"
          >
            <div class="flex flex-col items-center gap-3">
              <h2
                class="mx-auto text-center font-coeliac text-3xl font-semibold tracking-tight text-grey-darkest [text-shadow:0_1px_10px_rgb(255_255_255/0.85)] sm:w-4/5 sm:text-4xl sm:leading-10"
              >
                Have you got your travel cards?
              </h2>

              <div
                class="h-0.5 w-2/5 rounded-full bg-linear-to-r from-secondary/40 via-secondary/60 to-secondary/40"
              />
            </div>

            <p
              class="mx-auto prose prose-lg max-w-none rounded-xl bg-white/70 px-5 py-4 text-center text-lg leading-6 text-gray-600 backdrop-blur-sm sm:prose-xl sm:w-[72%] sm:leading-8"
            >
              Pop over to my shop to get your gluten free travel cards. They
              explain coeliac and eating gluten free in the local language, so
              you can relax and enjoy your trip with less stress.
            </p>

            <div class="flex items-center justify-center gap-x-6">
              <CoeliacButton
                label="View all Products"
                href="/shop"
                size="xl"
                theme="secondary"
                classes="hover:scale-[1.3] text-2xl!"
                bold
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="absolute bottom-0 h-full w-full bg-gray-50">
      <div
        class="absolute h-full w-full bg-linear-to-b from-primary-light/70 to-primary-light/60"
      />

      <img
        class="h-full w-full object-cover"
        src="/images/travel-cards-hero.webp"
        alt="Have you got your travel cards?"
        width="1408"
        height="490"
        fetchpriority="high"
        decoding="async"
      />
    </div>
  </div>
</template>
