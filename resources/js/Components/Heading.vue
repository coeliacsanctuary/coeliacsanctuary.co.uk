<script lang="ts" setup>
import { HeadingBackLink, HeadingCustomLink } from '@/types/Types';
import { Link } from '@inertiajs/vue3';
import { ArrowUturnLeftIcon } from '@heroicons/vue/20/solid';

withDefaults(
  defineProps<{
    as?: string;
    border?: boolean;
    backLink?: HeadingBackLink;
    customLink?: HeadingCustomLink;
    classes?: string;
  }>(),
  {
    as: 'h1',
    border: true,
    backLink: undefined,
    customLink: undefined,
    classes: '',
  },
);
</script>

<template>
  <div
    :class="{ 'border-gray-light border-b pb-2': border }"
    class="flex flex-col"
  >
    <Link
      v-if="backLink && backLink.position === 'top'"
      :href="backLink.href"
      class="mb-4 inline-flex items-center font-medium text-gray-500 hover:text-primary-dark xl:text-lg"
      :class="{
        'justify-center':
          !backLink.direction || backLink.direction === 'center',
        'justify-start': backLink.direction === 'left',
        'justify-end': backLink.direction === 'right',
      }"
    >
      <ArrowUturnLeftIcon class="h-6 w-6 pr-2 xl:h-8 xl:w-8" />

      <span v-html="backLink.label" />
    </Link>

    <a
      v-else-if="customLink && customLink.position === 'top'"
      :href="customLink.href"
      :target="customLink.newTab ? '_blank' : '_self'"
      class="mb-4 inline-flex items-center font-medium"
      :class="[
        {
          'justify-center':
            !customLink.direction || customLink.direction === 'center',
          'justify-start': customLink.direction === 'left',
          'justify-end': customLink.direction === 'right',
        },
        customLink.classes,
      ]"
    >
      <component
        :is="customLink.icon"
        v-if="customLink.icon && customLink.iconPosition === 'left'"
        class="h-6 w-6 pr-2 xl:h-8 xl:w-8"
      />

      <span v-html="customLink.label" />

      <component
        :is="customLink.icon"
        v-if="customLink.icon && customLink.iconPosition === 'right'"
        class="h-6 w-6 pl-2 xl:h-8 xl:w-8"
      />
    </a>

    <component
      :is="as"
      class="mb-0! text-center font-coeliac text-3xl font-semibold md:max-lg:text-4xl lg:text-5xl"
      :class="classes"
    >
      <slot />
    </component>

    <Link
      v-if="backLink && backLink.position !== 'top'"
      :href="backLink.href"
      class="mt-4 inline-flex items-center justify-center font-medium text-gray-500 hover:text-primary-dark xl:text-lg"
      :class="{
        'justify-center':
          !backLink.direction || backLink.direction === 'center',
        'justify-start': backLink.direction === 'left',
        'justify-end': backLink.direction === 'right',
      }"
      xe
    >
      <ArrowUturnLeftIcon class="h-6 w-6 pr-2 xl:h-8 xl:w-8" />

      <span v-html="backLink.label" />
    </Link>

    <a
      v-else-if="customLink && customLink.position === 'bottom'"
      :href="customLink.href"
      :target="customLink.newTab ? '_blank' : '_self'"
      class="mt-4 inline-flex items-center font-medium"
      :class="[
        {
          'justify-center':
            !customLink.direction || customLink.direction === 'center',
          'justify-start': customLink.direction === 'left',
          'justify-end': customLink.direction === 'right',
        },
        customLink.classes,
      ]"
    >
      <component
        :is="customLink.icon"
        v-if="customLink.icon && customLink.iconPosition === 'left'"
        class="h-6 w-6 pr-2 xl:h-8 xl:w-8"
      />

      <span v-html="customLink.label" />

      <component
        :is="customLink.icon"
        v-if="customLink.icon && customLink.iconPosition === 'right'"
        class="h-6 w-6 pl-2 xl:h-8 xl:w-8"
      />
    </a>
  </div>
</template>
