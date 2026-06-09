<script setup lang="ts">
import { Message as MessageType } from '@/types/AskSealiac';
import Message from '@/Layouts/Components/AskSealiac/Message.vue';
import MessageLoader from '@/Layouts/Components/AskSealiac/MessageLoader.vue';
import { nextTick, onMounted, ref, TransitionGroup, watch } from 'vue';

const props = defineProps<{
  messages: MessageType[];
  isFetching: boolean;
  isStreaming: boolean;
  streamedMessage: string;
}>();

const container = ref<HTMLElement>();
const isNearBottom = ref(true);

const checkIfNearBottom = () => {
  if (!container.value) {
    return;
  }

  const threshold = 50;
  const { scrollTop, scrollHeight, clientHeight } = container.value;

  isNearBottom.value = scrollHeight - scrollTop - clientHeight < threshold;
};

const scrollToBottom = () => {
  if (container.value && isNearBottom.value) {
    container.value.scrollTop = container.value.scrollHeight;
  }
};

onMounted(() => {
  if (props.messages.length > 0) {
    scrollToBottom();
  }
});

watch(
  () => props.messages.length,
  () => nextTick(scrollToBottom),
);

watch(() => props.streamedMessage, scrollToBottom);
</script>

<template>
  <div
    ref="container"
    class="flex min-h-0 min-w-0 flex-1 flex-col space-y-4 overflow-x-hidden overflow-y-auto p-2 sm:p-4"
    @scroll="checkIfNearBottom"
  >
    <TransitionGroup name="message">
      <Message
        v-for="(message, index) in messages"
        :key="index"
        :role="message.role"
        :message="message.message"
      />

      <MessageLoader
        v-if="
          (!streamedMessage || streamedMessage === '') &&
          (isFetching || isStreaming)
        "
        key="loader"
        data-role="loader"
      />

      <Message
        v-if="isStreaming && streamedMessage && streamedMessage !== ''"
        key="streaming"
        role="assistant"
        :message="streamedMessage"
      />
    </TransitionGroup>
  </div>
</template>

<style scoped>
.message-enter-active[data-role='user'],
.message-enter-active[data-role='loader'] {
  transition:
    opacity 0.3s ease-out,
    transform 0.3s ease-out;
}

.message-enter-from[data-role='user'],
.message-enter-from[data-role='loader'] {
  opacity: 0;
  transform: translateY(8px);
}
</style>
