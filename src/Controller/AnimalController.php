<?php

namespace App\Controller;

use App\Entity\Animal;
use App\Enumeration\Situacao;
use App\Form\AnimalType;
use App\Repository\AnimalRepository;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AnimalController extends AbstractController
{

    public function index(): Response
    {

        return $this->render('homePage.html.twig');
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

    /**
     * @Route("/", name="animal_adicionar")
     */
    public function adicionar(Request $request,EntityManagerInterface $em) : Response
    {
        $this->index();
        $animais = $this->listar($em);
        $animal = new Animal();
        $Situacao = new Situacao();
        $data['id'] = '!';
        try{
            $data['id'] = $em->getRepository(Animal::class)->findNextId() + 1;
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
                $em->persist($animal);
                $em->flush();
                $this->addFlash('success',"Animal inserido com sucesso!");
            }catch (\Exception $e){
                $this->addFlash('erro','Falha ao adicionar animal!');
            }
            return $this->redirectToRoute("animal_adicionar");
        }

        $data['titulo'] = 'Adicionar Animal';
        $data['form'] = $form;
        $data['animais'] = $animais;
        return $this->renderForm('animal/form.html.twig',$data);
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
            //    return $this->redirectToRoute("animal_editar",['id'=>$id]);

            }catch (\Exception $e){
                $this->addFlash('erroEdit','Falha ao Alterar o animal!');
            //    return $this->redirectToRoute("animal_editar",['id'=>$id]);
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
    public function pageAbate(EntityManagerInterface $orm): Response
    {
        $animais = $orm->getRepository(Animal::class)->findAnimaisAbate();

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
            $animal->setSituacao(Situacao::getSituacao("abatido"));
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
    public function relatorioAnimaisAbatidos(EntityManagerInterface $orm): Response
    {
        $animais = array();
        try{
            $animais = $orm->getRepository(Animal::class)->findAnimaisAbatidos();
        }catch (\Exception $e){
            $this->addFlash('erroAbate','Falha ao Acessar o banco de dados!');
        }
        $data['titulo'] = 'Lista de Gados Abatidos';
        $data['animais'] = $animais;
        return $this->render('animal/relatorios/abatidos.html.twig',$data);
    }

}
