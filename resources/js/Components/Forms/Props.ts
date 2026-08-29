import { CheckCircleIcon as CheckCircleIconOutline } from '@heroicons/vue/24/outline';
import { CheckCircleIcon as CheckCircleIconSolid } from '@heroicons/vue/24/solid';
import { FunctionalComponent } from 'vue';

export type BaseFormProps = {
  name: string;
  id?: string;
  required?: boolean;
  autocomplete?: string;
  placeholder?: string;
  borders?: boolean;
  background?: boolean;
  hideErrorBackground?: boolean;
  hasError?: boolean;
  disabled?: boolean;
};

export type BaseFormInputProps = BaseFormProps;

export const BaseFormInputPropDefaults = {
  id: undefined,
  required: false,
  borders: false,
  background: true,
  hideErrorBackground: false,
  hasError: false,
  disabled: false,
} satisfies Partial<BaseFormInputProps>;

export type InputProps = BaseFormInputProps & {
  type?: 'text' | 'number' | 'search' | 'email' | 'url' | 'phone';
  label: string;
  helpText?: string;
  error?: string;
  hideLabel?: boolean;
  size?: 'sm' | 'default' | 'large';
  min?: number;
  max?: number;
  wrapperClasses?: string;
  inputClasses?: string;
  errorClasses?: string;
};

const InputPropDefaultsWithoutType = {
  ...BaseFormInputPropDefaults,
  helpText: undefined,
  hideLabel: false,
  size: 'default',
  min: undefined,
  max: undefined,
  wrapperClasses: '',
  inputClasses: '',
  errorClasses: '',
} satisfies Partial<Omit<InputProps, 'type'>>;

export const InputPropDefaults = {
  ...InputPropDefaultsWithoutType,
  type: 'text',
} satisfies Partial<InputProps>;

export type TextareaProps = BaseFormInputProps & {
  label: string;
  rows?: number;
  error?: string;
  max?: number;
  hideLabel?: boolean;
  size?: 'default' | 'large';
  helpText?: string;
  resizable?: boolean;
  shadow?: boolean;
};

export const TextareaPropsDefaults = {
  ...BaseFormInputPropDefaults,
  rows: 5,
  max: undefined,
  hideLabel: false,
  size: 'default',
  helpText: undefined,
  resizable: true,
  shadow: true,
} satisfies Partial<TextareaProps>;

export type CheckboxProps = BaseFormProps & {
  label: string;
  hideLabel?: boolean;
  layout?: 'left' | 'right';
  xl?: boolean;
  highlight?: boolean;
};

export const CheckboxPropsDefault: Partial<CheckboxProps> = <
  Partial<CheckboxProps>
>{
  ...BaseFormInputPropDefaults,
  hideLabel: false,
  layout: 'right',
  xl: false,
  highlight: false,
};

export type FormSelectOption = {
  label?: string;
  value: string | number | boolean;
};

export type FormMultiSelectOption = {
  label?: string;
  value: string;
  isOther?: boolean;
};

export type FormSelectGroup = {
  label: string;
  options: FormSelectOption[];
};

export type FormSelectProps = BaseFormProps & {
  label?: string;
  options: FormSelectOption[] | FormSelectGroup[];
  placeholder?: string;
  hideLabel?: boolean;
  error?: string;
  size?: 'small' | 'default' | 'large';
  inputClasses?: string;
  wrapperClasses?: string;
};

export const FormSelectPropsDefaults = {
  ...BaseFormInputPropDefaults,
  label: undefined,
  placeholder: 'Select an option',
  hideLabel: false,
  error: undefined,
  size: 'default',
  inputClasses: '',
  wrapperClasses: '',
} satisfies Partial<FormSelectProps>;

export type FormMultiSelectProps = FormSelectProps & {
  options: FormMultiSelectOption[];
  allowOther: boolean;
};

export const FormMultiSelectPropsDefaults = {
  ...FormSelectPropsDefaults,
  allowOther: false,
} satisfies Partial<FormMultiSelectProps>;

export type FormStepperProps = BaseFormProps & {
  label?: string;
  options: FormSelectOption[];
  selectedClass?: string;
  baseClass?: string;
  iconClasses?: string;
  wrapperClasses?: string;
  icon?: FunctionalComponent;
  unselectedIcon?: FunctionalComponent | null;
  hideOptionsText?: boolean;
  defaultText?: string;
};

export const FormStepperPropsDefaults = {
  ...BaseFormInputPropDefaults,
  label: undefined,
  selectedClass: 'text-secondary',
  baseClass: 'text-grey-off',
  iconClasses: 'h-8 w-8',
  wrapperClasses: '',
  icon: CheckCircleIconSolid,
  unselectedIcon: CheckCircleIconOutline,
  hideOptionsText: false,
  defaultText: 'Select an option',
} satisfies Partial<FormStepperProps>;

export type FormLookupProps = Omit<InputProps, 'type'> & {
  lookupEndpoint: string;
  postParameter?: string;
  resultKey?: string;
  preselectTerm?: string;
  initialValue?: string;
  lock?: boolean;
  allowAny?: boolean;
  fallbackObject?: object;
  fallbackKey?: string;
  resultsClasses?: string;
};

export const FormLookupPropDefaults = {
  ...InputPropDefaultsWithoutType,
  postParameter: 'term',
  resultKey: 'data',
  preselectTerm: undefined,
  initialValue: undefined,
  lock: false,
  allowAny: false,
  fallbackObject: () => ({}),
  fallbackKey: undefined,
  resultsClasses: '',
} satisfies Partial<FormLookupProps>;

export type ProductQuantitySwitcherProps = Omit<
  InputProps,
  | 'type'
  | 'id'
  | 'autocomplete'
  | 'placeholder'
  | 'borders'
  | 'background'
  | 'helpText'
  | 'hideLabel'
  | 'size'
>;

export const ProductQuantitySwitcherPropDefaults = {
  required: false,
  hideErrorBackground: false,
  hasError: false,
  disabled: false,
  min: undefined,
  max: undefined,
  wrapperClasses: '',
  inputClasses: '',
  errorClasses: '',
} satisfies Partial<ProductQuantitySwitcherProps>;
