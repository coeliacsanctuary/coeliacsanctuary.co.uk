<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import axios, { AxiosResponse } from 'axios';
import { watchDebounced } from '@vueuse/core';
import useShopStore from '@/stores/useShopStore';

const props = defineProps<{ address: string }>();

const latlng = ref<{ lat?: number; lng?: number }>({});

onMounted(() => {
  navigator.geolocation.getCurrentPosition(
    (result) => {
      latlng.value = {
        lat: result.coords.latitude,
        lng: result.coords.longitude,
      };
    },
    null,
    {
      enableHighAccuracy: false,
    },
  );
});

const emits = defineEmits(['setAddress']);

const country = computed(() => useShopStore().selectedCountry);

const hasSelectedAddress = ref(false);
const searchResults = ref<{ id: string; address: string }[]>([]);

const selectAddress = async (id: string) => {
  const response = await axios.get(`/api/shop/address-search/${id}`);

  emits('setAddress', response.data);
  hasSelectedAddress.value = true;
  searchResults.value = [];
};

const handleSearch = async () => {
  if (country.value !== 'United Kingdom') {
    return;
  }

  if (hasSelectedAddress.value) {
    hasSelectedAddress.value = false;
    return;
  }

  if (props.address.length < 2) {
    return;
  }

  const request: AxiosResponse<{ id: string; address: string }[]> =
    await axios.post('/api/shop/address-search', {
      search: props.address,
      lat: latlng.value.lat,
      lng: latlng.value.lng,
    });

  searchResults.value = request.data;
};

watchDebounced(() => props.address, handleSearch, { debounce: 100 });
</script>

<template>
  <div class="relative">
    <slot />

    <div
      v-if="
        !hasSelectedAddress && searchResults.length > 0 && address.length >= 2
      "
      class="absolute top-full right-0 z-999 mt-px max-h-60 w-full overflow-scroll border border-grey-darker bg-white shadow-sm"
    >
      <ul class="divide-y divide-grey-off">
        <li
          v-for="result in searchResults"
          :key="result.id"
          class="cursor-pointer p-2 transition-all hover:bg-grey-off/30"
          @click="selectAddress(result.id)"
          v-text="result.address"
        />
      </ul>
    </div>
  </div>
</template>
