# Limpeza_Preencheimento_Planilha

## O que a aplicação faz:

O objetivo desse projeto é ler um arquivo CSV, e popular o BD com os dados desse arquivo para que sejam manipulados via código. 

Após isso, será realizada uma limpeza de dados duplicados de todos os valores de 2021 que são repetidos em 2022 e apaga-los, mantendo os dados não duplicados de 2021. Depois disso, o código deve apagar todos os dados de 2021 para baixo com exceção dos não repetidos.

Depois de ter a planilha "limpa" será feito um preenchimento da tabela principal com uma segunda tabela com dados que já estão na tabela principal, mas precisam ser complementados, por isso, a segunda tabela será responsável pela complementação de dados com uma validação de nomes repetidos, para tratamento manual posterior. 

Para finalizar, as datas comemorativas que começarem com "Dia", em geral são feriados nacionais, por isso, o campo cidade e estado deve ser preenchido com "null", então será feito um filtro para preencher esse campo de forma automática.

## Tecnologias Utilizadas - PHP e MySQL

Eu optei por trabalhar com o BD ao invés de trabalhar direto no CSV pela facilidade de manipulação e maior afinidade com o SQL, embora seja meu primeiro projeto, já tinha estudado sobre o tema e estava à procura de uma oportunidade para praticar.

Também optei por usar o PHP pelo mesmo motivo de já ter alguma familiaridade com a linguagem, o que me facilitou o desenvolvimento, uma vez que eu tinha prazo para entregar o projeto.


## Motivação do projeto

Eu precisava preencher uma planilha com mais de 22 mil linhas manualmente, planilha essa que tinha muitos dados duplicados e antigos que não teriam mais utilidade. Então, preencher 100% dos dados seria perda de tempo. Então antes de preencher os dados, seria necessário fazer uma limpeza, que tinha a condição de deletar os dados antigos, mantendo os dados de 2021 que fossem diferentes dos dados de 2022, os demais dados mais antigos poderiam ser deletados sem a validação de serem repetidos ou não.

Eu decidi preencher essa planilha de forma inteligente, usando um script para cumprir essa tarefa de maneira rápida e assertiva. Se eu preenchesse 1 linha a cada 15 segundos (o que é pouco, já que teoricamente, teria que validar no google alguns dados), eu demoraria cerca de 91,6 horas para preencher totalmente a planilha, claro que limpando os dados duplicados teriam bem menos linhas, mas, ainda sim teria que validar cerca de 80% dos dados manualmente antes de validar, pois os dados abaixo de 2021 representam apenas 20% dos dados.  

## Como o projeto resolveu meu problema

Com esse script eu fui capaz de limpar e preencher a planilha com muito mais eficiência e efetividade do que seria capaz de fazer manualmente, além disso, levei consideravelmente menos tempo para construir essa solução do que levaria para preencher os dados.

## O que aprendi

Esse foi meu primeiro contato com PHP e MySQL, então consegui praticar conceitos básicos de buscar e manipulação de dados de BD, além de criar uma lógica simples com laços de repetição para leitura e tomada de decisão de dados. Além disso, tive um contato inicial expressão regular simples, e função de mapeamento (map), que me ajudou a resolver o problema de forma mais simples. 

