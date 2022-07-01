<?php

namespace App\Enumeration;

class Situacao
{
    const VIVO = 'Vivo';
    const ABATIDO = 'Abatido';

    public function getSituacao($situacao){
        switch($situacao){
            case 1: return Situacao::VIVO;
            case 2: return Situacao::ABATIDO;
            case 'Vivo': return 1;
            case 'Abatido': return 2;
        }
        return $situacao;
    }
}
?>