export const average = (
  table = undefined,
  column = undefined,
  localKey = undefined,
  foreignKey = undefined,
  alias = undefined,
  operator = undefined,
  value = undefined,
) => ({
  table,
  column,
  localKey,
  foreignKey,
  alias,
  operator,
  value,
});

export const count = (
  table = undefined,
  localKey = undefined,
  foreignKey = undefined,
  alias = undefined,
  operator = undefined,
  value = undefined,
) => ({
  table,
  localKey,
  foreignKey,
  alias,
  operator,
  value,
});

export const join = (
  table = undefined,
  first = undefined,
  operator = undefined,
  second = undefined,
) => ({
  table,
  first,
  operator,
  second,
});

export const order = (
  column = undefined,
  direction = undefined,
  table = undefined,
  localKey = undefined,
  foreignKey = undefined,
) => ({
  column,
  direction,
  table,
  localKey,
  foreignKey,
});

export const where = (
  field = undefined,
  operator = undefined,
  value = undefined,
) => ({
  field,
  operator,
  value,
});
