export const metersToMiles = (meters: number): string =>
  (meters / 1609.344).toFixed(1);

export const formatDuration = (seconds: number): string => {
  const totalMinutes = Math.round(seconds / 60);
  const hours = Math.floor(totalMinutes / 60);
  const minutes = totalMinutes % 60;

  if (hours > 0) {
    return `${hours}h ${minutes}m`;
  }

  return `${minutes} min${minutes === 1 ? '' : 's'}`;
};
