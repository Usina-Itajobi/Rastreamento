function validateHour(value) {
  const test = /^(?:2[0-3]|[01]?[0-9]):[0-5][0-9]:[0-5][0-9]$/;

  return test.test(value);
}

function validateDate(value) {
  const test = /^([0-2][0-9]|(3)[0-1])(\/)(((0)[0-9])|((1)[0-2]))(\/)\d{4}$/;

  return test.test(value);
}

function validatePlaca(value) {
  const test = /^[a-zA-Z]{3}[0-9][A-Za-z0-9][0-9]{2}$/;

  return test.test(value);
}

/**
 * @see https://www.geradorcpf.com/javascript-validar-cpf.htm
 */
function validateCpf(value) {
  let cpf = value.replace(/[^\d]+/g, '');
  if (cpf == '') return false;

  // Elimina CPFs invalidos conhecidos
  if (
    cpf.length != 11 ||
    cpf == '00000000000' ||
    cpf == '11111111111' ||
    cpf == '22222222222' ||
    cpf == '33333333333' ||
    cpf == '44444444444' ||
    cpf == '55555555555' ||
    cpf == '66666666666' ||
    cpf == '77777777777' ||
    cpf == '88888888888' ||
    cpf == '99999999999'
  ) {
    return false;
  }

  // Valida 1º digito
  let add = 0;
  for (i = 0; i < 9; i++) {
    add += parseInt(cpf.charAt(i)) * (10 - i);
  }

  let rev = 11 - (add % 11);
  if (rev == 10 || rev == 11) {
    rev = 0;
  }
  if (rev != parseInt(cpf.charAt(9))) {
    return false;
  }

  // Valida 2º digito
  add = 0;
  for (i = 0; i < 10; i++) {
    add += parseInt(cpf.charAt(i)) * (11 - i);
  }

  rev = 11 - (add % 11);
  if (rev == 10 || rev == 11) {
    rev = 0;
  }
  if (rev != parseInt(cpf.charAt(10))) {
    return false;
  }

  return true;
}

function validateCelular(value) {
  const test = /^[1-9]{2}9[0-9]{8}$/;

  return test.test(value);
}

function validateEmail(value) {
  const test = /^[a-zA-Z]+[a-zA-Z0-9_.]+@[a-zA-Z.]+[a-zA-Z]$/;

  return test.test(value);
}

function validateEightCharactersPassword(value) {
  const test = /^(?=.{8,})/;

  return test.test(value);
}

function validateLettersPassword(value) {
  const test = /^(?=.*?[a-zA-Z])/;

  return test.test(value);
}

function validateNumbersPassword(value) {
  const test = /^(?=.*?[0-9])/;

  return test.test(value);
}

function validateSpecialCharactersPassword(value) {
  const test = /^(?=.*?[^\w\s])/;

  return test.test(value);
}

export {
  validateHour,
  validateDate,
  validatePlaca,
  validateCpf,
  validateCelular,
  validateEmail,
  validateEightCharactersPassword,
  validateLettersPassword,
  validateNumbersPassword,
  validateSpecialCharactersPassword,
};
