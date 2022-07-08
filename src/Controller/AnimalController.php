<?php

namespace App\Controller;

use App\Entity\Animal;
use App\Enumeration\Situacao;
use App\Form\AnimalType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Repository\RepositoryFactory;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AnimalController extends AbstractController
{
    protected $data;
    public function __construct()
    {
        $this->data = array();
    }

    /**
     * @Route("/", name="home_page")
     */
    public function index(EntityManagerInterface $orm): Response
    {
        //total de animais que tenham até um ano e cosumam mais de 500kg de ração por semana
        $this->data['relatorio1'] = array();

        $this->data['producao_leite'] = array();
        $this->data['demanda_racao'] = array();

        $this->data['relatorio1'] = $this->relatorio1($orm);
        $this->data['producao_leite'] = $this->producaoLeite($orm);
        $this->data['demanda_racao'] = $this->demandaRacao($orm);

        return $this->render('homePage.html.twig',$this->data);
    }

    public function producaoLeite($entityManager):float
    {
        $quantidade = 0.0;
        try{
            $quantidade = $entityManager->getRepository(Animal::class)->findProducaoLeite()[1];
        }catch (\Exception $e){
            $this->addFlash('erroEdit','Falha ao conectar com Banco de dados!');
        }
        return $quantidade;
    }

    public function demandaRacao($entityManager):float
    {
        $quantidade = 0.0;
        try{
            $quantidade = $entityManager->getRepository(Animal::class)->findDemandaRacao()[1];
        }catch (\Exception $e){
            $this->addFlash('erroEdit','Falha ao conectar com Banco de dados!');
        }
        return $quantidade;
    }

    public function listar($entityManager): array
    {
        $animais = array();
        try{
            $animais = $entityManager->getRepository(Animal::class)->findAll();
        }catch (\Exception $e){
            $this->addFlash('erroEdit','Falha ao conectar com Banco de dados!');
        }
        return $animais;
    }

    //retorna o total de animais que tenham até um ano e cosumam mais de 500kg de ração por semana
    private function relatorio1($orm)
    {
        $quantidade = 0;
        try{
            $quantidade = $orm->getRepository(Animal::class)->getTotal();
        }catch (\Exception $e){
            $this->addFlash('erroEdit','Falha ao conectar com Banco de dados!');
        }
        return $quantidade;
    }

    /**
     * @Route("/adicionar", name="animal_adicionar")
     */
    public function adicionar(Request $request,EntityManagerInterface $orm, PaginatorInterface $paginator) : Response
    {
        $animal = new Animal();
        $Situacao = new Situacao();
        $data['animais'] = array();
        $data['animais'] = $this->listar($orm);
        $data['id'] = '!';

        $data['animais'] = $paginator->paginate($data['animais'],$request->query->getInt('page',1),8);
        try{
            $data['id'] = $orm->getRepository(Animal::class)->findNextId() + 1;
        }catch (\Exception $e){
            $this->addFlash('erroADD','Falha ao conectar com Banco de dados!');
        }

        try{
            $form = $this->createForm(AnimalType::class, $animal, ['attr'=> ['class' => 'row align-items-center d-inline-flex']]);
            $form->handleRequest($request);
        }catch (\Exception $e){
            $this->addFlash('erro','Falha ao adicionar animal!');
        }

        if($form->isSubmitted() && $form->isValid()){
            $animal->setSituacao($Situacao::getSituacao(Situacao::VIVO));

            try{
                $orm->persist($animal);
                $orm->flush();
                $this->addFlash('success',"Animal inserido com sucesso!");
            }catch (\Exception $e){
                $this->addFlash('erro','Falha ao adicionar animal!');
            }
            return $this->redirectToRoute("animal_adicionar");
        }


        $data['titulo'] = 'Adicionar Animal';
        $data['form'] = $form;
        return $this->renderForm('animal/adicionar.html.twig',$data);
    }

    /**
     * @Route("/editar/{id}", name="animal_editar")
     */
    public function editar(int $id,Request $request, EntityManagerInterface $orm): Response
    {
        $animal = $orm->getRepository(Animal::class)->find($id);
        $formulario = $this->createForm(AnimalType::class, $animal, ['attr'=> ['class' => 'row align-items-center d-inline-flex']]);
        $formulario->handleRequest($request);

        if($formulario->isSubmitted() && $formulario->isValid()){
            try{
                $orm->persist($animal);
                $orm->flush();
                $this->addFlash('successEdit',"Animal Alterado com sucesso!");
            }catch (\Exception $e){
                $this->addFlash('erroEdit','Falha ao Alterar o animal!');
            }
            return $this->redirectToRoute("animal_adicionar");
        }

        $data['titulo'] = 'Editar Animal';
        $data['formulario'] = $formulario;
        $data['id'] = $animal->getId();
        return $this->renderForm('animal/editar.html.twig', $data);
    }

    /**
     * @Route("/excluir/{id}", name="animal_excluir")
     */
    public function excluir(int $id, EntityManagerInterface $orm)
    {
        try{
            $animal = $orm->getRepository(Animal::class)->find($id);
            $orm->getRepository(Animal::class)->remove($animal);
            $orm->flush();
            $this->addFlash('successRemove',"Animal excluido com sucesso!");

        }catch (\Exception $e){
            $this->addFlash('erroRemove','Falha ao excluir o animal!');
        }
        return $this->redirectToRoute("animal_adicionar");
    }

    /**
     * @Route("/page_abate", name="animais_abate")
     */
    public function pageAbate(EntityManagerInterface $orm, Request $request, PaginatorInterface $paginator): Response
    {

        $animais = array(Animal::class);
        $animais2 = array(Animal::class);
        try{
            $animais = $orm->getRepository(Animal::class)->findAnimaisAbate();
            $animais2 = $orm->getRepository(Animal::class)->getAbateAnimaisIdadeMaiorCinco();
        }catch (\Exception $e){
            $this->addFlash('erroEdit','Falha ao conectar com Banco de dados!');
        }

        foreach ($animais2 as $animal){
            if (!in_array($animal, $animais)){
                $animais[] = $animal;
            }
        }

        $animais = $paginator->paginate($animais,$request->query->getInt('page',1),8);

        $data['titulo'] = 'Lista de Gados para abate';
        $data['animais'] = $animais;
        return $this->render('animal/abate.html.twig', $data);
    }

    /**
     * @Route("/page_abate/abater/{id}", name="animal_abater")
     */
    public function abater(int $id,EntityManagerInterface $orm): Response
    {

        try{
            $animal = $orm->getRepository(Animal::class)->find($id);
            $animal->setSituacao(Situacao::getSituacao("Abatido"));
            $orm->persist($animal);
            $orm->flush();
            $this->addFlash('successAbate',"Animal Abatido com sucesso!");

        }catch (\Exception $e){
            $this->addFlash('erroAbate','Falha ao abater o animal!');
        }
        return $this->redirectToRoute("animais_abate");
    }

    /**
     * @Route("/abatidos", name="animais_abatidos")
     */
    public function relatorioAnimaisAbatidos(EntityManagerInterface $orm, Request $request, PaginatorInterface $paginator): Response
    {
        $animais = array();
        try{
            $animais = $orm->getRepository(Animal::class)->findAnimaisAbatidos();
        }catch (\Exception $e){
            $this->addFlash('erroAbate','Falha ao Acessar o banco de dados!');
        }

        $data['animais'] = $paginator->paginate($animais,$request->query->getInt('page',1),8);
        $data['titulo'] = 'Lista de Gados Abatidos';
        return $this->render('animal/relatorios/abatidos.html.twig',$data);
    }

}
