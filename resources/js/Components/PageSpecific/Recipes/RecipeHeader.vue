<script setup lang="ts">
import { RecipePage } from '@/types/RecipeTypes';
import { PrinterIcon } from '@heroicons/vue/20/solid';
import { ArrowUturnLeftIcon } from '@heroicons/vue/24/solid';
import { Link } from '@inertiajs/vue3';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import FullWidthComponent from '@/Components/FullWidthComponent.vue';
import { computed } from 'vue';

const props = defineProps<{
  recipe: RecipePage;
  backLink: string;
  recipeElement?: HTMLElement;
}>();

const metaItems = computed((): { label: string; value: string }[] => [
  { label: 'Added', value: props.recipe.published },
  { label: 'Prep Time', value: props.recipe.timing.prep_time },
  { label: 'Makes', value: props.recipe.nutrition.servings },
]);

const jumpToRecipe = () => {
  if (!props.recipeElement) {
    return;
  }

  props.recipeElement.scrollIntoView({
    behavior: 'smooth',
  });
};
</script>

<template>
  <FullWidthComponent
    wrapper-classes="relative bg-linear-to-b from-primary-light/75 via-primary-light/45 to-primary-light/70"
    inner-classes="p-4 md:p-8"
  >
    <svg
      class="pointer-events-none absolute inset-0 h-full w-full"
      aria-hidden="true"
    >
      <defs>
        <pattern
          id="recipe-header-doodles"
          width="360"
          height="300"
          patternUnits="userSpaceOnUse"
          patternTransform="rotate(-8)"
        >
          <g
            class="stroke-primary-dark opacity-[0.1]"
            fill="none"
            stroke-width="3"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <g transform="translate(20 30) rotate(-7)">
              <rect
                x="12"
                y="0"
                width="68"
                height="20"
                rx="10"
              />
              <path d="M0 10h12M80 10h12" />
            </g>

            <g transform="translate(255 18) rotate(10)">
              <path d="M18 0v22M12 22h12" />
              <path d="M18 22C0 34 0 50 18 58" />
              <path d="M18 22c18 12 18 28 0 36" />
              <path d="M18 22v36" />
            </g>

            <g transform="translate(30 170) rotate(-4)">
              <path d="M0 0h56" />
              <path d="M4 0c2 30 46 30 48 0" />
              <path d="M18 28h20" />
            </g>

            <g transform="translate(300 150) rotate(-16)">
              <ellipse
                cx="10"
                cy="13"
                rx="10"
                ry="13"
              />
              <path d="M10 26v26" />
            </g>

            <g transform="translate(120 235) rotate(8)">
              <path
                d="M12 21c-6-5-12-9-12-14a6 6 0 0 1 12-2 6 6 0 0 1 12 2c0 5-6 9-12 14z"
              />
            </g>

            <g
              transform="translate(140 95) rotate(14) scale(1.7)"
              stroke-width="1.8"
            >
              <path
                d="M12.1521 4.08723C12.1513 3.71959 12.1001 3.3538 12 3C16.9683 3.00545 20.9944 7.03979 21 12C21.0161 16.9625 16.9705 20.9835 12 20.9997C7.02946 21.0158 3.01615 16.963 3 12.0005C4.11168 12.2363 5.27038 11.9981 6.1499 11.2795C7.0562 10.5452 7.5789 9.43935 7.5702 8.27407C7.56959 8.01195 7.5461 7.75072 7.5 7.49268C8.51784 7.89624 9.67043 7.76409 10.5708 7.14162C11.5696 6.44537 12.161 5.3034 12.1521 4.08723Z"
              />
              <path d="M10.002 17.002V17H10V17.002L10.002 17.002Z" />
              <path d="M15.002 15.002V15H15V15.002L15.002 15.002Z" />
              <path d="M11.002 12.002V12H11V12.002L11.002 12.002Z" />
              <path d="M16.002 10.002V10H16V10.002L16.002 10.002Z" />
            </g>

            <path
              transform="translate(200 40)"
              d="M8 0C8 5 5 8 0 8c5 0 8 3 8 8 0-5 3-8 8-8-5 0-8-3-8-8z"
            />
            <path
              transform="translate(60 110) scale(0.8)"
              d="M8 0C8 5 5 8 0 8c5 0 8 3 8 8 0-5 3-8 8-8-5 0-8-3-8-8z"
            />

            <path
              stroke-width="5"
              d="M330 62h0.01M186 210h0.01M96 268h0.01M272 252h0.01M148 168h0.01"
            />
          </g>

          <g
            class="stroke-secondary opacity-[0.22]"
            fill="none"
            stroke-width="3"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <g
              transform="translate(215 195) rotate(-10) scale(1.9)"
              stroke-width="1.6"
            >
              <path
                d="M4 15.7059H20V19H4V15.7059ZM4 15.7059H20V10.4067C20 10.4035 19.9974 10.4009 19.9941 10.4009H4.00587C4.00263 10.4009 4 10.4035 4 10.4067V15.7059ZM15.4676 5C16.4366 5 17.222 5.7835 17.222 6.75L17.216 6.613L17.2521 6.60333C18.7897 7.12298 19.9117 8.52702 19.995 10.2006L19.9992 10.3893L20 10.395C20 10.3982 19.9974 10.4009 19.9941 10.4009H4.03964C4.0364 10.4009 4.03378 10.3982 4.03378 10.395C4.03378 10.3924 4.03547 10.3901 4.03794 10.3894L11.5789 8.10776L13.874 7.48283C13.7708 7.25991 13.7132 7.01166 13.7132 6.75C13.7132 5.7835 14.4987 5 15.4676 5ZM15.4676 8.5C16.4366 8.5 17.222 7.7165 17.222 6.75C17.222 5.7835 16.4366 5 15.4676 5C14.4987 5 13.7132 5.7835 13.7132 6.75C13.7132 7.7165 14.4987 8.5 15.4676 8.5Z"
              />
            </g>

            <path
              transform="translate(35 85) scale(1.1)"
              d="M8 0C8 5 5 8 0 8c5 0 8 3 8 8 0-5 3-8 8-8-5 0-8-3-8-8z"
            />

            <path
              stroke-width="5"
              d="M150 30h0.01M290 120h0.01M70 250h0.01"
            />
          </g>
        </pattern>
      </defs>

      <rect
        width="100%"
        height="100%"
        fill="url(#recipe-header-doodles)"
      />
    </svg>

    <div class="relative z-10 flex flex-col space-y-6">
      <Link
        :href="backLink"
        class="inline-flex items-center justify-center font-medium text-grey-darker transition hover:text-primary-dark xl:text-lg"
      >
        <ArrowUturnLeftIcon class="h-6 w-6 pr-2 xl:h-8 xl:w-8" />

        <span>Back to recipes</span>
      </Link>

      <h1
        class="text-center font-coeliac text-2xl font-semibold text-grey-darkest lg:text-3xl"
      >
        {{ recipe.title }}
      </h1>

      <div class="relative mx-auto w-2/3 max-w-3xs -rotate-5 xs:w-4/5">
        <div
          class="absolute -inset-6 -z-10 rounded-full bg-primary/40 blur-2xl"
          aria-hidden="true"
        />

        <div class="bg-white p-2 pb-7 shadow-xl xs:p-3 xs:pb-10">
          <img
            :alt="recipe.header_image_alt_text ?? recipe.title"
            :src="recipe.square_image || recipe.image"
            loading="lazy"
            class="aspect-square w-full object-cover"
          />
        </div>

        <div
          class="absolute -top-2 -left-5 h-5 w-[5rem] -rotate-[28deg] bg-secondary/70 shadow-sm xs:-top-3 xs:-left-8 xs:h-7 xs:w-[7rem]"
          aria-hidden="true"
        />
      </div>

      <div class="relative isolate">
        <div
          class="pointer-events-none absolute -inset-x-6 -inset-y-4 -z-10 rounded-[50%] bg-white/65 blur-2xl"
          aria-hidden="true"
        />

        <div
          class="prose max-w-none text-center md:prose-lg"
          v-text="recipe.meta_description"
        />
      </div>

      <ul
        class="mx-auto grid max-w-3xl grid-cols-2 gap-x-6 gap-y-3 rounded-xl bg-white/60 px-6 py-3 shadow-sm backdrop-blur-sm xs:grid-cols-4"
      >
        <li class="flex flex-col items-center text-center">
          <span
            class="text-xs font-semibold tracking-wide text-primary-dark uppercase"
          >
            Recipe by
          </span>

          <span
            class="[&>a]:font-semibold [&>a]:text-primary-dark [&>a]:hover:text-grey-darker"
            v-html="recipe.author"
          />
        </li>

        <li
          v-for="item in metaItems"
          :key="item.label"
          class="flex flex-col items-center text-center"
        >
          <span
            class="text-xs font-semibold tracking-wide text-primary-dark uppercase"
            v-text="item.label"
          />

          <span v-text="item.value" />
        </li>
      </ul>

      <div
        class="mx-auto grid w-full max-w-sm grid-cols-1 gap-4 xs:grid-cols-2"
      >
        <CoeliacButton
          class="justify-center"
          size="lg"
          label="Jump to Recipe"
          bold
          theme="primary"
          as="button"
          type="button"
          @click="jumpToRecipe"
        />

        <CoeliacButton
          class="justify-center"
          size="lg"
          :icon="PrinterIcon"
          label="Print Recipe"
          theme="secondary"
          bold
          as="a"
          :href="recipe.print_url"
          target="_blank"
        />
      </div>
    </div>
  </FullWidthComponent>
</template>
