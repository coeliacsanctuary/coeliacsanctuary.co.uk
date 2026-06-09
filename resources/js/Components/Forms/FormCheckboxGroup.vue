<script lang="ts" setup>
import { CheckboxItem } from '@/types/Types';
import { computed, ref } from 'vue';
import { ChevronUpIcon } from '@heroicons/vue/20/solid';
import FormInput from '@/Components/Forms/FormInput.vue';

const props = withDefaults(
  defineProps<{
    id?: string;
    label?: string;
    collapsible?: boolean;
    isCollapsedByDefault?: boolean;
    searchable?: boolean;
  }>(),
  {
    id: `filter-checkbox-group-${Math.random() * (9999 - 1000) + 1000}`,
    label: undefined,
    collapsible: false,
    isCollapsedByDefault: false,
    searchable: false,
  },
);

const items = defineModel<CheckboxItem[]>();

const isCollapsed = ref(props.isCollapsedByDefault);

const search = ref('');

const toggleCollapsed = () => {
  if (props.collapsible) {
    isCollapsed.value = !isCollapsed.value;
  }
};

const checkedCount = computed(
  () => items.value?.filter((item) => item.checked).length ?? 0,
);

const checkedItems = computed(() =>
  (items.value ?? [])
    .map((item, index) => ({ ...item, originalIndex: index }))
    .filter((item) => item.checked),
);

const itemChecked = (index: number) => {
  if (items.value) {
    items.value[index].checked = !items.value[index].checked;
  }
};

type Checkboxes = { group: string | undefined; items: CheckboxItem[] }[];

const checkboxes = computed((): Checkboxes => {
  const keys: Array<string | undefined> = [];

  const rawItems: CheckboxItem[] = items.value as CheckboxItem[];

  rawItems.forEach((item) => {
    if (!keys.includes(item.groupBy)) {
      keys.push(item.groupBy);
    }
  });

  const term = search.value.toLowerCase();

  return keys
    .map((key) => ({
      group: key,
      items: rawItems
        .map((item, index) => {
          return {
            ...item,
            originalIndex: index,
          };
        })
        .filter(
          (item) =>
            item.groupBy === key &&
            (!term || item.label.toLowerCase().includes(term)),
        ),
    }))
    .filter((group) => group.items.length > 0);
});
</script>

<template>
  <fieldset>
    <legend
      v-if="label || collapsible"
      class="mb-2 flex w-full items-end justify-between"
      :class="{
        'cursor-pointer': collapsible,
        'border-b border-gray-200 pb-2':
          collapsible && isCollapsed && checkedCount === 0,
      }"
      @click="toggleCollapsed"
    >
      <span
        v-if="label"
        class="text-lg font-semibold"
      >
        {{ label }}

        <span
          v-if="checkedCount > 0"
          class="text-primary-dark"
          v-text="` - (${checkedCount})`"
        />
      </span>

      <ChevronUpIcon
        v-if="collapsible"
        class="h-5 w-5 text-gray-400 transition-transform duration-300"
        :class="{ 'rotate-180': isCollapsed }"
        aria-hidden="true"
      />
    </legend>

    <fieldset v-if="collapsible && isCollapsed && checkedCount > 0">
      <div class="divide-gray-light divide-y border-t border-b border-gray-200">
        <div
          v-for="item in checkedItems"
          :key="item.value"
          class="relative flex items-center py-1 xmd:py-2"
        >
          <div class="min-w-0 flex-1">
            <label
              :for="`${id}-collapsed-${item.value}`"
              class="text-sm font-semibold text-gray-900 select-none xmd:text-base"
              v-text="item.label"
            />
          </div>
          <div class="ml-3 flex items-center">
            <input
              :id="`${id}-collapsed-${item.value}`"
              :checked="item.checked"
              class="h-4 w-4 rounded-sm border-gray-300 text-primary focus:ring-primary xmd:h-5 xmd:w-5"
              type="checkbox"
              @change="itemChecked(item.originalIndex)"
            />
          </div>
        </div>
      </div>
    </fieldset>

    <Transition
      enter-active-class="overflow-hidden transition-all duration-300 ease-in-out"
      leave-active-class="overflow-hidden transition-all duration-300 ease-in-out"
      enter-from-class="max-h-0 opacity-0"
      enter-to-class="max-h-[2000px] opacity-100"
      leave-from-class="max-h-[2000px] opacity-100"
      leave-to-class="max-h-0 opacity-0"
    >
      <div v-show="!isCollapsed">
        <div
          v-if="searchable"
          class="mb-2 pb-2"
        >
          <FormInput
            v-model="search"
            name="search"
            label=""
            hide-label
            placeholder="Search..."
            size="sm"
            borders
          />
        </div>

        <p
          v-if="search && !checkboxes.length"
          class="text-sm text-gray-500"
        >
          No results for &ldquo;{{ search }}&rdquo;
        </p>

        <fieldset
          v-for="group in checkboxes"
          :key="group.group"
        >
          <legend
            v-if="group.group"
            class="py-1 font-semibold text-primary-dark xmd:py-2"
            v-text="group.group"
          />
          <div
            class="divide-gray-light divide-y border-t border-b border-gray-200"
          >
            <div
              v-for="(item, index) in group.items"
              :key="item.value"
              class="relative flex items-center py-1 xmd:py-2"
            >
              <div
                :class="{ 'cursor-not-allowed': item.disabled }"
                class="min-w-0 flex-1"
              >
                <label
                  :class="{
                    'text-grey': !item.disabled,
                    'text-grey-off': item.disabled,
                    'font-semibold': item.checked === true,
                  }"
                  :for="`${id}-${item.value}`"
                  class="text-sm text-gray-900 select-none xmd:text-base"
                  v-text="item.label"
                />
              </div>
              <div class="ml-3 flex items-center">
                <input
                  :id="`${id}-${item.value}`"
                  :checked="item.checked"
                  :disabled="item.disabled"
                  class="h-4 w-4 rounded-sm border-gray-300 text-primary focus:ring-primary xmd:h-5 xmd:w-5"
                  type="checkbox"
                  @change="
                    itemChecked(item.originalIndex ? item.originalIndex : index)
                  "
                />
              </div>
            </div>
          </div>
        </fieldset>
      </div>
    </Transition>
  </fieldset>
</template>
