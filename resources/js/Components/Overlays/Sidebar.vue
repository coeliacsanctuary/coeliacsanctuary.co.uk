<script lang="ts" setup>
import {
  Dialog,
  DialogPanel,
  TransitionChild,
  TransitionRoot,
} from '@headlessui/vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import { onMounted, ref } from 'vue';

const emit = defineEmits(['close']);

withDefaults(
  defineProps<{
    open: boolean;
    side: 'left' | 'right';
    size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl' | '2xl';
  }>(),
  { side: 'left', size: 'xs' },
);

const closeOverlay = () => emit('close');

const isMounted = ref(false);

onMounted(() => {
  isMounted.value = true;
});
</script>

<template>
  <Teleport
    v-if="isMounted"
    to="body"
  >
    <TransitionRoot
      :show="open"
      as="template"
    >
      <Dialog
        as="div"
        class="relative z-50"
        @close="closeOverlay()"
      >
        <TransitionChild
          as="template"
          enter="ease-in-out duration-500"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="ease-in-out duration-500"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black/70 transition-opacity" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-hidden">
          <div class="absolute inset-0 overflow-hidden">
            <div
              :class="side === 'left' ? 'left-0 pr-10' : 'right-0 pl-10'"
              class="pointer-events-none fixed inset-y-0 flex max-w-full"
            >
              <TransitionChild
                :enter-from="
                  side === 'left' ? '-translate-x-full' : 'translate-x-full'
                "
                :leave-to="
                  side === 'left' ? '-translate-x-full' : 'translate-x-full'
                "
                as="template"
                enter="transform transition ease-in-out duration-500 sm:duration-700"
                enter-to="translate-x-0"
                leave="transform transition ease-in-out duration-500 sm:duration-700"
                leave-from="translate-x-0"
              >
                <DialogPanel
                  class="pointer-events-auto relative w-screen"
                  :class="{
                    'max-w-xs': size === 'xs',
                    'max-w-sm': size === 'sm',
                    'max-w-md': size === 'md',
                    'max-w-lg': size === 'lg',
                    'max-w-xl': size === 'xl',
                    'max-w-2xl': size === '2xl',
                  }"
                >
                  <TransitionChild
                    as="template"
                    enter="ease-in-out duration-500"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="ease-in-out duration-500"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                  >
                    <div
                      :class="
                        side === 'left'
                          ? 'right-0 -mr-8 pl-2 sm:-mr-10 sm:pl-4'
                          : 'left-0 -ml-8 pr-2 sm:-ml-10 sm:pr-4'
                      "
                      class="absolute top-0 flex pt-4"
                    >
                      <button
                        class="rounded-md text-gray-300 hover:text-white focus:outline-hidden"
                        type="button"
                        @click="closeOverlay()"
                      >
                        <XMarkIcon
                          aria-hidden="true"
                          class="h-6 w-6"
                        />
                      </button>
                    </div>
                  </TransitionChild>
                  <div class="flex h-full flex-col overflow-y-scroll shadow-xl">
                    <slot />
                  </div>
                </DialogPanel>
              </TransitionChild>
            </div>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </Teleport>
</template>
