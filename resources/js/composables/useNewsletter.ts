import { useForm } from 'laravel-precognition-vue-inertia';

type Payload = { email: string };

export default () => {
  const subscribeForm = useForm<Payload>('post', '/newsletter', {
    email: '',
  });

  return { subscribeForm };
};
