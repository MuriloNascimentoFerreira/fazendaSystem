<?php

namespace App\Repository;

use App\Entity\Animal;
use App\Enumeration\Situacao;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Utils;

/**
 * @extends ServiceEntityRepository<Animal>
 *
 * @method Animal|null find($id, $lockMode = null, $lockVersion = null)
 * @method Animal|null findOneBy(array $criteria, array $orderBy = null)
 * @method Animal[]    findAll()
 * @method Animal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnimalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Animal::class);
    }

    public function add(Animal $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Animal $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findCodigo($animal)
    {
        $a = $this->createQueryBuilder('a')
            ->andWhere('a.situacao = :situacao') //situacao = 1
            ->andWhere('a.codigo = :codigo') //codigo = $codigo
            ->setParameter('situacao', 1)
            ->setParameter('codigo', $animal->getCodigo())
            ->getQuery()
            ->getOneOrNullResult()
            ;

        if($a){
            return true;
        }else{
            return false;
        }
    }

    public function findCodigoEditar($animal)
    {
        $a = $this->createQueryBuilder('a')
            ->andWhere('a.situacao = :situacao') //situacao = 1
            ->andWhere('a.codigo = :codigo') //codigo = $codigo
            ->andWhere('a.id <> :id') //id = id
            ->setParameter('situacao', 1)
            ->setParameter('codigo', $animal->getCodigo())
            ->setParameter('id', $animal->getId())
            ->getQuery()
            ->getOneOrNullResult()
        ;
        if($a){
            return true;
        }else{
            return false;
        }
    }

    public function findAnimaisAbate()
    {
        $dataAgora = new \DateTime();
        $date = $dataAgora->sub(new \DateInterval('P5Y'));
        $animais = $this->createQueryBuilder('a')
            ->where('a.nascimento < :data')
            ->orWhere('a.leite < :leite')
            ->orWhere('a.leite < :leite and a.racao > :racao')
            ->orWhere('a.peso > :peso')
            ->andWhere('a.situacao = :situacao') //situacao = 1
            ->setParameter('leite', 40)
            ->setParameters(['leite'=> 70, 'racao'=> 350])
            ->setParameter('situacao', 1)
            ->setParameter('peso', 270)
            ->setParameter('data', $date)
            ->getQuery()
            ->getResult()
        ;
        if($animais){
            return $animais;
        }else{
            return $animais = array();
        }
    }

    public function getAnimalPodeSerAbatido($id)
    {
        $dataAgora = new \DateTime();
        $date = $dataAgora->sub(new \DateInterval('P5Y'));

        $animal = $this->createQueryBuilder('a')
        ->where('a.nascimento < :data')
        ->orWhere('a.leite < :leite')
        ->orWhere('a.leite < :leite and a.racao > :racao')
        ->orWhere('a.peso > :peso')
        ->andWhere('a.id = :id')
        ->andWhere('a.situacao = :situacao') //situacao = 1
        ->setParameter('leite', 40)
        ->setParameters(['leite'=> 70, 'racao'=> 350])
        ->setParameter('situacao', 1)
        ->setParameter('peso', 270)
        ->setParameter('data', $date)
        ->setParameter('id', $id)
        ->getQuery()
        ->getOneOrNullResult()
        ;
        if($animal){
            return true;
        }else{
            return false;
        }
    }

    public function findAnimaisAbatidos()
    {
        $animais =  $this->createQueryBuilder('a')
            ->andWhere('a.situacao = :situacao') //situacao = 2
            ->setParameter('situacao', 2)
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;

        if($animais){
            return $animais;
        }else{
            return $animais = array();
        }
    }

    public function findDemandaRacao()
    {
        $demandaRacao = $this->createQueryBuilder('a')
            ->select('sum(a.racao)')
            ->andWhere('a.situacao = :situacao')
            ->setParameter('situacao', 1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
        if($demandaRacao[1]){
            return $demandaRacao[1];
        }else{
            return 0.0;
        }
    }

    public function findProducaoLeite()
    {
        $producao =  $this->createQueryBuilder('a')
            ->select('sum(a.leite)')
            ->andWhere('a.situacao = :situacao')
            ->setParameter('situacao', 1)
            ->getQuery()
            ->getOneOrNullResult()
            ;

        if($producao[1]){
            return $producao[1];
        }else{
            return 0.0;
        }
    }

    //retorna o total de animais que tenham até um ano e cosumam mais de 500kg de ração por semana
    public function getTotal()
    {
        $dataAgora = new \DateTime();
        $date = $dataAgora->sub(new \DateInterval('P1Y'));
        $total = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.nascimento >= :date')
            ->andWhere('a.situacao = :situacao')
            ->andWhere('a.racao > :racao')
            ->setParameter('date', $date)
            ->setParameter('situacao', Situacao::getSituacao("Vivo"))
            ->setParameter('racao', 500)
            ->getQuery()
            ->getOneOrNullResult()
            ;
        return $total[1];
    }

}
