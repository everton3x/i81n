<!DOCTYPE html>
<!--
Copyright (C) 2014 Everton

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title>i81n :: Biblioteca para tradução</title>
    </head>
    <body>
        <h1>i81n :: Tradução sem gettext</h1>
        <p>i81n (cujo nome foi inspirado no termo i18n) é uma classe escrita em PHP para tradução de pa´ginas e sistemas em PHP sem a dependência de extensões como gettext, por exemplo.</p>
        <h2>Instalação</h2>
        <p>A instalação para uso apenas com a tradução (sem o parser) depende apenas da inclusão do arquivo i81n.php e da definição do diretório onde estarão as traduções (o padrão é "./locale").</p>
        <p>O diretório "locale" (ou outro que você deseje especificar) armazena os arquivos de tradução. Para cada idioma, deve haver um subdiretório de "locale" com a sigla do idioma (pt_BR, en_US, por exemplo). Dentro desses subdiretórios ficam os arquivos de tradução gerados pelo parser.</p>
        <p>Para alterar o diretório de "locale" para outro, na instância da classe i81n que for criada, utilize o método setLocaleDir().</p>
        <h2>Utilização</h2>
        <p>A utilização se baseia na criação de uma instância da classe i81n e na definição do idioma a ser utilizado através do método setLang().</p>
        <p>Utilize o método translate() para a tradução das mensagens (veja os arquivos de exemplos).</p>
        <p>Você também pode criar alias para o método i81n::translate(), seja extendendo a classe i81n ou criando uma função (você pode inclusive criar uma função gettext, por exemplo).</p>
        <h2>Criando as traduções para seus arquivos PHP</h2>
        <p>i81n tem um parser para extrair os textos a serem traduzidos (os quais chamamos de mensagens) direto das páginas de sua aplicação.</p>
        <p>Basicamente, o processo segue as seguintes etapas:</p>
        <ol>
            <li>Configuração do arquivo INI com os parâmetros do parser (veja parser_config_example.ini);</li>
            <li>Execução do arquivo parser.bat informando como argumento o caminho para o arquivo INI de configuração;</li>
            <li>parser.bat executa o arquivo parser.php, que na verdade serve apenas para executar o método estático i81n::parseDir(). Você pode criar outras formas de executar o parser proque todo o trabalho é feito pelo método i81n::parseDir().</li>
            <li>O método i81n::parseDir() lê os parâmetros do arquivo INI e busca nos arquivos da sua aplicação pelas mensagens a serem traduzidas, colocando-as todas em arquivos INI no subdiretório de "locale" relativo a linguagem da sua aplicação;</li>
            <li>Depois disso, é só criar cópias dos arquivos INI de tradução, uma para cada linguagem (em subdiretórios separados) e realizar a tradução das mensagens salvas neles.</li>
        </ol>
        <h2>Os arquivos de tradução</h2>
        <p>Os arquivos INI de tradução são separados em seções onde cada seção corresponde ao hash sha1 da mensagem original encontrada nos arquivos da sua aplicação. É por esse hash que o método i81n::translate() localiza a mensagem a utilizar.</p>
        <p>Em cada seção, existem dois parâmetros:</p>
        <ul>
            <li>omsg: a mensagem original, usada apenas como referência para a hora da tradução das mensagens nos arquivos INI.</li>
            <li>tmsg: a mensagem traduzida, que será retornada pelo método i81n::translate().</li>
        </ul>
        <h2>Dicas importantes</h2>
        <p>Utilize sempre mensagens estáticas, independentes de variáveis para que a tradução seja feita corretamente.</p>
        <p>Lembre-se que qualquer alteração nas mensagens, por menor que seja, demandará uma nova execução do parser e novas traduções manuais.</p>
        <p>Antes de executar o parser, faça backup dos arquivos INI de tradução.</p>
        <p>O exemplos estão nos arquivos example.php e no diretório subdir_test.</p>
    </body>
</html>
