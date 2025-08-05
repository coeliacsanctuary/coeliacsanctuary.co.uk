<script lang="ts" setup>
import { ArrowDownCircleIcon } from '@heroicons/vue/24/outline';
import {
  Listbox,
  ListboxButton,
  ListboxOption,
  ListboxOptions,
} from '@headlessui/vue';
import { Ref, ref, watch } from 'vue';
import Icon from '@/Components/Icon.vue';
import { SelectBoxItem } from '@/types/Types';
import useGoogleEvents from '@/composables/useGoogleEvents';

export type RecipeFilterOption = SelectBoxItem & {
  disabled: boolean;
  recipeCount: number;
};

const props = defineProps<{
  label: string;
  options: RecipeFilterOption[];
  currentOptions: string[];
}>();

const selectedOptions: Ref<(string | number)[]> = ref(props.currentOptions);

const emit = defineEmits(['changed']);

watch(selectedOptions, () => emit('changed', selectedOptions.value));

watch(
  () => props.currentOptions,
  () => (selectedOptions.value = props.currentOptions),
);

const optionClasses = (disabled: boolean, selected: boolean): string[] => {
  const base = [
    'p-2',
    'border-b',
    'border-secondary',
    'transition',
    'cursor-pointer',
    'last:border-b-0',
    'flex',
    'justify-between',
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
          open ? 'rounded-t-lg bg-secondary' : 'rounded-lg bg-secondary/70'
        "
        class="flex w-full items-center justify-between p-2 text-lg font-semibold transition hover:bg-secondary/100"
        @click="openBox(!open)"
      >
        <div class="flex items-center">
          <ArrowDownCircleIcon
            :class="{ 'rotate-180': open }"
            class="transition-duration-500 mr-2 h-8 w-8 transition"
          />
          <span v-text="label" />
        </div>
        <div v-if="selectedOptions.length">
          <span
            class="font-normal text-grey-dark"
            v-text="selectedOptions.length + '/' + options.length"
          />
        </div>
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
          class="absolute z-10 w-full overflow-hidden rounded-b-lg border-2 border-secondary bg-white shadow-lg"
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
            <div class="flex space-x-2">
              <Icon :name="option.value.toString()" />
              <span v-text="option.label" />
            </div>

            <span v-text="`(${option.recipeCount} recipes)`" />
          </ListboxOption>
        </ListboxOptions>
      </transition>
    </Listbox>
  </div>
</template>
