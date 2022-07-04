<?php

namespace App\Controller;

use App\Entity\Animal;
use App\Enumeration\Situacao;
use App\Form\AnimalType;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;

class AnimalController extends AbstractController
{
    /**
     * @Route("/index", name="app_animal")
     */
    public function index(EntityManagerInterface $orm): Response
    {
        $animal = new Animal();
        $animal->setLeite(5);
        $animal->setRacao(5);
        $animal->setPeso(5);
        $data = new \DateTime();
        $animal->setNascimento($data);
        $animal->setSituacao(1);

        try{
            $orm->persist($animal);
            $orm->flush();
        }catch (Exception $e){

        }

        return $this->render('animal/index.html.twig', [
            'controller_name' => 'AnimalController',
        ]);
    }

    /**
     * @Route("/", name="animal_adicionar")
     */
    public function adicionar(Request $request,EntityManagerInterface $em) : Response
    {
        $animal = new Animal();
        $Situacao = new Situacao();

        $form = $this->createForm(AnimalType::class, $animal, ['attr'=> ['class' => 'row align-items-center d-inline-flex']]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $animal->setSituacao($Situacao::getSituacao(Situacao::VIVO));

            try{
                $em->persist($animal);
                $em->flush();
            }catch (Exception $e){

            }
            $animal = new Animal();
        }

        $data['titulo'] = 'Adicionar Animal';
        $data['form'] = $form;

        return $this->renderForm('animal/form.html.twig',$data);
    }

}
