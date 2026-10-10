<script setup lang="ts">
import { PurchasedCardIndex } from '@/types/Shop';
import Card from '@/Components/Card.vue';
import CountryFlag from '@/Components/CountryFlag.vue';
import { onMounted, ref } from 'vue';
import DigitalCardCountryList from '@/Components/PageSpecific/Shop/DigitalCards/DigitalCardCountryList.vue';
import Modal from '@/Components/Overlays/Modal.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import useBrowser from '@/composables/useBrowser';
import { DocumentTextIcon } from '@heroicons/vue/24/solid';

defineProps<{ card: PurchasedCardIndex }>();

const showCountryListSidebar = ref(false);

const showAppleWalletButton = ref(false);

const showAppleWalletModal = ref(false);

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

        <button
          v-if="showAppleWalletButton"
          type="button"
          class="shrink-0 cursor-pointer"
          @click="showAppleWalletModal = true"
        >
          <img
            src="/images/svg/add-to-apple-wallet.svg"
            alt="Add to Apple Wallet"
            class="h-10 w-auto"
          />
        </button>
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

  <Modal
    :open="showAppleWalletModal"
    size="small"
    @close="showAppleWalletModal = false"
  >
    <template #header>
      <h3 class="pl-3 font-semibold lg:text-lg">
        Before you add this to Apple Wallet
      </h3>
    </template>

    <div class="flex flex-col space-y-2 p-2">
      <p class="prose lg:prose-lg">
        Apple limits how much text a wallet pass can show on its front, so the
        full translation can't be displayed there. The front of the pass carries
        the card name and language; the translation itself sits behind the pass
        details.
      </p>

      <p class="prose lg:prose-lg">
        To read it, open the pass, tap &bull;&bull;&bull; and choose Pass
        Details.
      </p>

      <p class="prose lg:prose-lg">
        For the clearest view, use the browser version of your card &mdash; the
        full translation is laid out on one screen, as it's meant to be read.
      </p>
    </div>

    <template #footer>
      <div class="flex items-center justify-center">
        <a
          :href="card.apple_wallet_link"
          class="p-1.5"
          @click="showAppleWalletModal = false"
        >
          <img
            src="/images/svg/add-to-apple-wallet.svg"
            alt="Add to Apple Wallet"
            class="h-14 w-auto"
          />
        </a>
      </div>
    </template>
  </Modal>
</template>
