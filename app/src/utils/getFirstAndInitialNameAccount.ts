export default function getFirstAndInitialNameAccount(nome: string) {
  let nomeInitials = '';

  if (nome) {
    const nomeSplit = nome.split(' ');
    nomeInitials = nomeSplit
      .slice(0, 2)
      .map((nome) => nome.charAt(0).toUpperCase())
      .join('');
  }

  let firstNome = '';
  if (nome) {
    const nomeSplit = nome.split(' ');
    firstNome = nomeSplit[0];
  }

  return { nomeInitials, nome: firstNome };
}
