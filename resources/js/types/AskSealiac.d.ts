export type Role = 'user' | 'assistant';

export type Message = {
  role: Role;
  message: string;
};
