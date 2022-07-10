<?php

namespace App\Repository;

use App\Entity\Animal;
use App\Enumeration\Situacao;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
        return $this->createQueryBuilder('a')
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
    }

    public function findAnimaisAbatidos()
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.situacao = :situacao') //situacao = 2
            ->setParameter('situacao', 2)
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findDemandaRacao()
    {
        return $this->createQueryBuilder('a')
            ->select('sum(a.racao)')
            ->andWhere('a.situacao = :situacao')
            ->setParameter('situacao', 1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    public function findProducaoLeite()
    {
        return $this->createQueryBuilder('a')
            ->select('sum(a.leite)')
            ->andWhere('a.situacao = :situacao')
            ->setParameter('situacao', 1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    //retorna o total de animais que tenham até um ano e cosumam mais de 500kg de ração por semana
    public function getTotal()
    {
        $dataAgora = new \DateTime();
        $date = $dataAgora->sub(new \DateInterval('P1Y'));
        return $this->createQueryBuilder('a')
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
    }

}
