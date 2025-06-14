<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { Comment } from '@/types/Types';
import { PaginatedResponse } from '@/types/GenericTypes';
import FormInput from '@/Components/Forms/FormInput.vue';
import { useForm } from '@inertiajs/vue3';
import FormTextarea from '@/Components/Forms/FormTextarea.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { ref } from 'vue';
import { CheckCircleIcon } from '@heroicons/vue/24/outline';
import Heading from '@/Components/Heading.vue';
import SubHeading from '@/Components/SubHeading.vue';

const emits = defineEmits(['load-more']);

const props = defineProps<{
  comments: PaginatedResponse<Comment>;
  module: 'blog' | 'recipe';
  id: number;
}>();

const form = useForm({
  module: props.module,
  id: props.id,
  name: '',
  email: '',
  comment: '',
});

const hasSubmitted = ref(false);

const commentSubmitting = ref(false);

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
</script>

<template>
  <Card>
    <Heading as="h3">Your Comments</Heading>

    <div
      v-if="comments.data.length"
      class="flex flex-col space-y-4"
    >
      <div
        v-for="(comment, index) in comments.data"
        :key="`${comment.name}-${index}`"
        class="flex flex-col space-y-2 border-l-8 border-secondary bg-linear-to-br from-primary/30 to-primary-light/30 p-3 shadow-sm"
      >
        <div
          class="prose prose-sm max-w-none md:prose-base"
          v-text="comment.comment.replaceAll('<br />', '\n')"
        />
        <div class="flex space-x-2 text-xs font-medium text-grey">
          <span
            class="font-semibold"
            v-text="comment.name"
          />
          <span v-text="comment.published" />
        </div>
        <div
          v-if="comment.reply"
          class="mt-2 flex flex-col space-y-2 bg-white/80 p-3"
        >
          <div class="flex space-x-2 text-sm font-medium text-grey">
            <span
              class="font-semibold"
              v-text="'Alison @ Coeliac Sanctuary'"
            />
            <span v-text="comment.reply.published" />
          </div>
          <div
            class="prose prose-sm max-w-none md:prose-base"
            v-html="comment.reply.comment"
          />
        </div>
      </div>

      <div
        v-if="comments.links.next"
        class="hover:bg-primary-gradient-10 cursor-pointer border border-primary bg-linear-to-br from-primary/20 to-primary-light/20 p-1 text-center text-lg shadow-sm"
        @click="emits('load-more')"
        v-text="'Load more comments...'"
      />
    </div>

    <div
      v-else
      class="my-8 font-semibold"
    >
      There's no comments on this blog, why not leave one?
    </div>
  </Card>

  <Card>
    <SubHeading>Submit Comment</SubHeading>

    <p class="mt-3">
      Want to leave a comment on this blog? Feel free to join the discussion!
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
