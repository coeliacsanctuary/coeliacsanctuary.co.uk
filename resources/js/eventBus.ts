import mitt from 'mitt';

type EventHandler<T = unknown> = (arg: T) => void;

const emitter = mitt<Record<string, unknown>>();

export default {
  $on: <T = unknown>(event: string, handler: EventHandler<T>) =>
    emitter.on(event, handler as EventHandler),
  $emit: <T = unknown>(event: string, payload?: T) =>
    emitter.emit(event, payload),
};
