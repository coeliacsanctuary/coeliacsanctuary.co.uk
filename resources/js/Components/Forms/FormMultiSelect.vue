<script lang="ts" setup>
import {
  FormMultiSelectOption,
  FormMultiSelectProps,
  FormMultiSelectPropsDefaults,
} from '@/Components/Forms/Props';
import { ref, watch } from 'vue';
import { CheckIcon, ChevronUpDownIcon } from '@heroicons/vue/20/solid';
import FormInput from '@/Components/Forms/FormInput.vue';
import {
  Listbox,
  ListboxButton,
  ListboxOption,
  ListboxOptions,
} from '@headlessui/vue';

const props = withDefaults(
  defineProps<FormMultiSelectProps>(),
  FormMultiSelectPropsDefaults,
);

const value = defineModel<FormMultiSelectOption[]>();

const classes = (): string[] => {
  const base = [
    'min-w-0',
    'appearance-none',
    'rounded-md',
    'leading-7',
    'text-gray-900',
    'placeholder-gray-400',
    'shadow-xs',
    'outline-hidden',
    'focus:ring-0',
    'focus:outline-hidden transition',
    'w-full',
    'relative',
    'text-left',
  ];

  if (props.size === 'large') {
    base.push(
      'text-base md:text-lg px-[calc(--spacing(4)-1px)] py-[calc(var(--spacing-1_75)-1px)]',
    );
  } else {
    base.push(
      'px-[calc(--spacing(3)-1px)] py-[calc(--spacing(1.5)-1px)] text-base sm:text-sm sm:leading-6',
    );
  }

  if (props.borders) {
    base.push('border border-grey-off focus:border-grey-dark');
  } else {
    base.push('border-0');
  }

  if (props.background) {
    base.push('bg-white');
  } else {
    base.push('bg-transparent');
  }

  if (props.error) {
    base.push('border-red!', 'focus:border-red-dark');

    if (!props.borders && props.background) {
      base.push('bg-red/90!');
    }
  }

  return base;
};

const optionWrapperClasses = (): string[] => [
  'absolute',
  'mt-1',
  'max-h-60',
  'w-full',
  'overflow-auto',
  'rounded-md',
  'border',
  'border-grey-off',
  'bg-white',
  'py-1',
  'text-base',
  'shadow-lg',
  'focus:border-grey-dark',
  'focus:outline-hidden',
  'sm:text-sm',
];

const liClasses = (active: boolean): string[] => {
  const base = [
    'relative',
    'cursor-default',
    'select-none',
    'py-2',
    'pl-10',
    'pr-4',
  ];

  base.push(
    active
      ? 'bg-primary-lightest/70 font-semibold text-primary-dark'
      : 'text-gray-900',
  );

  return base;
};

const liCheckClasses = (active: boolean, selected: boolean): string[] => {
  const base = ['absolute inset-y-0 left-0 flex items-center pl-3'];

  base.push(selected ? 'text-green' : 'text-grey-off');

  if (active) {
    base.push('text-primary-dark');
  }

  return base;
};

const liTextClasses = (active: boolean, selected: boolean): string[] => {
  const base = ['block truncate'];

  base.push(selected || active ? 'font-semibold' : 'font-normal');

  return base;
};

const otherOptionActive = ref(false);
const otherOptionSelected = ref(false);
const otherValue = ref('');

watch(otherOptionSelected, () => {
  if (otherOptionSelected.value) {
    value.value?.push({ label: '', value: 'other' });
    otherValue.value = '';

    return;
  }

  value.value = value.value?.filter((v) => v.value !== 'other');
  otherValue.value = '';
});

watch(otherValue, () => {
  const existingOptions: FormMultiSelectOption[] =
    value.value as FormMultiSelectOption[];

  if (!existingOptions.filter((v) => v.value === 'other').length) {
    return;
  }

  const index = existingOptions.findIndex((v) => v.value === 'other');

  existingOptions[index].label = otherValue.value;

  value.value = existingOptions;
});
</script>

<template>
  <div>
    <label
      v-if="hideLabel === false"
      :for="id"
      class="block leading-6 font-semibold text-primary-dark"
      :class="
        size === 'large'
          ? 'text-base sm:text-lg xl:text-xl'
          : 'text-base sm:text-lg'
      "
    >
      {{ label }}
      <span
        v-if="required"
        class="text-red"
        v-text="'*'"
      />
    </label>

    <div>
      <Listbox
        v-model="value"
        multiple
      >
        <div class="relative">
          <ListboxButton :class="classes()">
            <span
              v-if="value && value.length"
              v-text="value.map((option) => option.label).join(', ')"
            />
            <span
              v-else
              v-text="placeholder"
            />
            <span
              class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2"
            >
              <ChevronUpDownIcon
                class="h-5 w-5 text-gray-400"
                aria-hidden="true"
              />
            </span>
          </ListboxButton>

          <transition
            enter-active-class="transition duration-100 ease-out"
            enter-from-class="transform scale-95 opacity-0"
            enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-75 ease-out"
            leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0"
          >
            <ListboxOptions :class="optionWrapperClasses()">
              <ListboxOption
                v-for="option in options"
                v-slot="{ active, selected }"
                :key="option.value"
                :value="option"
                as="template"
              >
                <li :class="liClasses(active)">
                  <span
                    class="absolute inset-y-0 left-0 flex items-center pl-3"
                    :class="liCheckClasses(active, selected)"
                  >
                    <CheckIcon
                      class="h-5 w-5"
                      aria-hidden="true"
                    />
                  </span>
                  <span
                    :class="liTextClasses(active, selected)"
                    v-text="option.label"
                  />
                </li>
              </ListboxOption>

              <template v-if="allowOther">
                <li
                  :class="liClasses(otherOptionActive)"
                  @mouseenter="otherOptionActive = true"
                  @mouseleave="otherOptionActive = false"
                  @click="otherOptionSelected = !otherOptionSelected"
                >
                  <span
                    class="absolute inset-y-0 left-0 flex items-center pl-3"
                    :class="
                      liCheckClasses(otherOptionActive, otherOptionSelected)
                    "
                  >
                    <CheckIcon
                      class="h-5 w-5"
                      aria-hidden="true"
                    />
                  </span>
                  <div
                    :class="
                      liTextClasses(otherOptionActive, otherOptionSelected)
                    "
                  >
                    <span v-if="!otherOptionSelected">Other</span>
                    <FormInput
                      v-else
                      v-model="otherValue"
                      placeholder="Please specify..."
                      label=""
                      name=""
                      hide-label
                      @click.stop="undefined"
                    />
                  </div>
                </li>
              </template>
            </ListboxOptions>
          </transition>
        </div>
      </Listbox>
    </div>

    <p
      v-if="error"
      :id="`${name}-error`"
      class="mt-2 text-sm text-red"
      v-text="error"
    />
  </div>
</template>
