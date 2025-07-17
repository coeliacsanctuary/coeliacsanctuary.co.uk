<template>
  <DefaultField
    :field="field"
    :errors="errors"
    :show-help-text="showHelpText"
    :full-width-content="fullWidthContent"
  >
    <template #field>
      <div class="map-container flex flex-col space-y-2">
        <div class="address-container flex flex-col space-y-2">
          <textarea
            :id="`${field.attribute}-address`"
            v-model="address"
            type="text"
            class="form-control form-input-bordered form-input h-auto w-full py-3"
            :class="errorClasses"
            placeholder="Address"
            rows="5"
          />

          <div class="flex space-x-2">
            <input
              :id="`${field.attribute}-latitude`"
              ref="latitude"
              v-model="latitude"
              type="text"
              class="form-control form-input-bordered form-input w-full"
              :class="errorClasses"
              placeholder="Latitude"
              disabled
            />

            <input
              :id="`${field.attribute}-longitude`"
              ref="longitude"
              v-model="longitude"
              type="text"
              class="form-control form-input-bordered form-input w-full"
              :class="errorClasses"
              placeholder="Longitude"
              disabled
            />
          </div>

          <DefaultButton
            size="sm"
            @click.prevent.stop="lookup()"
          >
            Lookup Address
          </DefaultButton>
        </div>
        <div
          ref="map"
          class="map h-full w-full"
        >
          Map
        </div>
      </div>
    </template>
  </DefaultField>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova';

export default {
  mixins: [FormField, HandlesValidationErrors],

  props: ['resourceName', 'resourceId', 'field'],

  data: () => ({
    isLookingUp: false,
    address: '',
    latitude: 55.309322,
    longitude: -4.174804,
    zoom: 4,
    mapOptions: {},
    map: null,
    marker: null,
  }),

  mounted() {
    this.initMap();

    google.maps.event.addListener(this.map, 'bounds_changed', () => {
      const center = this.map.getBounds().getCenter();

      this.latitude = center.lat();
      this.longitude = center.lng();

      this.marker.setPosition(
        new google.maps.LatLng(this.latitude, this.longitude),
      );
    });
  },

  methods: {
    /*
     * Set the initial, internal value for the field.
     */
    setInitialValue() {
      this.value = JSON.parse(this.field.value);

      this.address = this.value.address;

      if (this.address) {
        this.latitude = this.value.latitude !== 0 ? this.value.latitude : this.latitude;
        this.longitude = this.value.longitude !== 0 ? this.value.longitude : this.longitude;
        this.zoom = 16;
      }
    },

    initMap() {
      this.mapOptions = {
        center: new google.maps.LatLng(this.latitude, this.longitude),
        zoom: this.zoom,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        gestureHandling: 'greedy',
        mapTypeControl: false,
        streetViewControl: false,
        rotateControl: false,
        fullscreenControl: false,
      };

      this.map = new google.maps.Map(this.$refs.map, this.mapOptions);

      this.marker = new google.maps.Marker({
        position: new google.maps.LatLng(this.latitude, this.longitude),
        map: this.map,
      });
    },

    lookup() {
      this.isLookingUp = true;

      Nova.request()
        .post('/nova-vendor/jpeters8889/address-field/lookup', {
          address: this.address,
        })
        .then((response) => {
          this.address = response.data.formatted_address.replace(/, /g, '\n');
          this.latitude = response.data.lat;
          this.longitude = response.data.lng;

          const latLng = new google.maps.LatLng(this.latitude, this.longitude);

          this.marker.setPosition(latLng);
          this.map.setCenter(latLng);
          this.map.setZoom(16);
        })
        .finally(() => (this.isLookingUp = false));
    },

    /**
     * Fill the given FormData object with the field's internal value.
     */
    fill(formData) {
      const value = JSON.stringify({
        address: this.address,
        latitude: this.latitude,
        longitude: this.longitude,
      });

      formData.append(this.field.attribute, value);
    },
  },
};
</script>

<style>
@media (min-width: 1200px) {
  .map-container {
    flex-direction: row !important;
    column-gap: 1rem;
  }

  .map-container .address-container,
  .map-container .map {
    width: 50%;
  }

  .map-container .map {
    margin-top: 0 !important;
  }
}

.map {
  min-height: 250px;
}
</style>
