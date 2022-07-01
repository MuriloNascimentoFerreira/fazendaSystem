<?php

namespace App\Enumeration;

class Situacao
{
    const VIVO = 'Vivo';
    const ABATIDO = 'Abatido';

    public function getSituacao($numero){
        switch($numero){
            case 1: return Situacao::VIVO;
            case 2: return Situacao::ABATIDO;
        }
        return $numero;
    }
}
?>