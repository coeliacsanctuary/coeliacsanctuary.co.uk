<script setup lang="ts">
import { EateryBranchesCollection } from '@/types/EateryTypes';
import Warning from '@/Components/Warning.vue';
import Sidebar from '@/Components/Overlays/Sidebar.vue';
import CountryList from '@/Components/PageSpecific/EatingOut/Details/Modals/BranchList/CountryList.vue';
import CountyList from '@/Components/PageSpecific/EatingOut/Details/Modals/BranchList/CountyList.vue';
import TownList from '@/Components/PageSpecific/EatingOut/Details/Modals/BranchList/TownList.vue';
import BranchList from '@/Components/PageSpecific/EatingOut/Details/Modals/BranchList/BranchList.vue';

defineProps<{
  eateryName: string;
  show: boolean;
  branches: EateryBranchesCollection;
}>();

const emits = defineEmits(['close']);

const close = () => {
  emits('close');
};
</script>

<template>
  <Sidebar
    :open="show"
    side="right"
    size="lg"
    @close="close()"
  >
    <div class="flex-1 bg-white">
      <div
        class="border-grey-mid relative border-b bg-grey-light p-3 pr-[34px] text-center text-sm font-semibold"
      >
        {{ eateryName }}'s Branch List
      </div>

      <div class="flex flex-col space-y-3 p-3">
        <Warning>
          <p class="prose-sm max-w-none lg:prose">
            Please note while we take every care to keep this list up to date,
            branches can open and close at any time without warning, please
            check
            {{ eateryName }}'s website for the most accurate information.
          </p>
        </Warning>

        <CountryList
          v-for="(counties, country) in branches"
          :key="country"
          :country="<string>country"
        >
          <CountyList
            v-for="(towns, county) in counties"
            :key="county"
            :county="<string>county"
          >
            <TownList
              v-for="(locations, town) in towns"
              :key="town"
              :town="<string>town"
            >
              <BranchList
                v-for="branch in locations"
                :key="branch.id"
                :branch="branch"
                :eatery-name="eateryName"
              />
            </TownList>
          </CountyList>
        </CountryList>
      </div>
    </div>
  </Sidebar>
</template>
