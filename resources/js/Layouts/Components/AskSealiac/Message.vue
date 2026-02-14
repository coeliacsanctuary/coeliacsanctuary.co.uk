<script setup lang="ts">
import { Role } from '@/types/AskSealiac';
import SealiacSeal from '@/Svg/SealiacSeal.vue';
import { computed } from 'vue';
import { marked } from 'marked';
import { UserIcon } from '@heroicons/vue/16/solid';

const props = defineProps<{ role: Role; message: string }>();

const parsedMessage = computed(() =>
  props.message ? (marked.parse(props.message) as string) : '',
);
</script>

<template>
  <div
    class="flex"
    :class="{ 'flex-row-reverse': role === 'user' }"
    :data-role="role"
  >
    <div>
      <div
        class="size-10 rounded p-1 xs:size-12"
        :class="
          role === 'assistant'
            ? 'mr-2 bg-primary/50 xs:mr-4'
            : 'ml-2 bg-primary-light/50 xs:ml-4'
        "
      >
        <SealiacSeal v-if="role === 'assistant'" />
        <UserIcon
          v-if="role === 'user'"
          class="opacity-50"
        />
      </div>
    </div>
    <div
      class="prose prose-sm min-w-0 flex-1 rounded-lg p-2 break-words sm:prose sm:px-4"
      :class="role === 'assistant' ? 'bg-primary/50' : 'bg-primary-light/50'"
      v-html="parsedMessage"
    />
  </div>
</template>
