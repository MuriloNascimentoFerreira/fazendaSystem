<?php

namespace App\Enumeration;

class Situacao
{
    const VIVO = 'Vivo';
    const ABATIDO = 'Abatido';

    public static function getSituacao($situacao){
        switch($situacao){
            case 1: return Situacao::VIVO;
            case 2: return Situacao::ABATIDO;
            case 'vivo': return 1;
            case 'abatido': return 2;
        }
        return $situacao;
    }
}
?>