<?php namespace CovidGifts\App;

use CovidGifts\App\Contracts\CodeGenerator as CodeGeneratorInterface;

class CodeGenerator implements CodeGeneratorInterface {

    private $size = 12;

    public function make($attributes, $formatted = false)
    {
        $chars = "23456789ABCDEFGHJKLMNPQRSTUVWXYZ";
        $res = "";
        for ($i = 0; $i < $this->size; $i++) {
            $res .= $chars[mt_rand(0, strlen($chars)-1)];
        }
        return $formatted ? $this->format($res) : $res;;
    }

    public function format($str)
    {
        return substr($str,0,4).'-'.substr($str,4,4).substr($str,8,4);   
    }

    public function raw($str)
    {
        return str_replace('-', '', $str);
    }

}