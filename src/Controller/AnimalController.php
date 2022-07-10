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

    /**
     * @Route("animal/adicionar", name="animal_adicionar")
     */
    public function adicionar(Request $request,EntityManagerInterface $orm, PaginatorInterface $paginator) : Response
    {
        $animal = new Animal();
        $Situacao = new Situacao();
        $data['animais'] = array();
        $data['animais'] = $this->listar($orm);
        $data['animais'] = $paginator->paginate($data['animais'],$request->query->getInt('page',1),8);

        try{
            $form = $this->createForm(AnimalType::class, $animal, ['attr'=> ['class' => 'row align-items-center d-inline-flex']]);
            $form->handleRequest($request);

        }catch (\Exception $e){
            $this->addFlash('erro','Falha ao adicionar animalll!');
        }

        if($form->isSubmitted() && $form->isValid()){
            $animal->setSituacao($Situacao::getSituacao(Situacao::VIVO));
            if(!$orm->getRepository(Animal::class)->findCodigo($animal)){
                try{
                    $orm->persist($animal);
                    $orm->flush();
                    $this->addFlash('success',"Animal inserido com sucesso!");
                }catch (\Exception $e){
                    $this->addFlash('erro','Falha ao adicionar o animal!');
                }
            }else{
                $this->addFlash('erro','Falha ao adicionar, código do animal já existente!');
            }


            return $this->redirectToRoute("animal_adicionar");
        }


        $data['titulo'] = 'Adicionar Animal';
        $data['form'] = $form;
        return $this->renderForm('animal/adicionar.html.twig',$data);
    }

    public function listar($entityManager)
    {
        $animais = array();
        try{
            $animais = $entityManager->getRepository(Animal::class)->findAll();
        }catch (\Exception $e){
            $this->addFlash('erroEdit','Falha na listagem!');
        }
        return $animais;
    }

    /**
     * @Route("animal/editar/{id}", name="animal_editar")
     */
    public function editar(int $id,Request $request, EntityManagerInterface $orm): Response
    {
        $animal = $orm->getRepository(Animal::class)->find($id);
        $formulario = $this->createForm(AnimalType::class, $animal, ['attr'=> ['class' => 'row align-items-center d-inline-flex']]);
        $formulario->handleRequest($request);
        if($formulario->isSubmitted() && $formulario->isValid()){

            if($animal->getSituacao() == Situacao::getSituacao('Vivo')) {
                if (!$orm->getRepository(Animal::class)->findCodigoEditar($animal)) {
                    try {
                        $orm->persist($animal);
                        $orm->flush();
                        $this->addFlash('successEdit', "Animal Alterado com sucesso!");
                    } catch (\Exception $e) {
                        $this->addFlash('erroEdit', 'Falha ao Alterar o animal!');
                    }
                }else {
                    $this->addFlash('erro', 'Falha ao editar, código do animal já existente!');
                }
            }else{
                $this->addFlash('erro', 'Falha ao editar, não é possivel editar animal abatido!');
            }
            return $this->redirectToRoute("animal_adicionar");
        }

        $data['titulo'] = 'Editar Animal';
        $data['formulario'] = $formulario;
        $data['id'] = $animal->getId();
        return $this->renderForm('animal/editar.html.twig', $data);
    }

    /**
     * @Route("animal/excluir/{id}", name="animal_excluir")
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
     * @Route("animal/page_abate", name="animais_abate")
     */
    public function pageAbate(EntityManagerInterface $orm, Request $request, PaginatorInterface $paginator): Response
    {
        $animais = array(Animal::class);

        try{
            $animais = $orm->getRepository(Animal::class)->findAnimaisAbate();

        }catch (\Exception $e){
            $this->addFlash('erroEdit','Falha ao carregar os animais para o abate!');
        }

        foreach ($animais as $animal){
            if($animal->getSituacao() == Situacao::getSituacao('Abatido')){
                $animais->remove($animal);
            }
        }

        $animais = $paginator->paginate($animais,$request->query->getInt('page',1),8);

        $data['titulo'] = 'Lista de Gados para abate';
        $data['animais'] = $animais;
        return $this->render('animal/abate.html.twig', $data);
    }

    /**
     * @Route("animal/page_abate/abater/{id}", name="animal_abater")
     */
    public function abater(int $id,EntityManagerInterface $orm): Response
    {
        try{
            if($orm->getRepository(Animal::class)->getAnimalPodeSerAbatido($id)){
                $animal = $orm->getRepository(Animal::class)->find($id);
                $animal->setSituacao(Situacao::getSituacao("Abatido"));
                $orm->persist($animal);
                $orm->flush();
                $this->addFlash('successAbate',"Animal Abatido com sucesso!");

            }else{
                $this->addFlash('erroAbate','Falha ao abater o animal!');
            }
        }catch (\Exception $e){
            $this->addFlash('erroAbate','Falha ao abater o animal!');
        }
        return $this->redirectToRoute("animais_abate");
    }

    /**
     * @Route("animal/abatidos", name="animais_abatidos")
     */
    public function relatorioAnimaisAbatidos(EntityManagerInterface $orm, Request $request, PaginatorInterface $paginator): Response
    {
        $animais = array();
        try{
            $animais = $orm->getRepository(Animal::class)->findAnimaisAbatidos();
        }catch (\Exception $e){
            $this->addFlash('erroAbate','Falha ao listar animais abatidos!');
        }

        $data['animais'] = $paginator->paginate($animais,$request->query->getInt('page',1),8);
        $data['titulo'] = 'Lista de Gados Abatidos';
        return $this->render('animal/relatorios/abatidos.html.twig',$data);
    }

    public function producaoLeite($entityManager)
    {
        $quantidade = 0.0;
        try{
            $quantidade = $entityManager->getRepository(Animal::class)->findProducaoLeite();
        }catch (\Exception $e){
            $this->addFlash('erroEdit','Falha ao calcular a produçao de leite!');
        }
        return $quantidade;
    }

    public function demandaRacao($entityManager)
    {
        $quantidade = 0.0;
        try{
            $quantidade = $entityManager->getRepository(Animal::class)->findDemandaRacao();
        }catch (\Exception $e){
            $this->addFlash('erroEdit','Falha ao calcular a demanda de ração!');
        }
        return $quantidade;
    }

    //retorna o total de animais que tenham até um ano e cosumam mais de 500kg de ração por semana
    private function relatorio1($orm)
    {
        $quantidade = 0.0;
        try{
            $quantidade = $orm->getRepository(Animal::class)->getTotal();
        }catch (\Exception $e){
            $this->addFlash('erroEdit','Falha ao conectar com Banco de dados!');
        }
        return $quantidade;
    }
}
