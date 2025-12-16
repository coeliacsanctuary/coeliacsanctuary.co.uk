import { EventType } from '@/types/JourneyTypes';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { ShallowRef, watch } from 'vue';
import { useElementVisibility } from '@vueuse/core/index';

export default () => {
  const logWhenVisible = (
    templateRef: Readonly<ShallowRef>,
    type: EventType,
    identifier: string,
    data: object = {},
    sensitive: boolean = false,
  ) => {
    const targetIsVisible = useElementVisibility(templateRef);

    watch(targetIsVisible, (isVisible) => {
      if (isVisible) {
        logEvent(type, identifier, data, sensitive);
      }
    });
  };

  const logEvent = (
    type: EventType,
    identifier: string,
    data: object = {},
    sensitive: boolean = false,
  ) => {
    const page = usePage<{
      journey?: { token: string };
    }>();

    const token = page.props.journey?.token;

    axios
      .post('/api/event', {
        token,
        event_type: type,
        event_identifier: identifier,
        data,
        sensitive,
      })
      .then(() => {
        //
      })
      .catch(() => {
        //
      });
  };

  return { logWhenVisible, logEvent };
};
