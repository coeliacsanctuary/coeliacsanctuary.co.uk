<script lang="ts" setup>
import Sidebar from '@/Components/Overlays/Sidebar.vue';
import { computed, onMounted, ref, watch } from 'vue';
import { marked } from 'marked';
import UseGoogleEvents from '@/composables/useGoogleEvents';
import PromptBox from '@/Layouts/Components/AskSealiac/PromptBox.vue';
import Messages from '@/Layouts/Components/AskSealiac/Messages.vue';
import { Message, Role } from '@/types/AskSealiac';
import { useStream } from '@laravel/stream-vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import useLocalStorage from '@/composables/useLocalStorage';
import ConfirmModal from '@/Components/ConfirmModal.vue';

const emit = defineEmits(['close']);

const props = defineProps<{ open: boolean }>();

const close = () => emit('close');

const chatId = ref<string>('');
const messages = ref<Message[]>([]);
const showClearChatConfirmation = ref(false);

const { isInLocalStorage, getFromLocalStorage, putInLocalStorage } =
  useLocalStorage();

const { data, isFetching, isStreaming, send } = useStream('/api/ask-sealiac', {
  onFinish: () => {
    if (data.value === '') {
      return;
    }

    messages.value.push({
      role: 'assistant',
      message: data.value,
    });
  },
});

onMounted(() => {
  if (isInLocalStorage('ask-sealiac-chat')) {
    type Payload = { messages: Message[]; chatId: string };

    const history: Payload = getFromLocalStorage<Payload>('ask-sealiac-chat', {
      messages: [],
      chatId: '',
    }) as Payload;

    if (history.messages && history.chatId) {
      messages.value = history.messages;
      chatId.value = history.chatId;

      return;
    }
  }

  generateChatId();
});

const generateChatId = () => {
  chatId.value = crypto.randomUUID().replace(/-/g, '').substring(0, 8);
};

const sendGreetingPrompt = () => {
  sendPrompt(
    'Use the `Greeting` tool, and introduce yourself to the user',
    'assistant',
  );
};

const sendPrompt = (prompt: string, role: Role = 'user') => {
  if (role === 'user') {
    messages.value.push({
      role,
      message: prompt,
    });
  }

  send({
    prompt,
    messages: messages.value,
    chatId: chatId.value,
  });
};

const resetChat = () => {
  generateChatId();
  messages.value = [];
  showClearChatConfirmation.value = false;

  sendGreetingPrompt();
};

watch(
  () => props.open,
  () => {
    if (!props.open) {
      return;
    }

    if (messages.value.length === 0) {
      sendGreetingPrompt();
    }

    UseGoogleEvents().googleEvent('event', 'ask-sealiac', {
      event_category: 'opened-ask-sealiac',
    });
  },
);

watch(
  messages,
  () => {
    putInLocalStorage('ask-sealiac-chat', {
      messages: messages.value,
      chatId: chatId.value,
    });
  },
  { deep: true },
);
</script>

<template>
  <Sidebar
    :open="open"
    side="right"
    size="xl"
    @close="!showClearChatConfirmation ? close() : undefined"
  >
    <div class="flex h-full flex-col bg-white">
      <div
        v-if="messages.length >= 2"
        class="flex items-center justify-between border-b border-grey-off p-2 sm:px-4"
      >
        <div>
          <span
            v-if="messages.length >= 40"
            class="text-xs font-semibold"
          >
            {{ messages.length }}/50 messages exchanged.
          </span>
        </div>
        <CoeliacButton
          label="New Chat"
          theme="ghost"
          size="sm"
          bold
          class="!text-xs uppercase"
          as="button"
          @click="showClearChatConfirmation = true"
        />
      </div>

      <Messages
        :messages="messages"
        :is-fetching="isFetching"
        :is-streaming="isStreaming"
        :streamed-message="data"
      />

      <PromptBox
        :is-fetching="isFetching"
        :is-streaming="isStreaming"
        :limit-reached="messages.length === 50"
        @send-prompt="(prompt) => sendPrompt(prompt)"
      />
    </div>
  </Sidebar>

  <ConfirmModal
    :show="showClearChatConfirmation"
    @cancel="showClearChatConfirmation = false"
    @confirm="() => resetChat()"
  >
    Are you sure you want to start a new chat? All previous history will be
    lost.
  </ConfirmModal>
</template>
