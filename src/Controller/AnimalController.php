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
    public function listar($entityManager): array
    {

        $animaiss = $entityManager->getRepository(Animal::class)->findAll();

        //buscar todos os animais e retornar para a função adicionar
        $animal1 = new Animal();
        $animal1->setLeite(4);
        $animal1->setPeso(4);
        $animal1->setRacao(4);
        $animal1->setSituacao(1);
        $animal1->setNascimento(new \DateTime());

        $animal2 = new Animal();
        $animal2->setLeite(11);
        $animal2->setPeso(11);
        $animal2->setRacao(11);
        $animal2->setSituacao(1);
        $animal2->setNascimento(new \DateTime());

        $animais = [$animal1, $animal2];

        return $animaiss;
    }

    /**
     * @Route("/", name="animal_adicionar")
     */
    public function adicionar(Request $request,EntityManagerInterface $em) : Response
    {
        $animais = $this->listar($em);
        $animal = new Animal();
        $Situacao = new Situacao();

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

}
