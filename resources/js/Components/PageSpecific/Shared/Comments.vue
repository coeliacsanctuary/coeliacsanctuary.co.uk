<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { Comment } from '@/types/Types';
import { PaginatedResponse } from '@/types/GenericTypes';
import FormInput from '@/Components/Forms/FormInput.vue';
import { useForm } from '@inertiajs/vue3';
import FormTextarea from '@/Components/Forms/FormTextarea.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { ComponentPublicInstance, ref, useTemplateRef } from 'vue';
import {
  CheckCircleIcon,
  ChatBubbleLeftRightIcon,
  ArrowUturnLeftIcon,
} from '@heroicons/vue/24/outline';
import SubHeading from '@/Components/SubHeading.vue';
import { ucfirst } from '@/helpers';
import useJourneyTracking from '@/composables/useJourneyTracking';

const emits = defineEmits(['load-more', 'reset']);

const props = withDefaults(
  defineProps<{
    comments: PaginatedResponse<Comment>;
    module: 'blog' | 'recipe';
    id: number;
    isLoading?: boolean;
    hasLoadedMore?: boolean;
  }>(),
  {
    isLoading: false,
    hasLoadedMore: false,
  },
);

const form = useForm({
  module: props.module,
  id: props.id,
  name: '',
  email: '',
  comment: '',
});

const hasSubmitted = ref(false);

const commentSubmitting = ref(false);

const formCard = useTemplateRef<ComponentPublicInstance>('formCard');

const scrollToForm = (): void => {
  (formCard.value?.$el as HTMLElement | undefined)?.scrollIntoView({
    behavior: 'smooth',
    block: 'center',
  });
};

const submitComment = () => {
  commentSubmitting.value = true;

  form.post('/comments', {
    preserveScroll: true,
    onSuccess: () => {
      form.reset('name', 'email', 'comment');
      hasSubmitted.value = true;
    },
    onFinish: () => {
      commentSubmitting.value = false;
    },
  });
};

useJourneyTracking().logWhenVisible(
  useTemplateRef('card'),
  'scrolled_into_view',
  'CommentsCard',
  {
    page: props.module,
    id: props.id,
  },
);
</script>

<template>
  <Card
    ref="card"
    class="space-y-4"
  >
    <div class="flex items-baseline gap-3">
      <SubHeading
        as="h2"
        classes="text-primary-dark"
      >
        Comments
      </SubHeading>

      <span
        v-if="comments.meta.total"
        class="rounded-lg bg-primary/40 px-3 py-1 text-sm font-semibold"
        v-text="comments.meta.total"
      />
    </div>

    <div
      v-if="comments.data.length"
      class="flex flex-col space-y-4"
    >
      <article
        v-for="(comment, index) in comments.data"
        :key="`${comment.name}-${index}`"
        class="flex flex-col rounded-lg border border-primary-light bg-primary-light/25 p-4"
      >
        <header class="flex items-center gap-3">
          <span
            class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary/50 font-semibold text-primary-dark uppercase"
            aria-hidden="true"
            v-text="comment.name.charAt(0)"
          />

          <span class="flex flex-col leading-tight">
            <span
              class="font-semibold"
              v-text="comment.name"
            />

            <span
              class="text-xs text-grey"
              v-text="comment.published"
            />
          </span>
        </header>

        <div
          class="mt-3 text-sm whitespace-pre-line md:text-base"
          v-text="comment.comment.replaceAll('<br />', '\n')"
        />

        <div
          v-if="comment.reply"
          class="mt-4 flex flex-col rounded-lg border-l-4 border-secondary bg-white p-3 sm:ml-8"
        >
          <header class="flex items-center gap-2 text-sm">
            <ArrowUturnLeftIcon class="size-4 shrink-0 text-secondary" />

            <span class="font-semibold">Alison @ Coeliac Sanctuary</span>

            <span
              class="text-xs text-grey"
              v-text="comment.reply.published"
            />
          </header>

          <div
            class="prose prose-sm mt-2 max-w-none md:prose-base"
            v-html="comment.reply.comment"
          />
        </div>
      </article>

      <div
        class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-4"
      >
        <CoeliacButton
          v-if="comments.links.next"
          as="button"
          theme="light"
          size="xl"
          label="Load more comments..."
          class="h-full w-full flex-1 justify-center !text-xl"
          bold
          :loading="isLoading"
          @click="emits('load-more')"
        />

        <CoeliacButton
          v-if="hasLoadedMore"
          as="button"
          theme="secondary"
          size="xl"
          :label="`Back to ${ucfirst(module)}`"
          class="h-full justify-center !text-xl"
          bold
          @click="emits('reset')"
        />
      </div>
    </div>

    <div
      v-else
      class="flex flex-col items-center gap-3 py-8 text-center"
    >
      <span
        class="flex size-20 items-center justify-center rounded-full bg-primary-light/50"
      >
        <ChatBubbleLeftRightIcon class="size-10 text-primary-dark" />
      </span>

      <p class="text-lg font-semibold">No comments yet</p>

      <p class="max-w-md text-grey-dark">
        {{
          module === 'recipe'
            ? 'Made this one, or planning to? Let us know how you got on.'
            : 'Got something to add? Start the conversation.'
        }}
      </p>

      <CoeliacButton
        as="button"
        type="button"
        theme="primary"
        label="Leave a comment"
        bold
        class="mt-1 justify-center"
        @click="scrollToForm"
      />
    </div>
  </Card>

  <Card
    ref="formCard"
    class="mt-3"
  >
    <SubHeading classes="text-primary-dark">Leave a Comment</SubHeading>

    <p class="mt-3">
      Want to leave a comment on this {{ module }}? Feel free to join the
      discussion!
    </p>

    <form
      v-if="!hasSubmitted"
      class="mt-4 flex flex-col space-y-4"
      @submit.prevent="submitComment()"
    >
      <FormInput
        id="name"
        v-model="form.name"
        :error="form.errors.name"
        autocomplete="fullname"
        label="Your Name"
        name="name"
        required
        borders
      />

      <FormInput
        id="email"
        v-model="form.email"
        :error="form.errors.email"
        autocomplete="email"
        label="Email Address"
        name="email"
        required
        borders
        type="email"
      />

      <FormTextarea
        id="comment"
        v-model="form.comment"
        :error="form.errors.comment"
        label="Your Comment..."
        name="comment"
        required
        borders
      />

      <small class="text-xs italic sm:text-sm md:text-base">
        Note, your email address will never be displayed with your comment, it
        is only required to alert you when your comment has been approved or if
        the Coeliac Sanctuary team reply to your comment.
      </small>

      <div class="text-center">
        <CoeliacButton
          :loading="commentSubmitting"
          as="button"
          label="Submit Comment"
          theme="light"
          type="submit"
        />
      </div>
    </form>

    <template v-else>
      <div class="flex items-center justify-center text-center text-green">
        <CheckCircleIcon class="h-24 w-24" />
      </div>

      <p class="md:prose-md prose mb-2 max-w-none text-center">
        Thank you for submitting your comment! Your comment will be approved
        before appearing on the website.
      </p>
    </template>
  </Card>
</template>
