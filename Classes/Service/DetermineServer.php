<?php
namespace Ribase\RibaseConsole\Service;


use Symfony\Component\Yaml\Yaml;

class DetermineServer
{

    /**
     * @var string
     */
    protected $filename = PATH_site.'../configs/console/aliases.yml';


    /**
     * @param $alias
     * @return string
     */
    public function getServer($alias) {
        $contents = Yaml::parse(file_get_contents($this->filename));
        $syncString = false;
        foreach ($contents as $key => $value ){
            foreach ($value as $key2 => $value2){
                if($value2 == $alias) {
                    if($value["type"] == 'foreign') {
                        $syncString = $value["user"].'@'.$value["server"].':'.$value["pathInternal"];
                    }else {
                        $syncString = PATH_site;
                    }
                }
            }
        }
        $syncString = str_replace("web/","",$syncString);
        return $syncString;
    }
    /**
     * @param $alias
     * @return string
     */
    public function getServerForCommand($alias) {
        $contents = Yaml::parse(file_get_contents($this->filename));
        $syncString = false;
        foreach ($contents as $key => $value ){
            foreach ($value as $key2 => $value2){
                if($value2 == $alias) {
                    if($value["type"] == 'foreign') {
                        $syncString = $value["user"].'@'.$value["server"];
                    }else {
                        $syncString = "";
                    }
                }
            }
        }
        $syncString = str_replace("web/","",$syncString);
        return $syncString;
    }

    /**
     * @param $alias
     * @return string
     */
    public function getPathForCommand($alias) {
        $contents = Yaml::parse(file_get_contents($this->filename));
        $syncString = false;
        foreach ($contents as $key => $value ){
            foreach ($value as $key2 => $value2){
                if($value2 == $alias) {
                    if($value["type"] == 'foreign') {
                        $syncString = $value["pathInternal"];
                    }else {
                        $syncString = PATH_site;
                    }
                }
            }
        }
        return $syncString;
    }


    /**
     * @param $alias
     * @return string
     */
    public function getServerRsync($alias) {
        $contents = Yaml::parse(file_get_contents($this->filename));
        $rsyncString = false;
        foreach ($contents as $key => $value ){
            foreach ($value as $key2 => $value2){
                if($value2 == $alias) {
                    if($value["type"] == 'foreign') {
                        $rsyncString = $value["user"].'@'.$value["server"].':'.$value["pathInternal"];
                    }else {
                        $rsyncString = $value["pathInternal"];
                    }
                }
            }
        }

        return $rsyncString;
    }
}