<?php

namespace App\Service;

use App\Entity\Animal;

class Relatorios
{
    public function listar($entityManager)
    {
        $animais = array();
        try{
            $animais = $entityManager->getRepository(Animal::class)->findAll();
        }catch (\Exception $e){
            $this->addFlash('erro','Falha na listagem!');
        }
        return $animais;
    }

    //retorna o total de animais que tenham até um ano e cosumam mais de 500kg de ração por semana
    public function relatorio1($orm)
    {
        $quantidade = 0.0;
        try{
            $quantidade = $orm->getRepository(Animal::class)->getTotal();
        }catch (\Exception $e){
            $this->addFlash('erro','Falha ao calcular  relatório 1!');
        }
        return $quantidade;
    }
    public function demandaRacao($entityManager)
    {
        $quantidade = 0.0;
        try{
            $quantidade = $entityManager->getRepository(Animal::class)->findDemandaRacao();
        }catch (\Exception $e){
            $this->addFlash('erro','Falha ao calcular a demanda de ração!');
        }
        return $quantidade;
    }

    public function producaoLeite($entityManager)
    {
        $quantidade = 0.0;
        try{
            $quantidade = $entityManager->getRepository(Animal::class)->findProducaoLeite();
        }catch (\Exception $e){
            $this->addFlash('erro','Falha ao calcular a produçao de leite!');
        }
        return $quantidade;
    }
}
?>