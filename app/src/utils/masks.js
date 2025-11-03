/* eslint-disable no-param-reassign */
function maskDate(value) {
  value = value.replace(/\D/g, '');
  value = value.replace(/(\d{2})(\d)/, '$1/$2');
  value = value.replace(/(\d{2})(\d)/, '$1/$2');

  return value;
}

function maskHour(value) {
  value = value.replace(/\D/g, '');
  value = value.replace(/(\d{2})(\d)/, '$1:$2');
  value = value.replace(/(\d{2})(\d)/, '$1:$2');
  value = value.replace(/(\d{2})(\d)/, '$1:$2');

  return value;
}

export { maskDate, maskHour };
