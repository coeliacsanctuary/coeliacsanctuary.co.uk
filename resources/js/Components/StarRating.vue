<script lang="ts" setup>
import { computed, onMounted, ref, watch } from 'vue';
import Icon from '@/Components/Icon.vue';
import { StarRating } from '@/types/EateryTypes';

const props = withDefaults(
  defineProps<{
    rating: StarRating;
    align?: 'center' | 'start' | 'end';
    size?: string;
    color?: string;
    showAll?: boolean;
  }>(),
  {
    align: 'center',
    size: 'w-6 h-6',
    color: 'text-secondary',
    showAll: false,
  },
);

const wholeNumber = ref(0);
const hasHalf = ref(false);

const loadStars = () => {
  let stars = 0;

  if (props.rating) {
    stars = props.rating;
  }

  const parts = stars.toString().split('.');

  wholeNumber.value = parseInt(parts[0], 10);
  const remainingNumber = parts[1] ? parseInt(parts[1].charAt(0), 10) : 0;

  hasHalf.value = remainingNumber > 3 && remainingNumber < 7;

  if (remainingNumber >= 7) {
    wholeNumber.value += 1;
  }
};

onMounted(() => {
  loadStars();
});

const remainingStars = computed((): number[] => {
  let remaining = 5 - wholeNumber.value;

  if (hasHalf.value) {
    remaining -= 1;
  }

  return new Array(remaining) as number[];
});

const halfStarIcon = computed(() => {
  if (props.showAll) {
    return 'star-half-mix';
  }

  return 'star-half-full';
});

const shouldShowEmptyStars = (): boolean => {
  if (!props.showAll) {
    return false;
  }

  if (wholeNumber.value === 5) {
    return false;
  }

  if (wholeNumber.value === 4 && hasHalf.value) {
    return false;
  }

  return true;
};

watch(
  () => props.rating,
  () => loadStars(),
);
</script>

<template>
  <div
    :class="[
      {
        'justify-center sm:justify-start': align === 'center',
        justifyEnd: align === 'end',
        justifyStart: align === 'start',
      },
      color,
    ]"
    class="flex space-x-0.5"
  >
    <Icon
      v-for="n in wholeNumber"
      :key="n"
      :class="size"
      name="star-full"
    />
    <Icon
      v-if="hasHalf"
      :class="size"
      :name="halfStarIcon"
    />
    <template v-if="shouldShowEmptyStars()">
      <Icon
        v-for="n in remainingStars"
        :key="n"
        :class="size"
        name="star-empty"
      />
    </template>
  </div>
</template>
