<?php

/* 
 * Copyright (C) 2014 Everton
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Biblioteca para internacionalização de aplicações com PHP
 * 
 * 
 */
class i81n{
    /**
     * O idioma a ser utilizado
     * @var string
     */
    protected $lang = NULL;
    
    /**
     * O diretório que contém os arquivos INI de tradução. O padrão é ./locale
     * @var string
     */
    protected $localeDir = './locale';
    
    /**
     * Armazena a tabela de tradução.
     * 
     * @var array
     */
    protected $translateTable = array();
    
    /**
     * Especifica o domínio a ser utilizado. Cada arquivo INI é um domínio. Para todos os domínios, utilizar * (padrão).
     * @var string
     */
    protected $domain = '*';

    /**
     * Construtor da classe.
     * 
     * @param string $lang Uma string representando o idioma a ser utilizado.
     * 
     * @throws Exception
     */
    public function __construct($lang = NULL, $domain = '*') {
        if($lang == NULL){
            throw new Exception('Undefined language!');
        }
        $this->setLang($lang);
        
        $this->setDomain($domain);
        
        $this->parseLang();
    }

    /**
     * Define o domínio a ser utilizado para a tradução.
     * 
     * Cada domínio corresponde a um arquivo INI com as traduções respectivas. É útil para criar traduções para módulos separados.
     * 
     * @param string $domain O domínio a ser utilizado para tradução.
     * @return boolean
     * @throws Exception
     */
    public function setDomain($domain){
        switch ($domain){
            case '*'://todos os domínios
                $this->domain = $domain;
                break;
            default:
                if(!file_exists($this->getLocaleDir().'/'.$this->getLang().'/'.$domain.'.ini')){
                    throw new Exception("The domain $domain was not found.");
                }else{
                    $this->domain = $domain;
                }
                break;
        }
        return true;
    }
    
    /**
     * Retorna o domínio atualmente configurado.
     * @return string
     */
    public function getDomain(){
        return $this->domain;
    }

    /**
     * Retorna a linguagem definida na variável de ambiente LANG
     * 
     * @return string
     */
    public function getLang(){
        return $this->lang;
    }
    
    /**
     * Configura a variável de ambiente LANG.
     * 
     * @param string $lang Um código de linguagem, tal como en_US, pt_BR, ...
     * @return string Retorna o conteúdo de LANG após a alteração.
     */
    public function setLang($lang){
        $this->lang = $lang;
        return $this->getLang();
    }
    
    /**
     * Retorna o caminho para os arquivos de tradução.
     * @return string
     */
    public function getLocaleDir(){
        return realpath($this->localeDir);
    }
    
    /**
     * Configura o caminho para os arquivos de tradução.
     * 
     * @param string $dirname O diretório onde estão os arquivos de tradução.
     * @return string Retorna o diretório configurado.
     * @throws Exception
     */
    public function setLocaleDir($dirname){
        //testa se é diretório e se existe
        if(!is_dir($dirname)){
            throw new Exception("$dirname is not a directory.");
        }elseif(!file_exists($dirname)){
            throw new Exception("The $dirname directory does not exist.");
        }
        
        //define o local dos arquivos de tradução
        $this->localeDir = realpath($dirname);//pega o caminho absoluto
        
        return $this->getLocaleDir();
    }
    
    /**
     * Monta a tabela de tradução a partir do domínio especificado.
     * 
     * @return bool
     * @throws Exception
     */
    protected function parseLang(){
        if($this->getDomain() === '*'){
            try{
                $dir = glob($this->getLocaleDir().'/'.$this->getLang()."/*.ini");
            } catch (Exception $ex) {
                throw $ex;
            }
        }else{
            $dir = array($this->getLocaleDir().'/'.$this->getLang().'/'.$this->getDomain().'.ini');
        }
        $table = array();
        foreach($dir as $path){
            if(is_file($path)){
                try{
                    $ini = parse_ini_file($path, true);
                    $table = array_merge($table, $ini);
                } catch (Exception $ex) {
                    throw $ex;
                }
            }
        }
        $this->translateTable = $table;
              
        return true;
    }
    
    /**
     * Cria um hash para uma mensagem.
     * 
     * Utilizado para referenciar as mensagens no arquivo INI de tradução.
     * 
     * 
     * @param string $msg A mensagem para calcular o hash
     * @return string Retorna o hash para a mensagem
     */
    protected static function hash($msg){
        return sha1($msg);
    }
    
    /**
     * Procura uma tradução através do hash da mesnagem original.
     * 
     * @param string $hash O hash da mensagem a ser procurada na tabela de tradução.
     * @return string Retorna a mensagem traduzida ou uma string vazia.
     */
    protected function searchInTranslateTable($hash){
        
        if(count($this->translateTable) == 0){
            return '';
        }else{
            if(array_key_exists($hash, $this->translateTable)){
                return $this->translateTable[$hash]['tmsg'];
            }else{
                return '';
            }
        }
    }

    /**
     * Realiza a tradução de mensagens.
     * 
     * @param string $string A mensagem a ser traduzida.
     * @return string A mensagem traduzida
     */
    public function translate($msg){
        if(count($this->translateTable) == 0){
            return $msg;
        }else{
            $hash = self::hash($msg);
            $translated = $this->searchInTranslateTable($hash);
            if(strlen($translated) > 0){
                return $translated;
            }else{
                return $msg;
            }
        }
    }
    
    //aqui começas os métodos para criação automática dos arquivos INI
    
    /**
     * Identifica em um arquivo fonte por mensagens usados com a biblioteca i81n (método translate) e cria um arquivo INI de tradução.
     * 
     * @param string $file Arquivo fonte para o parser
     * @param string $pattern O padrão de busca das mensagens a traduzir
     * @return array Retorna um array com as linhas do arquivo INI de tradução
     * @throws Exception
     */
    protected static function parseFileSource($file, $pattern = '->translate'){
        //printf("<p>Initiating processing %s</p>", $file);
        //testa o arquivo fonte
        if(!file_exists($file)){
            throw new Exception("File $file not found.");
        }
        
        //lê o conteúdo do arquivo-fonte
        //printf("<p>Loading the contents of %s</p>", $file);
        try {
            $source = file($file);
        } catch (Exception $ex) {
            throw $ex;
        }
        
        $msg = array();
        foreach($source as $line){
            $explode = explode($pattern, $line);
            if(count($explode) > 0){
                foreach ($explode as $row){
                    preg_match_all("/\(\'(.*)\'\)/", $row, $matches);
                    if(count($matches) > 0){
                        foreach ($matches[1] as $m){
                            $msg[] = $m;
                        }
                    }
                }
            }
        }
        
        $inistr = array();
        foreach ($msg as $str){
            $hash = self::hash($str);
            $inistr[] = "[$hash]".PHP_EOL;
            $inistr[] = "omsg = '$str'".PHP_EOL;
            $inistr[] = "tmsg = '$str'".PHP_EOL;
        }
        
        return $inistr;
    }
    
    public static function parseDir($configfile){
        printf("Starting the scan".PHP_EOL);
        //Carrega o arquivo de configurações
        try {
            $conf = parse_ini_file($configfile, true);
        } catch (Exception $ex) {
            throw $ex;
        }
        
        $input = realpath($conf['general']['input']);
        $output = realpath($conf['general']['output']);
        $domain = $conf['general']['domain'];
        $pattern = $conf['general']['pattern'];
        $recursive = $conf['general']['recursive'];
        $exclude = $conf['exclude'];
        printf("%s loaded".PHP_EOL, $configfile);
        
        if(!file_exists($input)){
            throw new Exception("Source directory does not exist!");
        }
        
        if(!file_exists($output)){
            throw new Exception("Destination directory does not exist!");
        }
        
        $sources = self::getFileSources($input, $recursive, $exclude);
        printf("Sources found".PHP_EOL);
        echo '<pre>';
        print_r($sources);
        echo '</pre>';
        
        foreach($sources as $filename){
            printf("Processing file %s with pattern %s ".PHP_EOL, $filename, $pattern);
            $parsed = self::parseFileSource($filename, $pattern);
            
            switch ($domain) {
                case 'FILE_DOMAIN':
                    $d = basename($filename, '.php');
                    $mode = 'w';
                    break;

                case 'UNIQUE_DOMAIN':
                    $d = 'main';
                    $mode = 'a';
                    break;

                case 'DIR_DOMAIN':
                    $d = str_replace(dirname($input), '', dirname($filename));
                    $d = str_replace('/', '.', $d);
                    $d = str_replace('\\', '.', $d);
                    if($d[0] == '.');
                    $d = substr($d, 1);
                    $mode = 'w';
                    break;

                default:
                    throw new Exception("The choice for a domain name is not valid!");
                    break;
            }
            printf("Selected domain %s based on option %s".PHP_EOL, $d, $domain);
            $o = "$output/$d.ini";
            try{
                $handle = fopen($o, $mode);
                foreach ($parsed as $str){
                    fwrite($handle, "$str".PHP_EOL);
                }
                fclose($handle);
            } catch (Exception $ex) {
                throw $ex;
            }
            printf("Save translation in %s".PHP_EOL, $o);
        }
        
        printf("Process completed".PHP_EOL);
        
        return true;
        
        
    }
    
    protected static function getFileSources($sourcedir, $recursive, $exclude){
        printf("Starting search for files in %s".PHP_EOL, $sourcedir);
        reset($exclude);
        $sources = array();
        
        $glob = glob("$sourcedir/*");
        
        foreach($glob as $f){
            printf("Analyzing the file %s".PHP_EOL, $f);
            $ex = false;
            foreach($exclude as $pattern){
                if(preg_match($pattern, $f) === 1){
                    $ex = true;
                    printf("Deleted file %s by pattern %s".PHP_EOL, $f, $pattern);
                }
            }
            if($ex == false){
                if(is_dir($f)){
                    if($recursive == 1){
                        printf("%s is a directory. Recursion starting".PHP_EOL, $f);
                        $child = self::getFileSources($f, $recursive, $exclude);
                        $sources = array_merge($sources, $child);
                        printf("Recursion of %s terminated".PHP_EOL, $f);
                    }else{
                        printf("%s is a directory. Recursion not initialised".PHP_EOL, $f);
                    }
                }else{
                    $sources[] = $f;
                    printf("Adding %s the list".PHP_EOL, $f);
                }
            }
        }
        
        return $sources;
    }
}