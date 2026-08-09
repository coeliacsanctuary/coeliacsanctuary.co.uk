import { ref, Ref } from 'vue';
import { LatLng } from '@/helpers';

export type GeolocationError =
  | 'unsupported'
  | 'denied'
  | 'unavailable'
  | 'timeout';

const messages: Record<GeolocationError, string> = {
  unsupported: "Your browser doesn't support location sharing.",
  denied:
    'Location access was denied, check your browser settings to enable it.',
  unavailable: "We couldn't work out where you are, please try again.",
  timeout: 'Finding your location took too long, please try again.',
};

/**
 * Thin wrapper around the browser geolocation API. Knows nothing about what the
 * position is used for.
 */
export default () => {
  const hasWindowObject: boolean = typeof window !== 'undefined';

  const isSupported: boolean = hasWindowObject && 'geolocation' in navigator;

  const isLocating = ref(false);
  const coords: Ref<LatLng | undefined> = ref();
  const error: Ref<GeolocationError | undefined> = ref();

  const errorFor = (code: number): GeolocationError => {
    if (code === GeolocationPositionError.PERMISSION_DENIED) {
      return 'denied';
    }

    if (code === GeolocationPositionError.TIMEOUT) {
      return 'timeout';
    }

    return 'unavailable';
  };

  const locate = (): Promise<LatLng | undefined> => {
    error.value = undefined;

    if (!isSupported) {
      error.value = 'unsupported';

      return Promise.resolve(undefined);
    }

    isLocating.value = true;

    return new Promise((resolve) => {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          coords.value = {
            lat: position.coords.latitude,
            lng: position.coords.longitude,
          };

          isLocating.value = false;

          resolve(coords.value);
        },
        (positionError) => {
          error.value = errorFor(positionError.code);
          isLocating.value = false;

          resolve(undefined);
        },
      );
    });
  };

  const errorMessage = (): string | undefined =>
    error.value ? messages[error.value] : undefined;

  return { isSupported, isLocating, coords, error, errorMessage, locate };
};
