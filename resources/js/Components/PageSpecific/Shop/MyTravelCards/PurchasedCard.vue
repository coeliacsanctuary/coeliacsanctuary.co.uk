<script setup lang="ts">
import { PurchasedCardIndex } from '@/types/Shop';
import Card from '@/Components/Card.vue';
import CountryFlag from '@/Components/CountryFlag.vue';
import { onMounted, ref } from 'vue';
import DigitalCardCountryList from '@/Components/PageSpecific/Shop/DigitalCards/DigitalCardCountryList.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import useBrowser from '@/composables/useBrowser';
import { DocumentTextIcon } from '@heroicons/vue/24/solid';

defineProps<{ card: PurchasedCardIndex }>();

const showCountryListSidebar = ref(false);

const showAppleWalletButton = ref(false);

onMounted(() => {
  showAppleWalletButton.value = useBrowser().isAppleDevice();
});
</script>

<template>
  <Card class="mx-auto w-full max-w-sm">
    <div class="mx-auto flex max-w-3xs flex-1 flex-col space-y-3">
      <div class="px-4 pt-2">
        <CountryFlag
          :code="card.flag"
          :country="card.language"
          width="256"
          height="192"
        />
      </div>

      <h2
        class="text-center text-lg font-semibold text-primary-dark md:text-xl"
        v-text="card.title"
      />

      <div class="flex-1" />

      <div class="flex items-center gap-2">
        <CoeliacButton
          :href="card.link"
          theme="secondary"
          label="View"
          bold
          :icon="DocumentTextIcon"
          icon-position="left"
          icon-classes="size-6!"
          classes="h-10 flex-1 justify-center text-center items-center text-base!"
          prefetch
        />

        <a
          v-if="showAppleWalletButton"
          :href="card.apple_wallet_link"
          class="shrink-0"
        >
          <img
            src="/images/svg/add-to-apple-wallet.svg"
            alt="Add to Apple Wallet"
            class="h-10 w-auto"
          />
        </a>
      </div>

      <button
        v-if="card.countries?.length"
        type="button"
        class="cursor-pointer text-center text-sm text-primary-dark transition hover:underline"
        @click="showCountryListSidebar = true"
      >
        Where can I use this card?
      </button>
    </div>
  </Card>

  <DigitalCardCountryList
    v-if="card.countries?.length"
    :title="card.title"
    :countries="card.countries"
    :show="showCountryListSidebar"
    @close="showCountryListSidebar = false"
  />
</template>
