<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { Link } from '@inertiajs/vue3';
import { HomeHoverItem } from '@/types/Types';
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{ item: HomeHoverItem; landscape?: boolean }>(),
  { landscape: false },
);

const squareImage = computed<string | undefined>(() =>
  !props.landscape && props.item.type === 'Recipe'
    ? props.item.square_image
    : undefined,
);

const meta = computed<string | undefined>(() => {
  if (!props.item.nutrition) {
    return props.item.date;
  }

  const { servings, calories, portion_size: portionSize } = props.item.nutrition;

  return (
    [
      servings ? `Makes ${servings}` : null,
      calories ? `${calories} calories per ${portionSize}` : null,
    ]
      .filter(Boolean)
      .join(' · ') || props.item.date
  );
});
</script>

<template>
  <Card
    class="group overflow-hidden rounded-lg!"
    no-padding
  >
    <Link
      :href="item.link"
      class="flex h-full flex-col"
      prefetch
    >
      <div class="shrink-0 overflow-hidden">
        <img
          v-if="squareImage"
          :alt="item.header_image_alt_text ?? item.title"
          :src="squareImage"
          loading="lazy"
          width="800"
          height="800"
          class="aspect-square w-full object-cover transition duration-500 group-hover:scale-105"
        />
        <img
          v-else
          :alt="item.header_image_alt_text ?? item.title"
          :src="item.image"
          loading="lazy"
          width="1200"
          height="630"
          class="aspect-[1200/630] w-full object-cover transition duration-500 group-hover:scale-105"
        />
      </div>

      <div class="flex flex-1 flex-col space-y-1 bg-primary-lightest/60 p-3">
        <h3
          class="line-clamp-2 text-base font-semibold transition group-hover:text-primary-dark"
          v-text="item.title"
        />

        <span
          v-if="meta"
          class="mt-auto pt-2 text-xs text-grey-dark italic"
          v-text="meta"
        />
      </div>
    </Link>
  </Card>
</template>
