<?php
/**
 * Created by PhpStorm.
 * User: Artem
 * Date: 03.06.2019
 * Time: 22:26
 */

namespace AppBundle\Repositories;


use AppBundle\Entity\Book;
use AppBundle\Entity\Author;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\QueryBuilder;
use function Symfony\Component\DependencyInjection\Loader\Configurator\expr;

class BookRepository extends EntityRepository
{
    private $name_val = 'filter_name';
    private $description_val = 'filter_description';
    private $publicationDate_from_val = 'filter_date_from';
    private $publicationDate_to_val = 'filter_date_to';
    private $authors_val = 'filter_authors';

    public function __construct($em, Mapping\ClassMetadata $class)
    {
        parent::__construct($em, $class);
        $this->query = $this->createQueryBuilder('book')
            ->innerJoin(
            'book.authors',
            'a'
        );
    }

    public function parse_conditions($conditions)
    {
        foreach ($conditions as $key => $value){
            if ($value != null && $value != ''){
                if ($key == $this->name_val){
                    $this->query->andWhere('book.name LIKE :name')
                        ->setParameter('name', '%'.$value.'%');
                } elseif ($key == $this->description_val){
                    $this->query->andWhere('book.description LIKE :description')
                        ->setParameter('description', '%'.$value.'%');
                } elseif ($key == $this->publicationDate_from_val){
                    $this->query->andWhere('book.publicationDate >= :publicationDateFrom')
                        ->setParameter('publicationDateFrom', $value);
                } elseif ($key == $this->publicationDate_to_val){
                    $this->query->andWhere('book.publicationDate <= :publicationDateTo')
                        ->setParameter('publicationDateTo', $value);
                } elseif ($key == $this->authors_val){
//                    foreach ($value as $author) {
//                        $this->query->andWhere('a.id IN (:authors)')
//                            ->setParameter('authors', $value);
//                    }
//                    $sub = new QueryBuilder($this->_em);
//                    $sub->select('aa');
//                    $sub->from("AppBundle\Entity\Book","aa");
//                    $sub->andWhere($sub->expr()->exists('select id from aa.authors where id = 2'));
//
//                    $this->query->andWhere($this->query->expr()->exists($sub->getDQL()));
                }

            }
        }
    }

    public function getQuery()
    {
        return $this->query->getQuery();
    }
}