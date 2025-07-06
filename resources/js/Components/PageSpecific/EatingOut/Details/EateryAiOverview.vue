<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { onMounted, ref } from 'vue';
import Loader from '@/Components/Loader.vue';
import SealiacSeal from '@/Svg/SealiacSeal.vue';
import axios, { AxiosResponse } from 'axios';
const props = defineProps<{
  eateryName: string;
  eateryId: number;
  branchId?: number;
}>();

const isLoading = ref(true);
const overview = ref<string | undefined>();

const getAiOverview = () => {
  let url = `/api/wheretoeat/${props.eateryId}/sealiac`;

  if (props.branchId) {
    url += `?branchId=${props.branchId}`;
  }

  axios.get(url).then((response: AxiosResponse<{ data: string }>) => {
    overview.value = response.data.data;
    isLoading.value = false;
  });
};

onMounted(() => {
  getAiOverview();
});
</script>

<template>
  <Card class="space-y-2 lg:space-y-4 lg:rounded-lg lg:p-8">
    <template v-if="isLoading">
      <Loader
        color="primary"
        :absolute="false"
        size="size-18"
        width="border-6"
        display
      />
    </template>
    <template v-else>
      <div class="flex w-full items-end border-b border-primary-light">
        <SealiacSeal class="mr-1 mb-2 size-12 flex-shrink-0" />

        <h3 class="text-lg font-semibold lg:text-2xl">
          Here's what Sealiac the Seal thinks about {{ eateryName }}
        </h3>
      </div>

      <p
        class="prose"
        v-html="overview"
      />
    </template>
  </Card>
</template>
