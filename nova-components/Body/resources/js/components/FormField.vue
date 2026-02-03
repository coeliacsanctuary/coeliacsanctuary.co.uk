<template>
  <DefaultField
    :field="field"
    :errors="errors"
    :show-help-text="showHelpText"
    :full-width-content="fullWidthContent"
  >
    <template #field>
      <div class="flex flex-wrap gap-2">
        <Button
          variant="outline"
          as="span"
          @click="showHeadingModal = true"
        >
          Add Header
        </Button>

        <Button
          variant="outline"
          as="span"
          @click="showButtonModal = true"
        >
          Add Button
        </Button>

        <Button
          variant="outline"
          as="span"
          @click="showIframeModal = true"
        >
          Add iFrame
        </Button>
      </div>

      <textarea
        :id="field.attribute"
        v-model="value"
        :rows="field.rows"
        class="form-control form-input-bordered form-input h-auto w-full py-3"
        :class="errorClasses"
        :placeholder="field.name"
      />

      <p
        v-if="customError"
        class="my-2 text-red-500"
        v-text="customError"
      />

      <Modal
        :show="showHeadingModal"
        size="3xl"
      >
        <form
          class="mx-auto overflow-hidden rounded-lg bg-white shadow-lg dark:bg-gray-800"
          @submit.prevent="addHeader"
        >
          <slot>
            <ModalHeader v-text="'Insert Heading'" />

            <ModalContent>
              <div class="flex flex-col md:flex-row">
                <div class="mt-2 w-full px-6 md:mt-0 md:w-1/5 md:px-8 md:py-5">
                  <label
                    for="title-introduction-text-field"
                    class="inline-block pt-2 leading-tight"
                  >
                    Heading
                  </label>
                </div>
                <div
                  class="mt-1 w-full px-6 pb-5 md:mt-0 md:w-4/5 md:px-8 md:py-5"
                >
                  <div class="space-y-1">
                    <input
                      v-model="heading"
                      type="text"
                      class="form-control form-input-bordered form-input h-auto w-full py-3"
                    />
                  </div>
                </div>
              </div>
            </ModalContent>
          </slot>

          <ModalFooter>
            <div class="ml-auto">
              <Button
                variant="link"
                type="button"
                data-testid="cancel-button"
                dusk="cancel-delete-button"
                class="mr-3"
                @click="showHeadingModal = false"
              >
                Cancel
              </Button>

              <Button type="submit"> Insert </Button>
            </div>
          </ModalFooter>
        </form>
      </Modal>

      <Modal
        :show="showButtonModal"
        size="3xl"
      >
        <form
          class="mx-auto overflow-hidden rounded-lg bg-white shadow-lg dark:bg-gray-800"
          @submit.prevent="addButton"
        >
          <slot>
            <ModalHeader v-text="'Insert Button'" />

            <ModalContent>
              <div class="mb-2 flex flex-col md:flex-row">
                <div class="mt-2 w-full px-6 md:mt-0 md:w-1/5 md:px-8 md:py-5">
                  <label
                    for="title-introduction-text-field"
                    class="inline-block pt-2 leading-tight"
                  >
                    Theme
                  </label>
                </div>
                <div
                  class="mt-1 w-full px-6 pb-5 md:mt-0 md:w-4/5 md:px-8 md:py-5"
                >
                  <div class="space-y-1">
                    <select
                      v-model="buttonTheme"
                      class="form-control form-input-bordered form-input w-full"
                    >
                      <option value="primary">Primary (Blue)</option>
                      <option value="secondary">Secondary (Yellow)</option>
                      <option value="light">Light Blue</option>
                      <option value="negative">Negative (Red)</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="mb-2 flex flex-col md:flex-row">
                <div class="mt-2 w-full px-6 md:mt-0 md:w-1/5 md:px-8 md:py-5">
                  <label
                    for="title-introduction-text-field"
                    class="inline-block pt-2 leading-tight"
                  >
                    Size
                  </label>
                </div>
                <div
                  class="mt-1 w-full px-6 pb-5 md:mt-0 md:w-4/5 md:px-8 md:py-5"
                >
                  <div class="space-y-1">
                    <select
                      v-model="buttonSize"
                      class="form-control form-input-bordered form-input w-full"
                    >
                      <option value="sm">Small</option>
                      <option value="md">Medium</option>
                      <option value="lg">Large</option>
                      <option value="xl">XL</option>
                      <option value="xxl">XXL</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="mb-2 flex flex-col md:flex-row">
                <div class="mt-2 w-full px-6 md:mt-0 md:w-1/5 md:px-8 md:py-5">
                  <label
                    for="title-introduction-text-field"
                    class="inline-block pt-2 leading-tight"
                  >
                    Label
                  </label>
                </div>
                <div
                  class="mt-1 w-full px-6 pb-5 md:mt-0 md:w-4/5 md:px-8 md:py-5"
                >
                  <div class="space-y-1">
                    <input
                      v-model="buttonLabel"
                      type="text"
                      class="form-control form-input-bordered form-input h-auto w-full py-3"
                    />
                  </div>
                </div>
              </div>
              <div class="mb-2 flex flex-col md:flex-row">
                <div class="mt-2 w-full px-6 md:mt-0 md:w-1/5 md:px-8 md:py-5">
                  <label
                    for="title-introduction-text-field"
                    class="inline-block pt-2 leading-tight"
                  >
                    Button Link
                  </label>
                </div>
                <div
                  class="mt-1 w-full px-6 pb-5 md:mt-0 md:w-4/5 md:px-8 md:py-5"
                >
                  <div class="space-y-1">
                    <input
                      v-model="buttonHref"
                      type="url"
                      class="form-control form-input-bordered form-input h-auto w-full py-3"
                    />
                  </div>
                </div>
              </div>
              <div class="mb-2 flex flex-col md:flex-row">
                <div class="mt-2 w-full px-6 md:mt-0 md:w-1/5 md:px-8 md:py-5">
                  <label
                    for="title-introduction-text-field"
                    class="inline-block pt-2 leading-tight"
                  >
                    Open in new tab
                  </label>
                </div>
                <div
                  class="mt-1 w-full px-6 pb-5 md:mt-0 md:w-4/5 md:px-8 md:py-5"
                >
                  <div class="space-y-1">
                    <Checkbox v-model="buttonNewWindow" />
                  </div>
                </div>
              </div>
              <div class="mb-2 flex flex-col md:flex-row">
                <div class="mt-2 w-full px-6 md:mt-0 md:w-1/5 md:px-8 md:py-5">
                  <label
                    for="title-introduction-text-field"
                    class="inline-block pt-2 leading-tight"
                  >
                    Make text bold
                  </label>
                </div>
                <div
                  class="mt-1 w-full px-6 pb-5 md:mt-0 md:w-4/5 md:px-8 md:py-5"
                >
                  <div class="space-y-1">
                    <Checkbox v-model="buttonBoldText" />
                  </div>
                </div>
              </div>
              <div class="mb-2 flex flex-col md:flex-row">
                <div class="mt-2 w-full px-6 md:mt-0 md:w-1/5 md:px-8 md:py-5">
                  <label
                    for="title-introduction-text-field"
                    class="inline-block pt-2 leading-tight"
                  >
                    Wrapper custom styles
                  </label>
                </div>
                <div
                  class="mt-1 w-full px-6 pb-5 md:mt-0 md:w-4/5 md:px-8 md:py-5"
                >
                  <div class="space-y-1">
                    <input
                      v-model="buttonWrapperStyles"
                      type="text"
                      class="form-control form-input-bordered form-input h-auto w-full py-3"
                    />
                  </div>
                </div>
              </div>
              <div class="mb-2 flex flex-col md:flex-row">
                <div class="mt-2 w-full px-6 md:mt-0 md:w-1/5 md:px-8 md:py-5">
                  <label
                    for="title-introduction-text-field"
                    class="inline-block pt-2 leading-tight"
                  >
                    Preview
                  </label>
                </div>
                <div
                  class="mt-1 w-full px-6 pb-5 md:mt-0 md:w-4/5 md:px-8 md:py-5"
                >
                  <div class="space-y-1 border p-2">
                    <iframe
                      :src="buttonPreviewUrl"
                      class="w-full"
                      style="height: 100px"
                    />
                  </div>
                </div>
              </div>
            </ModalContent>
          </slot>

          <ModalFooter>
            <div class="ml-auto">
              <Button
                variant="link"
                type="button"
                data-testid="cancel-button"
                dusk="cancel-delete-button"
                class="mr-3"
                @click="showButtonModal = false"
              >
                Cancel
              </Button>

              <Button type="submit"> Insert </Button>
            </div>
          </ModalFooter>
        </form>
      </Modal>

      <Modal
        :show="showIframeModal"
        size="3xl"
      >
        <form
          class="mx-auto overflow-hidden rounded-lg bg-white shadow-lg dark:bg-gray-800"
          @submit.prevent="addIframe"
        >
          <slot>
            <ModalHeader v-text="'Insert iFrame'" />

            <ModalContent>
              <div class="flex flex-col md:flex-row">
                <div class="mt-2 w-full px-6 md:mt-0 md:w-1/5 md:px-8 md:py-5">
                  <label
                    for="title-introduction-text-field"
                    class="inline-block pt-2 leading-tight"
                  >
                    URL
                  </label>
                </div>
                <div
                  class="mt-1 w-full px-6 pb-5 md:mt-0 md:w-4/5 md:px-8 md:py-5"
                >
                  <div class="space-y-1">
                    <input
                      v-model="url"
                      type="url"
                      class="form-control form-input-bordered form-input h-auto w-full py-3"
                    />
                  </div>
                </div>
              </div>
            </ModalContent>
          </slot>

          <ModalFooter>
            <div class="ml-auto">
              <Button
                variant="link"
                type="button"
                data-testid="cancel-button"
                dusk="cancel-delete-button"
                class="mr-3"
                @click="showIframeModal = false"
              >
                Cancel
              </Button>

              <Button type="submit"> Insert </Button>
            </div>
          </ModalFooter>
        </form>
      </Modal>
    </template>
  </DefaultField>
</template>

<script>
import { Button, Checkbox } from 'laravel-nova-ui';
import { FormField, HandlesValidationErrors } from 'laravel-nova';
import debounce from 'lodash/debounce';

export default {
  components: { Button, Checkbox },

  mixins: [FormField, HandlesValidationErrors],

  props: ['resourceName', 'resourceId', 'field'],

  data: () => ({
    customError: null,
    showHeadingModal: false,
    showButtonModal: false,
    showIframeModal: false,

    heading: '',
    buttonTheme: 'primary',
    buttonSize: 'md',
    buttonHref: '',
    buttonNewWindow: false,
    buttonBoldText: false,
    buttonLabel: '',
    buttonWrapperStyles: '',
    url: '',
  }),

  watch: {
    value: {
      handler: debounce(function (newVal) {
        this.validateHtml(newVal);
      }, 300),
      immediate: true, // Optional: validate on load
    },
  },

  methods: {
    /*
     * Set the initial, internal value for the field.
     */
    setInitialValue() {
      this.value = this.field.value || '';
    },

    /**
     * Fill the given FormData object with the field's internal value.
     */
    fill(formData) {
      formData.append(this.field.attribute, this.value || '');
    },

    addHeader() {
      this.insertText(`<article-header>${this.heading}</article-header>`);

      this.showHeadingModal = false;
    },

    addIframe() {
      this.insertText(`<article-iframe src="${this.url}"></article-iframe>`);

      this.showIframeModal = false;
    },

    addButton() {
      const code = `<article-button theme="${this.buttonTheme}" size="${this.buttonSize}" href="${this.buttonHref}" ${this.buttonNewWindow ? 'target="_blank"' : ''} wrapper-styles="${this.buttonWrapperStyles}" ${this.buttonBoldText ? 'bold' : ''}>${this.buttonLabel}</article-button>`;

      this.insertText(code);

      this.showButtonModal = false;
    },

    insertText(text) {
      const bodyField = document.getElementById(this.field.attribute);

      const cursorPosition = bodyField.selectionStart;

      const textBefore = this.value.substring(0, cursorPosition);
      const textAfter = this.value.substring(cursorPosition, this.value.length);

      this.value = textBefore + text + textAfter;
    },

    validateHtml(content) {
      console.log('validating');

      const parser = new DOMParser();
      const doc = parser.parseFromString(`<div>${content}</div>`, 'text/html');
      const hasError = doc.querySelector('parsererror');

      console.log({ hasError, doc });

      if (hasError) {
        this.customError =
          'Invalid HTML detected. Please check for broken or mismatched tags.';

        return;
      }

      const mismatchedCaseTags =
        /<([a-zA-Z][a-zA-Z0-9]*)\b[^>]*>([^<>]*)<\/([a-zA-Z][a-zA-Z0-9]*)>/g;

      let match;
      while ((match = mismatchedCaseTags.exec(content)) !== null) {
        const [one, open, two, close] = match;

        if (open !== close) {
          this.customError = `Mismatched tag casing detected: <${open}>...</${close}>. Tag names must match exactly.`;
          return;
        }
      }

      this.customError = null;
    },
  },

  computed: {
    buttonPreviewUrl() {
      const url = new URL(`${window.location.origin}/cs-adm/preview-button`);

      url.searchParams.set('size', this.buttonSize);
      url.searchParams.set('theme', this.buttonTheme);
      url.searchParams.set('label', this.buttonLabel);
      url.searchParams.set('href', this.buttonHref);
      url.searchParams.set('wrapperStyles', this.buttonWrapperStyles);

      if (this.buttonBoldText) {
        url.searchParams.set('bold', '1');
      }

      return url.toString();
    },
  },
};
</script>
