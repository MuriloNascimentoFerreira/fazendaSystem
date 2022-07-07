<?php

namespace App\Repository;

use App\Entity\Animal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\Integer;

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

    public function findNextId():?int
    {
        $conexao = $this->getEntityManager()->getConnection();
        $db = $conexao->prepare("SELECT MAX(id) FROM animal");
        $result = $db->executeQuery();
        $id = $result->fetchNumeric();
        return $id[0];
    }

    public function findAnimaisAbate(): array
    {
        return $this->createQueryBuilder('a')
            ->orWhere('a.leite < :leite') //40
            ->orWhere('a.leite < :leite and a.racao > :racao') // leite=70 racao= 350 ()
            ->andWhere('a.situacao = :situacao') //situacao = 1
            ->orWhere('a.peso > :peso') //peso = 270 kilo
            ->setParameter('leite', 40)
            ->setParameters(['leite'=> 70, 'racao'=> 350])
            ->setParameter('situacao', 1)
            ->setParameter('peso', 270)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAnimaisAbatidos(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.situacao = :situacao') //situacao = 2
            ->setParameter('situacao', 2)
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findDemandaRacao(): array
    {
        return $this->createQueryBuilder('a')
            ->select('sum(a.racao)')
            ->andWhere('a.situacao = :situacao')
            ->setParameter('situacao', 1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    public function findProducaoLeite(): ?array
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
    public function getTotal():?int
    {
        $conexao = $this->getEntityManager()->getConnection();
        $db = $conexao->prepare("SELECT COUNT(*) FROM animal WHERE TIMESTAMPDIFF(YEAR,nascimento,CURRENT_DATE()) <= 1 AND racao > 500");
        $result = $db->executeQuery();
        $quantidade = $result->fetchNumeric();
        return $quantidade[0];
    }

    public function getAbateAnimaisIdadeMaiorCinco()
    {
        $conexao = $this->getEntityManager()->getConnection();
        $db = $conexao->prepare("SELECT * FROM animal WHERE TIMESTAMPDIFF(YEAR,nascimento,CURRENT_DATE()) > 5 AND situacao = 1");
        $result = $db->executeQuery();
        $dados = $result->fetchAllAssociative();
        $animais = array();

        if($result){
            foreach($dados as $item){
                $animal = new Animal();
                $animal->setId($item['id']);
                $animal->setLeite($item['leite']);
                $animal->setRacao($item['racao']);
                $animal->setPeso($item['peso']);
                $nascimento = new \DateTime($item['nascimento']);
                $animal->setNascimento($nascimento);
                $animal->setSituacao($item['situacao']);
                $animais[] = $animal;
            }
        }

        return $animais;
    }
}
