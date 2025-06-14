<script setup lang="ts">
import Modal from '@/Components/Overlays/Modal.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { computed, onMounted, Ref, ref } from 'vue';
import type { Component } from 'vue';
import Loader from '@/Components/Loader.vue';
import axios, { AxiosResponse } from 'axios';
import { DataResponse } from '@/types/GenericTypes';
import { EditableEateryData, EditableEateryField } from '@/types/EateryTypes';
import FormTextarea from '@/Components/Forms/FormTextarea.vue';
import FormInput from '@/Components/Forms/FormInput.vue';
import FormSelect from '@/Components/Forms/FormSelect.vue';
import EaterySuggestEditOpeningTimes from '@/Components/PageSpecific/EatingOut/Details/Modals/EaterySuggestEditOpeningTimes.vue';
import { FormSelectOption } from '@/Components/Forms/Props';
import EaterySuggestEditFeatures from '@/Components/PageSpecific/EatingOut/Details/Modals/EaterySuggestEditFeatures.vue';

const props = defineProps<{
  eateryName: string;
  eateryId: number;
  show: boolean;
  isNationwide?: boolean;
}>();

const loading = ref(true);

const emits = defineEmits(['close', 'openReport']);

const editableData: Ref<EditableEateryData | undefined> =
  ref<EditableEateryData>();

const fields: Ref<EditableEateryField[]> = ref([]);

const newValue: Ref<string | number | null | Array<string | number | object>> =
  ref(null);

const parseFields = () => {
  if (!editableData.value) {
    return;
  }

  const eatery: EditableEateryData = editableData.value;

  fields.value = [
    {
      id: 'address',
      label: 'Address',
      shouldDisplay: !eatery.is_nationwide,
      getter: () => eatery.address.replaceAll('\n', ', '),
      isFormField: true,
      formField: {
        component: FormTextarea as Component,
        value: () => eatery.address,
        props: {
          rows: 5,
        },
      },
      updated: false,
    },
    {
      id: 'website',
      label: 'Website',
      shouldDisplay: true,
      getter: () => eatery.website || '',
      isFormField: true,
      formField: {
        component: FormInput as Component,
        value: () => eatery.website || '',
      },
      updated: false,
    },
    {
      id: 'gf_menu_link',
      label: 'Gluten Free Menu Link',
      shouldDisplay: true,
      getter: () => eatery.gf_menu_link || '',
      isFormField: true,
      formField: {
        component: FormInput as Component,
        value: () => eatery.gf_menu_link || '',
      },
      updated: false,
    },
    {
      id: 'phone',
      label: 'Phone Number',
      shouldDisplay: !eatery.is_nationwide,
      getter: () => eatery.phone || '',
      isFormField: true,
      formField: {
        component: FormInput as Component,
        value: () => eatery.phone || '',
      },
      updated: false,
    },
    {
      id: 'venue_type',
      label: 'Venue Type',
      shouldDisplay: true,
      getter: () => eatery.venue_type.label,
      isFormField: true,
      formField: {
        component: FormSelect as Component,
        value: () => eatery.venue_type.id,
        props: {
          options: eatery.venue_type.values.map(
            (value): FormSelectOption => ({
              label: value.label,
              value: value.value,
            }),
          ),
        },
      },
      updated: false,
    },
    {
      id: 'cuisine',
      label: 'Cuisine',
      shouldDisplay: eatery.type_id === 1,
      getter: () => eatery.cuisine.label,
      isFormField: true,
      formField: {
        component: FormSelect as Component,
        value: () => eatery.cuisine.id,
        props: {
          options: eatery.cuisine.values.map(
            (value): FormSelectOption => ({
              label: value.label,
              value: value.value,
            }),
          ),
        },
      },
      updated: false,
    },
    {
      id: 'opening_times',
      label: 'Opening Times',
      shouldDisplay: eatery.type_id !== 3 && !eatery.is_nationwide,
      getter: () => {
        if (!eatery.opening_times) {
          return null;
        }

        if (!eatery.opening_times.today) {
          return 'Currently closed';
        }

        return `${eatery.opening_times.today[0]} - ${eatery.opening_times.today[1]}`;
      },
      capitalise: true,
      isFormField: false,
      component: {
        component: EaterySuggestEditOpeningTimes as Component,
        change: (value: object[]): void => {
          newValue.value = value;
        },
        props: {
          currentOpeningTimes: eatery.opening_times,
        },
      },
      updated: false,
    },
    {
      id: 'features',
      label: 'Features',
      shouldDisplay: true,
      getter: () =>
        eatery.features.selected.map((feature) => feature.label).join(', '),
      isFormField: false,
      component: {
        component: EaterySuggestEditFeatures as Component,
        change: (value: object[]): void => {
          newValue.value = value;
        },
        props: {
          currentFeatures: eatery.features.values,
        },
      },
      updated: false,
    },
    {
      id: 'info',
      label: 'Additional Information',
      shouldDisplay: true,
      getter: () =>
        'Is there anything else we should know about this location?',
      truncate: false,
      isFormField: true,
      formField: {
        component: FormTextarea as Component,
        value: () => '',
      },
      updated: false,
    },
  ];
};

const loadData = () => {
  loading.value = true;

  axios
    .get(`/api/wheretoeat/${props.eateryId}/suggest-edit`)
    .then((response: AxiosResponse<DataResponse<EditableEateryData>>) => {
      editableData.value = response.data.data;
      loading.value = false;
      parseFields();
    });
};

onMounted(() => {
  loadData();
});

const close = () => {
  emits('close');
};

const editing: Ref<EditableEateryField | null> = ref(null);

const isFieldBeingEdited = (field: EditableEateryField): boolean =>
  field.label === editing.value?.label;

const isFieldNotBeingEdited = (field: EditableEateryField): boolean =>
  !isFieldBeingEdited(field);

const cancelEditingField = (): void => {
  if (!editing.value) {
    return;
  }

  editing.value = null;
  newValue.value = null;
};

const openField = (field: EditableEateryField): void => {
  if (editing.value) {
    cancelEditingField();
  }

  newValue.value = field.isFormField ? field.formField.value() : '';

  editing.value = field;
};

const isSubmitting = ref(false);

const currentFieldIndex = computed((): number | null => {
  if (!editing.value) {
    return null;
  }

  return fields.value.indexOf(editing.value);
});

const updateField = (): void => {
  if (!editing.value || !newValue.value) {
    return;
  }

  isSubmitting.value = true;

  axios
    .post(`/api/wheretoeat/${props.eateryId}/suggest-edit`, {
      field: editing.value.id,
      value: newValue.value,
    })
    .then(() => {
      fields.value[currentFieldIndex.value as number].updated = true;

      cancelEditingField();
    })
    .catch(() => {
      //
    })
    .finally(() => {
      isSubmitting.value = false;
    });
};

const openReport = () => {
  emits('close');
  emits('openReport');
};
</script>

<template>
  <Modal
    :open="show"
    size="medium"
    @close="close()"
  >
    <div
      class="border-grey-mid relative border-b bg-grey-light p-3 pr-[34px] text-center text-sm font-semibold"
    >
      Suggest Edits for {{ eateryName }}
    </div>
    <div class="p-3">
      <div
        v-if="loading"
        class="py-8"
      >
        <Loader
          color="primary"
          size="size-24"
          width="border-8"
          :absolute="false"
          display
        />
      </div>

      <template v-else>
        <div
          class="mb-4 flex flex-col space-y-3 border-b border-primary-light pb-4"
        >
          <p class="prose prose-sm">
            Can you improve the details we've got for
            <strong v-text="eateryName" />? Let us know using the options below,
            submit the details and we'll check it out!
          </p>

          <p
            class="prose prose-sm cursor-pointer border border-primary-light bg-primary-light/20 p-2"
            @click="openReport()"
          >
            <template v-if="!isNationwide">
              Has <strong v-text="eateryName" /> closed down, or no longer does
              gluten free? Click here to report it instead!
            </template>
            <template v-else>
              Does <strong v-text="eateryName" /> no longer do gluten free? Or
              has it changed procedures? Click here to report it instead!
            </template>
          </p>
        </div>

        <ul class="divide-blue-light flex flex-col divide-y">
          <template v-for="field in fields">
            <li
              v-if="field.shouldDisplay"
              :key="field.id"
              class="flex flex-col py-1"
            >
              <div
                v-if="field.updated"
                class="bg-blue-light/25 flex flex-col space-y-2 rounded-sm p-1 text-center"
              >
                <span>Thanks for your suggestion!</span>
                <span
                  class="cursor-pointer text-xs font-semibold hover:text-primary-dark"
                  @click="
                    field.updated = false;
                    openField(field);
                  "
                >
                  Update again
                </span>
              </div>

              <template v-else>
                <div class="flex w-full items-center justify-between">
                  <span
                    :class="
                      isFieldBeingEdited(field)
                        ? 'text-blue-dark text-sm font-semibold'
                        : ''
                    "
                    >{{ field.label }}</span
                  >
                  <span
                    v-if="isFieldNotBeingEdited(field)"
                    class="text-blue-dark cursor-pointer text-xs font-semibold transition hover:text-black"
                    @click="openField(field)"
                  >
                    Update
                  </span>
                </div>

                <div
                  v-if="isFieldNotBeingEdited(field)"
                  class="text-xs text-grey"
                  :class="{
                    capitalize: field.capitalise,
                    truncate:
                      field.truncate !== undefined ? field.truncate : true,
                  }"
                >
                  {{ field.getter() || 'Not set' }}
                </div>

                <div
                  v-if="isFieldBeingEdited(field)"
                  class="flex flex-col space-y-2"
                >
                  <template v-if="field.isFormField">
                    <component
                      :is="field.formField.component"
                      v-model="newValue"
                      :name="field.id"
                      :label="field.label"
                      v-bind="field.formField.props || null"
                      small
                      hide-label
                    />
                  </template>

                  <template v-else>
                    <component
                      :is="field.component.component"
                      v-bind="field.component.props"
                      @change="field.component.change"
                    />
                  </template>

                  <div
                    class="flex justify-between text-xs font-semibold xs:text-base"
                  >
                    <CoeliacButton
                      theme="secondary"
                      size="sm"
                      as="button"
                      type="button"
                      label="Cancel"
                      @click="cancelEditingField()"
                    ></CoeliacButton>

                    <CoeliacButton
                      size="sm"
                      as="button"
                      type="button"
                      label="Submit"
                      :disabled="newValue === ''"
                      :loading="isSubmitting"
                      @click="updateField()"
                    ></CoeliacButton>
                  </div>
                </div>
              </template>
            </li>
          </template>
        </ul>
      </template>
    </div>
  </Modal>
</template>
