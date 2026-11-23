<script lang="ts" setup>
import { computed, Ref, ref, watch } from 'vue';
import { useForm } from 'laravel-precognition-vue-inertia';
import { useHttp } from '@inertiajs/vue3';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import FormLookup from '@/Components/Forms/FormLookup.vue';
import FormSelect from '@/Components/Forms/FormSelect.vue';
import { LatLng } from '@/types/EateryTypes';
import {
  ArrowDownIcon,
  ArrowUpIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline';
import { Trip, TripLocation } from '@/types/TripPlannerTypes';

type PointRow = {
  key: string;
  location?: TripLocation;
  sessionToken: string;
};

const props = defineProps<{
  mode: 'new' | 'edit';
  trip?: Trip;
  canEdit?: boolean;
  destination?: TripLocation | null;
}>();

const emits = defineEmits(['success', 'processing']);

const makeRow = (location?: TripLocation): PointRow => ({
  key: crypto.randomUUID(),
  location,
  sessionToken: crypto.randomUUID(),
});

const initialRows = (): PointRow[] => {
  if (props.mode === 'edit' && props.trip?.points.length) {
    return props.trip.points.map((point) => makeRow(point));
  }

  return [makeRow(), makeRow(props.destination ?? undefined)];
};

const rows: Ref<PointRow[]> = ref(initialRows());

const lookups: Record<string, { setValue: (value: string) => void } | null> =
  {};

const isEditing = computed((): boolean => props.mode === 'edit');

const viaCount = computed((): number => rows.value.length - 2);

const allPointsSelected = computed((): boolean =>
  rows.value.every((row) => row.location !== undefined),
);

const isVia = (index: number): boolean =>
  index > 0 && index < rows.value.length - 1;

const rowLabel = (index: number): string => {
  if (index === 0) {
    return 'Starting from';
  }

  return index === rows.value.length - 1 ? 'Going to' : 'Via';
};

const addStop = () => {
  if (viaCount.value >= 5) {
    return;
  }

  rows.value.splice(rows.value.length - 1, 0, makeRow());
};

const moveStop = (index: number, direction: -1 | 1) => {
  const target = index + direction;

  if (!isVia(index) || !isVia(target)) {
    return;
  }

  [rows.value[index], rows.value[target]] = [
    rows.value[target],
    rows.value[index],
  ];
};

const removeStop = (index: number) => {
  if (!isVia(index)) {
    return;
  }

  rows.value.splice(index, 1);
};

const form = useForm<{
  points: TripLocation[];
  distance: number;
  stop_interval: number | '';
}>('post', '/wheretoeat/trip-planner', {
  points: [],
  distance: isEditing.value ? (props.trip?.distance ?? 2) : 2,
  stop_interval: isEditing.value ? (props.trip?.stopInterval ?? '') : '',
});

watch(
  () => form.processing,
  (processing) => emits('processing', processing),
);

const submitLabel = computed((): string => {
  if (!isEditing.value) {
    return 'Plan my trip';
  }

  return props.canEdit ? 'Update this trip' : 'Save as my own copy';
});

const distanceOptions = [1, 2, 3, 5, 10].map((miles) => ({
  value: miles,
  label: `Within ${miles} mile${miles > 1 ? 's' : ''} of my route`,
}));

const stopIntervalOptions = [
  { value: '', label: 'No stops' },
  { value: 60, label: 'Every hour' },
  { value: 90, label: 'Every 1½ hours' },
  { value: 120, label: 'Every 2 hours' },
  { value: 180, label: 'Every 3 hours' },
];

const lookupEndpoint = (row: PointRow): string =>
  `/api/wheretoeat/lookup?sessionToken=${row.sessionToken}`;

const placeLookup = useHttp<{ sessionToken: string }, LatLng>({
  sessionToken: '',
});

const lookupError = ref(false);

const errorMessage = computed((): string | undefined => {
  if (lookupError.value) {
    return "Sorry, I couldn't find that location...";
  }

  return form.hasErrors
    ? 'Sorry, there was an error planning your trip...'
    : undefined;
});

const selectLocation = (
  location: { id: string; label: string },
  row: PointRow,
) => {
  lookups[row.key]?.setValue(location.label);
  lookupError.value = false;
  placeLookup.sessionToken = row.sessionToken;

  placeLookup.get(`/api/wheretoeat/lookup/${encodeURIComponent(location.id)}`, {
    onSuccess: ({ lat, lng }) => {
      row.location = { label: location.label, lat, lng };
      row.sessionToken = crypto.randomUUID();
    },
    onHttpException: () => {
      lookupError.value = true;
    },
    onNetworkError: () => {
      lookupError.value = true;
    },
  });
};

const planTrip = () => {
  if (!allPointsSelected.value) {
    return;
  }

  form.points = rows.value.map((row) => row.location as TripLocation);

  const options = {
    onSuccess: () => emits('success'),
  };

  if (isEditing.value && props.canEdit && props.trip) {
    form.submit('patch', `/wheretoeat/trip-planner/${props.trip.key}`, options);

    return;
  }

  form.submit('post', '/wheretoeat/trip-planner', options);
};
</script>

<template>
  <form
    class="flex flex-col space-y-3"
    @submit.prevent="planTrip()"
  >
    <div
      v-for="(row, index) in rows"
      :key="row.key"
      class="flex items-end gap-2"
    >
      <FormLookup
        :ref="
          (el) =>
            (lookups[row.key] = el as {
              setValue: (value: string) => void;
            } | null)
        "
        :initial-value="row.location?.label"
        :name="`point-${index}`"
        borders
        :label="rowLabel(index)"
        class="relative flex-1"
        results-classes="absolute bg-white w-full z-20"
        placeholder="Address, town or postcode..."
        :lookup-endpoint="lookupEndpoint(row)"
        ignore-autofill
      >
        <template #item="location: { id: string; label: string }">
          <div
            class="cursor-pointer border-b border-grey-off p-2 transition hover:bg-grey-lightest"
            @click="selectLocation(location, row)"
            v-text="location.label"
          />
        </template>
      </FormLookup>

      <div
        v-if="isVia(index)"
        class="flex gap-1 pb-1"
      >
        <button
          type="button"
          class="cursor-pointer rounded-md border border-grey-off p-2 disabled:cursor-not-allowed disabled:opacity-40"
          :disabled="!isVia(index - 1)"
          @click="moveStop(index, -1)"
        >
          <ArrowUpIcon class="size-4" />
        </button>

        <button
          type="button"
          class="cursor-pointer rounded-md border border-grey-off p-2 disabled:cursor-not-allowed disabled:opacity-40"
          :disabled="!isVia(index + 1)"
          @click="moveStop(index, 1)"
        >
          <ArrowDownIcon class="size-4" />
        </button>

        <button
          type="button"
          class="cursor-pointer rounded-md border border-grey-off p-2"
          @click="removeStop(index)"
        >
          <XMarkIcon class="size-4" />
        </button>
      </div>
    </div>

    <div v-if="viaCount < 5">
      <CoeliacButton
        as="button"
        type="button"
        theme="light"
        size="sm"
        label="Add a stop"
        @click="addStop()"
      />
    </div>

    <FormSelect
      v-model="form.distance"
      name="distance"
      borders
      label="How far off your route?"
      help-text="How far from your route should I look for places to eat?"
      :options="distanceOptions"
    />

    <FormSelect
      v-model="form.stop_interval"
      name="stop_interval"
      borders
      label="Suggest stops"
      placeholder=""
      help-text="I'll suggest places to stop and eat at this interval."
      :options="stopIntervalOptions"
    />

    <p
      v-if="isEditing && !canEdit"
      class="text-grey-dark"
    >
      This trip was shared with you, so any changes will be saved as your own
      copy.
    </p>

    <CoeliacButton
      theme="primary"
      size="lg"
      as="button"
      type="submit"
      :label="submitLabel"
      bold
      classes="justify-center"
      :loading="form.processing"
      :disabled="!allPointsSelected"
    />

    <p
      v-if="errorMessage"
      class="text-red"
      v-text="errorMessage"
    />
  </form>
</template>
