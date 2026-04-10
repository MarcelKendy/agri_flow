// Accepts masked or unmasked CPF
function isValidCPF(value) {
  if (!value) return false
  const cpf = value.replace(/\D/g, '')

  if (cpf.length !== 11) return false
  if (/^(\d)\1{10}$/.test(cpf)) return false

  let sum = 0, rest

  // first digit
  for (let i = 1; i <= 9; i++)
    sum += parseInt(cpf.substring(i - 1, i)) * (11 - i)

  rest = (sum * 10) % 11
  rest = (rest === 10 || rest === 11) ? 0 : rest

  if (rest !== parseInt(cpf.substring(9, 10))) return false

  // second digit
  sum = 0
  for (let i = 1; i <= 10; i++)
    sum += parseInt(cpf.substring(i - 1, i)) * (12 - i)

  rest = (sum * 10) % 11
  rest = (rest === 10 || rest === 11) ? 0 : rest

  return rest === parseInt(cpf.substring(10, 11))
}

// Accepts masked or unmasked CNPJ
function isValidCNPJ(value) {
  if (!value) return false
  const cnpj = value.replace(/\D/g, '')

  if (cnpj.length !== 14) return false
  if (/^(\d)\1{13}$/.test(cnpj)) return false

  let length = 12
  let numbers = cnpj.substring(0, length)
  let sum = 0
  let pos = length - 7

  for (let i = length; i >= 1; i--) {
    sum += numbers.charAt(length - i) * pos--
    if (pos < 2) pos = 9
  }

  let result = sum % 11 < 2 ? 0 : 11 - (sum % 11)
  if (result != cnpj.charAt(12)) return false

  length = 13
  numbers = cnpj.substring(0, length)
  sum = 0
  pos = length - 7

  for (let i = length; i >= 1; i--) {
    sum += numbers.charAt(length - i) * pos--
    if (pos < 2) pos = 9
  }

  result = sum % 11 < 2 ? 0 : 11 - (sum % 11)
  return result == cnpj.charAt(13)
}

export function getRules(rules) {
  const validation_rules = Object.entries(rules).map(([key, v]) => {
    if (key === 'required') {
      return v
        ? value => (value === 0 ? true : !!value) || (v.message ? v.message : 'Esse campo é obrigatório')
        : null
    } else if (key === 'cpf') {
      return value => isValidCPF(value) || (v.message ? v.message : 'CPF inválido (000.000.000-00)')    
    } else if (key === 'cpf_cnpj') {
      return value => (isValidCPF(value) || isValidCNPJ(value)) || (v.message ? v.message : 'CPF/CNPJ inválido (000.000.000-00) ou (00.000.000/0000-00)')
    } else if (key === 'minlen' && v.val > 0) {
      return value => ((!value) || value.length >= v.val) ||
        (v.message ? v.message : `No mínimo ${v.val} caracteres`)
    } else if (key === 'maxlen' && v.val > 0) {
      return value => (value && value.length <= v.val) ||
        (v.message ? v.message : `No máximo ${v.val} caracteres`)
    } else if (key === 'hasLowercase') {
      return value => (value && /[a-z]/.test(value)) ||
        (v.message ? v.message : 'Letra minúscula obrigatória')
    } else if (key === 'hasUppercase') {
      return value => (value && /[A-Z]/.test(value)) ||
        (v.message ? v.message : 'Letra maiúscula obrigatória')
    } else if (key === 'hasNumber') {
      return value => (value && /\d/.test(value)) ||
        (v.message ? v.message : 'Número obrigatório')
    } else if (key === 'number') {
      return value => ((v.val ? value >= v.val : true) && (v.val ? /^(?!0)[0-9]+$/ : /^[0-9]+$/).test(value)) ||
        (v.message ? v.message : (v.val ? ('Apenas números (valor mínimo: ' + v.val + ')') : ('Apenas números')))
    } else if (key === 'money') {
      return (value) => {
        if (v.mask) {
          if (parseInt(value) > 0) {
            return true
          } else {
            return v.message ? v.message : 'Valor inválido'
          }
        }
        if (/^([1-9]\d*|0)(\.\d{3})*,\d{2}$/.test(value) && (v.zero ? true : (value != '0,00'))) {
          return true
        } else {
          return v.message ? v.message : 'Valor inválido'
        }
      }
    } else if (key === 'float') {
      const regex = new RegExp(`^[1-9]\\d*,\\d{${v.val > 0 ? v.val : 2}}$`)
      return value => (value && regex.test(value)) ||
        (v.message ? v.message : (`Apenas números (${v.val > 0 ? v.val : 2} casas decimais)`))
    } else if (key === 'hasSpecialchar') {
      return value => (value && /[^a-zA-Z0-9]/.test(value)) ||
        (v.message ? v.message : 'Caractere especial obrigatório')
    } else if (key === 'email') {
      const regex = v.sicoob ? /^[a-z]+\.[a-z]+$/ : /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      return value => regex.test(value) ||
        (v.message ? v.message : 'Email inválido')
    } else if (key === 'integer_range') {
      return value => (value && /^[0-9]+$/.test(value) && value >= v.val[0] && value <= v.val[1]) ||
        (!v.silent ? (v.message ? v.message : 'Valor inválido') : '')
    } else if (key === 'date_period') {
      return value => ((!v.val[0] || !v.val[1]) || (v.val[0] <= v.val[1])) ||
        (v.message ? v.message : 'O fim deve ser posterior ao começo')
    } else if (key === 'equals') {
      return value => value == v.val ||
        (v.message ? v.message : 'Valor inválido')
    } else if (key === 'date') {
      return value => (!value || /^\d{4}-\d{2}-\d{2}$/.test(value)) ||
        (v.message ? v.message : 'Formato de data inválido')
    } else if (key === 'file_format') {      
      return value => (!value || !value['type'] || value.type.includes(v.val) ||
        (v.message ? v.message : 'Arquivo de formato inválido'))
    } else if (key === 'name') {
      return value => /^[A-ZÁÉÍÓÚÃÕÂÊÎÔÛÀÇ][a-záéíóúãõâêîôûàç\s]+(?: [A-ZÁÉÍÓÚÃÕÂÊÎÔÛÀÇ][a-záéíóúãõâêîôûàç\s]+)+$/.test(value) ||
        (v.message ? v.message : 'Nome completo com iniciais maiúsculas')
    } else {
      return true
    }
  })

  return validation_rules.filter(rule => rule !== null)
}
