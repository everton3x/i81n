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
        <title>i81n :: Exemplo de uso</title>
    </head>
    <body>
        <h1>Exemplo de uso da biblioteca de internacionalização i81n</h1>
        <?php
        //$lang = 'pt_BR';//define o idioma a utilizar
        $lang = 'en_US';
        require 'i81n.php';//carrega a biblioteca i81n
        
        $i81n = new i81n($lang, '*');//cria uma instância de tradução para todos os domínios disponíveis
        
        printf("<p>%s.</p>", $i81n->translate('A biblioteca i81n possibilita a internacionalização de aplicações através do PHP sem a necessidade de gettext ou outra extenção do PHP'));
        
        printf("<p>%s %s %s %s</p>", $i81n->translate('O idioma atual é '), $i81n->getLang(), $i81n->translate('Os arquivos estão armazenados no seguinte diretório: '), $i81n->getLocaleDir());
        
        printf("<p>%s.</p>", $i81n->translate('i81n suporta domínios para que você possa dividir suas traduções em módulos.'));
        ?>
    </body>
</html>
