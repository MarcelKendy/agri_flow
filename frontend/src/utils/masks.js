import { format } from 'v-money3'

function moneyMask(object = 'item', key = 'value') {
  object[key] = format(object[key], {
    masked: true,
    prefix: '',
    suffix: '',
    thousands: '.',
    decimal: ',',
    precision: 2,
    focusOnRight: true,
  });
  return object[key]
}

function moneyMaskRaw(value) {
  return format(value, {
    masked: true,
    prefix: '',
    suffix: '',
    thousands: '.',
    decimal: ',',
    precision: 2,
    focusOnRight: true,
  });
}

function nameMask(object, key = 'name') {
  object[key] = object[key]
    .replace(/[^a-zA-ZÀ-Öà-öØ-öø-ÿ\s]/g, '')
    .replace(/\s+/g, ' ')
    .toLowerCase()
    .split(' ')
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ')
  return object[key]
}

function nameMaskRaw(value) {
  return value
    .replace(/[^a-zA-ZÀ-Öà-öØ-öø-ÿ\s]/g, '')
    .replace(/\s+/g, ' ')
    .toLowerCase()
    .split(' ')
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ')
}


function percentageMask(object, key) {
  let value = object[key]
  if (value === null || value === undefined) {
    object[key] = ''
    return
  }
  value = value.toString().replace(/[^\d]/g, '')
  if (!value) {
    object[key] = ''
    return
  }
  let number = parseFloat(value) / 100
  if (number > 100) number = 100
  if (number < 0) number = 0
  object[key] = number.toFixed(2)
  return object[key]
}

function dateMask(object, key = 'date') {
  let value = object[key]
  if (value === null || value === undefined) {
    object[key] = ''
    return
  }
  value = value.toString().replace(/[^\d]/g, '')
  if (!value) {
    object[key] = ''
    return
  }
  if (value.length > 2) {
    value = value.slice(0, 2) + '/' + value.slice(2)
  }
  if (value.length > 5) {
    value = value.slice(0, 5) + '/' + value.slice(5)
  }
  if (value.length > 10) {
    value = value.slice(0, 10)
  }
  object[key] = value
  return object[key]
}

export { moneyMask, moneyMaskRaw, nameMask, nameMaskRaw, percentageMask, dateMask }
