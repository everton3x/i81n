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
 * Library for internationalizing applications with PHP
 * 
 * 
 */
class i81n{
    /**
     * The language to be used
     * @var string
     */
    protected $lang = NULL;
    
    /**
     * The directory containing the translation INI files. The default is ./locale
     * @var string
     */
    protected $localeDir = './locale';
    
    /**
     * Holds the translation table.
     * 
     * @var array
     */
    protected $translateTable = array();
    
    /**
     * Specifies the domain to be used. Each INI file is a domain. For all domains, use * (default).
     * @var string
     */
    protected $domain = '*';

    /**
     * Class constructor.
     * 
     * @param string $lang A string representing the language to be used.
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
     * Sets the domain to be used for translation.
     * 
     * Each domain corresponds to an INI file with the translations. It is useful to create translations for separate modules.
     * 
     * @param string $domain The domain to be used for translation.
     * @return boolean
     * @throws Exception
     */
    public function setDomain($domain){
        switch ($domain){
            case '*'://all domains
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
     * Returns the currently configured domain.
     * @return string
     */
    public function getDomain(){
        return $this->domain;
    }

    /**
     * Returns the language set in the LANG environment variable
     * 
     * @return string
     */
    public function getLang(){
        return $this->lang;
    }
    
    /**
     * Set the LANG environment variable.
     * 
     * @param string $lang A language code, such as en_US, en_GB, ...
     * @return string Returns the contents of LANG after the change.
     */
    public function setLang($lang){
        $this->lang = $lang;
        return $this->getLang();
    }
    
    /**
     * Returns the path for translation files.
     * @return string
     */
    public function getLocaleDir(){
        return realpath($this->localeDir);
    }
    
    /**
     * Sets the path for translation files.
     * 
     * @param string $dirname The directory where the files are translation.
     * @return string Returns the configured directory.
     * @throws Exception
     */
    public function setLocaleDir($dirname){
        //forehead is directory and if there
        if(!is_dir($dirname)){
            throw new Exception("$dirname is not a directory.");
        }elseif(!file_exists($dirname)){
            throw new Exception("The $dirname directory does not exist.");
        }
        
        //defines the location of the translation files
        $this->localeDir = realpath($dirname);//pega o caminho absoluto
        
        return $this->getLocaleDir();
    }
    
    /**
     * Mounts from the domain specified translation table.
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
     * Creates a hash for a message.
     * 
     * Used to reference the messages in INI file translation.
     * 
     * 
     * @param string $msg The message to compute the hash
     * @return string Returns the hash for the message
     */
    protected static function hash($msg){
        return sha1($msg);
    }
    
    /**
     * Search through a translation of the original hash mesnagem.
     * 
     * @param string $hash The hash of the message to be sought in the translation table.
     * @return string Returns the translated message or an empty string.
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
     * Performs the translation of messages.
     * 
     * @param string $string The message to be translated.
     * @return string The translated message
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
    
    //here start methods for automatic creation of INI files
    
    /**
     * Identifies a file source for messages used with i81n library (translate method) and creates an INI file translation.
     * 
     * @param string $file Source file for the parser
     * @param string $pattern The search pattern of the messages to translate
     * @return array Returns an array with the lines of the INI file translation
     * @throws Exception
     */
    protected static function parseFileSource($file, $pattern = '->translate'){
        //printf("<p>Initiating processing %s</p>", $file);
        //tests the source file
        if(!file_exists($file)){
            throw new Exception("File $file not found.");
        }
        
        //reads the contents of the source file
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
        //Load the settings file
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