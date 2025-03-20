<script setup lang="ts">
import { EditableEateryFeatureValues } from '@/types/EateryTypes';
import { onMounted, Ref, ref } from 'vue';
import FormCheckbox from '@/Components/Forms/FormCheckbox.vue';

const props = defineProps<{
  currentFeatures: EditableEateryFeatureValues[];
}>();

const emits = defineEmits(['change']);

type Feature = {
  key: number;
  label: string;
  selected: boolean;
};

const features: Ref<Feature[]> = ref([]);

const emitChange = () => {
  emits('change', features.value);
};

onMounted(() => {
  features.value = props.currentFeatures.map((feature) => ({
    key: feature.id,
    label: feature.label,
    selected: feature.selected,
  }));
});
</script>

<template>
  <div class="text-sm">
    <ul class="flex flex-col space-y-px divide-y divide-grey-off">
      <li
        v-for="feature in features"
        :key="feature.key"
        class="flex w-full justify-between py-1"
      >
        <div class="flex w-full items-center justify-between space-x-1">
          <FormCheckbox
            v-model="feature.selected"
            :label="feature.label"
            :name="`feature_${feature.key}`"
            class="w-full"
            @update:model-value="emitChange()"
          />
        </div>
      </li>
    </ul>
  </div>
</template>
