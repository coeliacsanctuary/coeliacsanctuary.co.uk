<script lang="ts" setup>
import { DetailedEatery, EateryBranchesCollection } from '@/types/EateryTypes';
import Card from '@/Components/Card.vue';
import { computed, ref } from 'vue';
import { pluralise } from '@/helpers';
import EateryBranchListModal from '@/Components/PageSpecific/EatingOut/Details/Modals/EateryBranchListModal.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import useScreensize from '@/composables/useScreensize';

const props = defineProps<{
  eatery: DetailedEatery;
}>();

const showModal = ref(false);

const { screenIsLessThan } = useScreensize();

const branches = computed(
  (): EateryBranchesCollection =>
    props.eatery.nationwide_branches as EateryBranchesCollection,
);

const numberOfBranches = computed(() => {
  let count = 0;

  Object.keys(branches.value).forEach((country: string) => {
    Object.keys(branches.value[country]).forEach((county: string) => {
      Object.keys(branches.value[country][county]).forEach((town: string) => {
        count += branches.value[country][county][town].length;
      });
    });
  });

  return count;
});
</script>

<template>
  <Card
    class="space-y-2 lg:space-y-4 lg:rounded-lg lg:p-8"
    :class="$attrs.class"
  >
    <p class="prose max-w-none sm:prose-lg lg:prose-xl">
      Our gluten free eating out guide currently features
      <span
        class="font-semibold"
        v-text="numberOfBranches"
      />
      {{ ' ' }}
      <span
        class="font-semibold"
        v-text="eatery.name"
      />
      {{ pluralise('restaurant', numberOfBranches) }} across the UK.
    </p>

    <CoeliacButton
      as="button"
      label="View Branches"
      theme="secondary"
      :size="screenIsLessThan('md') ? 'xxl' : 'lg'"
      classes="justify-center"
      @click="showModal = true"
      @close="showModal = false"
    />
  </Card>

  <EateryBranchListModal
    :eatery-name="eatery.name"
    :show="showModal"
    :branches="branches"
    @close="showModal = false"
  />
</template>
