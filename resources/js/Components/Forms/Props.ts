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

export const BaseFormInputPropDefaults: Partial<BaseFormInputProps> = {
  id: undefined,
  required: false,
  borders: false,
  background: true,
  hideErrorBackground: false,
  hasError: false,
  disabled: false,
};

export type InputProps = BaseFormInputProps & {
  type?: 'text' | 'number' | 'search' | 'email' | 'url' | 'phone';
  label: string;
  helpText?: string;
  error?: string;
  hideLabel?: boolean;
  size?: 'default' | 'large';
  min?: number;
  max?: number;
  wrapperClasses?: string;
  inputClasses?: string;
  errorClasses?: string;
};

export const InputPropDefaults: Partial<InputProps> = {
  ...BaseFormInputPropDefaults,
  type: 'text',
  helpText: undefined,
  hideLabel: false,
  size: 'default',
  min: undefined,
  max: undefined,
  wrapperClasses: '',
  inputClasses: '',
  errorClasses: '',
};

export type TextareaProps = BaseFormInputProps & {
  label: string;
  rows?: number;
  error?: string;
  max?: number;
  hideLabel?: boolean;
  size?: 'default' | 'large';
  helpText?: string;
};

export const TextareaPropsDefaults: Partial<TextareaProps> = {
  ...BaseFormInputPropDefaults,
  rows: 5,
  max: undefined,
  hideLabel: false,
  size: 'default',
  helpText: undefined,
};

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
};

export const FormSelectPropsDefaults: Partial<FormSelectProps> = {
  ...BaseFormInputPropDefaults,
  label: undefined,
  placeholder: 'Select an option',
  hideLabel: false,
  error: undefined,
  size: 'default',
  inputClasses: '',
};

export type FormMultiSelectProps = FormSelectProps & {
  options: FormMultiSelectOption[];
  allowOther: boolean;
};

export const FormMultiSelectPropsDefaults: Partial<FormMultiSelectProps> = {
  ...(FormSelectPropsDefaults as Partial<FormMultiSelectProps>),
  allowOther: false,
};

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

export const FormStepperPropsDefaults: Partial<FormStepperProps> = {
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
};

export type FormLookupProps = Omit<InputProps, 'type'> & {
  lookupEndpoint: string;
  postParameter?: string;
  resultKey?: string;
  preselectTerm?: string;
  lock?: boolean;
  allowAny?: boolean;
  fallbackObject?: object;
  fallbackKey?: string;
  resultsClasses?: string;
};

export const FormLookupPropDefaults: Partial<
  FormLookupProps & { type?: string }
> = {
  ...InputPropDefaults,
  type: undefined,
  postParameter: 'term',
  resultKey: 'data',
  preselectTerm: undefined,
  lock: false,
  allowAny: false,
  fallbackObject: {},
  fallbackKey: undefined,
  resultsClasses: '',
};

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

const tempProductQuantitySwitcherPropDefaults: Partial<ProductQuantitySwitcherProps> =
  { ...InputPropDefaults };

const keysToDelete = [
  'type',
  'id',
  'autocomplete',
  'placeholder',
  'borders',
  'background',
  'helpText',
  'hideLabel',
  'size',
];

keysToDelete.forEach((key) => {
  delete tempProductQuantitySwitcherPropDefaults[key];
});

export const ProductQuantitySwitcherPropDefaults: Partial<ProductQuantitySwitcherProps> =
  tempProductQuantitySwitcherPropDefaults;
