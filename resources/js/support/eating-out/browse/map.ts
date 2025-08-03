import { MapBrowserEvent, View } from 'ol';
import Map from 'ol/Map';
import TileLayer from 'ol/layer/Tile';
import { OSM } from 'ol/source';
import VectorSource from 'ol/source/Vector';
import VectorLayer from 'ol/layer/Vector';
import { computed, onMounted, ref, Ref } from 'vue';
import { Coordinate } from 'ol/coordinate';
import { fromLonLat, toLonLat, transformExtent } from 'ol/proj';
import { UrlParameters } from '@/types/EatingOutBrowseTypes';
import useScreensize from '@/composables/useScreensize';
import eventBus from '@/eventBus';
import { clusterStyle } from '@/support/eating-out/browse/styles';
import { Extent } from 'ol/extent';
import { getDistance } from 'ol/sphere';
import { LatLng } from '@/types/EateryTypes';
import { AnimationOptions } from 'ol/View';

export default (
  processedUrl: Ref<UrlParameters>,
  rawMarkerSource: Ref<VectorSource>,
) => {
  const { currentBreakpoint } = useScreensize();

  const map: Ref<Map> = ref() as Ref<Map>;
  const view: Ref<View> = ref() as Ref<View>;
  const markerLayer = ref<VectorLayer<VectorSource>>();

  const initialLatLng = computed((): Coordinate => {
    let latLng: [number, number] = [54.093409, -2.89479];

    if (processedUrl.value.latLng) {
      latLng = processedUrl.value.latLng
        .split(',')
        .map((str) => parseFloat(str)) as [number, number];
    }

    return fromLonLat(latLng.reverse());
  });

  const initialZoom = computed((): number => {
    if (processedUrl.value.zoom) {
      return parseFloat(processedUrl.value.zoom);
    }

    switch (currentBreakpoint()) {
      case 'sm':
      case 'xmd':
      case 'md':
      case 'lg':
      case 'xl':
      case '2xl':
        return 6;
      case 'xs':
      case 'xxs':
      default:
        return 5;
    }
  });

  const createMap = () => {
    view.value = new View({
      center: initialLatLng.value,
      zoom: initialZoom.value,
      enableRotation: false,
    });

    map.value = new Map({
      layers: [
        new TileLayer({
          source: new OSM(),
        }),
      ],
      target: 'map',
      view: view.value,
    });

    map.value.on('moveend', handleMapMove);

    map.value.on('click', handleMapClick);

    map.value.on('pointermove', (event: MapBrowserEvent<MouseEvent>) => {
      if (event.dragging) {
        return;
      }

      map.value.getTargetElement().style.cursor = map.value.hasFeatureAtPixel(
        map.value.getEventPixel(event.originalEvent),
      )
        ? 'pointer'
        : '';
    });

    markerLayer.value = new VectorLayer({
      source: rawMarkerSource.value,
      style: clusterStyle,
      properties: {
        name: 'markers',
      },
    });

    map.value.addLayer(markerLayer.value);
  };

  const handleMapClick = (event: MapBrowserEvent<MouseEvent>) => {
    try {
      void markerLayer.value?.getFeatures(event.pixel).then((feature) => {
        if (!feature.length) {
          return;
        }

        if (feature[0].get('cluster') === true) {
          eventBus.$emit('cluster-clicked', {
            pixel: event.pixel,
            markerLayer: markerLayer.value,
          });

          return;
        }

        eventBus.$emit('clicked-feature', feature[0]);
      });
    } catch (e) {
      console.error(e);
    }
  };

  const handleMapMove = () => {
    eventBus.$emit('map-moved');
  };

  const getZoom = (): number => map.value.getView().getZoom() as number;

  const getExtent = (): Extent => map.value.getView().calculateExtent();

  const getViewableRadius = (): number => {
    const latLng = transformExtent(
      map.value.getView().calculateExtent(map.value.getSize()),
      'EPSG:3857',
      'EPSG:4326',
    );

    return getDistance([latLng[0], latLng[1]], [latLng[2], latLng[3]]);
  };

  const getLatLng = (): LatLng => {
    const latLng = toLonLat(
      map.value.getView().getCenter() as Coordinate,
    ).reverse();

    return {
      lat: latLng[0],
      lng: latLng[1],
    };
  };

  const navigateTo = (latLng: LatLng): void => {
    const coordinates = fromLonLat([latLng.lng, latLng.lat]);

    if (
      getLatLng().lat.toFixed(5) === latLng.lat.toFixed(5) &&
      getLatLng().lng.toFixed(5) === latLng.lng.toFixed(5)
    ) {
      eventBus.$emit('map-loading', false);

      return;
    }

    view.value.animate({
      center: coordinates,
      duration: 1000,
      zoom: 13,
    });
  };

  onMounted(() => {
    eventBus.$on('map-animate-to', (params) => {
      map.value.getView().animate(params as AnimationOptions);
    });
  });

  return {
    createMap,
    getZoom,
    getExtent,
    getViewableRadius,
    navigateTo,
    getLatLng,
  };
};
