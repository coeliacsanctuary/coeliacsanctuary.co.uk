<script lang="ts" setup>
import { ArrowDownCircleIcon } from '@heroicons/vue/24/outline';
import {
  Listbox,
  ListboxButton,
  ListboxOption,
  ListboxOptions,
} from '@headlessui/vue';
import { computed, WritableComputedRef } from 'vue';
import Icon from '@/Components/Icon.vue';
import { SelectBoxItem } from '@/types/Types';
import useGoogleEvents from '@/composables/useGoogleEvents';
import { pluralise } from '@/helpers';

export type RecipeFilterOption = SelectBoxItem & {
  disabled: boolean;
  recipeCount: number;
};

const props = defineProps<{
  label: string;
  options: RecipeFilterOption[];
  currentOptions: string[];
}>();

const emit = defineEmits(['changed']);

const selectedOptions: WritableComputedRef<(string | number)[]> = computed({
  get: () => props.currentOptions,
  set: (options) => emit('changed', options),
});

const optionClasses = (disabled: boolean, selected: boolean): string[] => {
  const base = [
    'p-2',
    'border-b',
    'border-secondary/50',
    'transition',
    'cursor-pointer',
    'last:border-b-0',
    'flex',
    'justify-between',
    'items-center',
    'gap-2',
  ];

  if (selected) {
    base.push('bg-primary-light/50 sm:hover:bg-primary-light/70');
  } else if (!disabled) {
    base.push('hover:bg-grey-light');
  } else {
    base.push('text-grey-off-dark');
  }

  return base;
};

const openBox = (open: boolean) => {
  if (!open) {
    return;
  }

  useGoogleEvents().googleEvent('event', 'modules', {
    event_category: 'opened-recipe-filter',
    event_label: `opened-recipe-filter-for-${props.label}`,
  });
};
</script>

<template>
  <div class="relative">
    <Listbox
      v-slot="{ open }"
      v-model="selectedOptions"
      multiple
    >
      <ListboxButton
        :class="
          open
            ? 'rounded-t-lg border-secondary bg-secondary/60'
            : 'rounded-lg border-secondary/50 bg-secondary/25'
        "
        class="flex w-full items-center justify-between gap-2 border p-2 font-semibold transition hover:bg-secondary/60"
        @click="openBox(!open)"
      >
        <div class="flex items-center gap-2">
          <ArrowDownCircleIcon
            :class="{ 'rotate-180': open }"
            class="size-5 shrink-0 text-primary-dark transition duration-500"
          />

          <span v-text="label" />
        </div>

        <span
          v-if="selectedOptions.length"
          class="rounded-full bg-primary-dark px-2 py-0.5 text-xs leading-none font-semibold text-white"
          v-text="selectedOptions.length"
        />
      </ListboxButton>

      <transition
        enter-active-class="transition duration-100 ease-out"
        enter-from-class="transform scale-95 opacity-0"
        enter-to-class="transform scale-100 opacity-100"
        leave-active-class="transition duration-75 ease-out"
        leave-from-class="transform scale-100 opacity-100"
        leave-to-class="transform scale-95 opacity-0"
      >
        <ListboxOptions
          class="absolute z-10 max-h-[60vh] w-full overflow-y-auto rounded-b-lg border border-secondary bg-white text-sm shadow-lg"
        >
          <ListboxOption
            v-for="option in options"
            :key="option.value"
            :class="
              optionClasses(
                option.disabled,
                selectedOptions.includes(option.value),
              )
            "
            :disabled="option.disabled"
            :value="option.value"
          >
            <div class="flex min-w-0 items-center space-x-2">
              <Icon
                :name="option.value.toString()"
                class="size-5 shrink-0"
              />

              <span v-text="option.label" />
            </div>

            <span
              class="shrink-0 text-xs text-grey-dark"
              v-text="
                `${option.recipeCount} ${pluralise('recipe', option.recipeCount)}`
              "
            />
          </ListboxOption>
        </ListboxOptions>
      </transition>
    </Listbox>
  </div>
</template>
