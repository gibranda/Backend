<?php

namespace Persona\Hris\Core\DataProvider\ORM;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\Exception\RuntimeException;
use Doctrine\ORM\QueryBuilder;
use Persona\Hris\Core\Manager\ManagerFactory;

/**
 * @author Muhamad Surya Iksanudin <surya.iksanudin@personahris.com>
 */
final class CollectionDataProvider extends AbstractDataProvider implements CollectionDataProviderInterface
{
    /**
     * @var ManagerFactory
     */
    private $managerFactory;

    /**
     * @var QueryCollectionExtensionInterface[]|array
     */
    private $collectionExtensions;

    /**
     * @var int
     */
    private $cacheLifetime;

    /**
     * @param ManagerFactory                      $managerFactory
     * @param QueryCollectionExtensionInterface[] $collectionExtensions
     * @param int                                 $cacheLifetime
     */
    public function __construct(ManagerFactory $managerFactory, array $collectionExtensions = [], $cacheLifetime = 0)
    {
        $this->managerFactory = $managerFactory;
        $this->collectionExtensions = $collectionExtensions;
        $this->cacheLifetime = $cacheLifetime;
    }

    /**
     * {@inheritdoc}
     *
     * @throws RuntimeException
     */
    public function getCollection(string $resourceClass, string $operationName = null)
    {
        $manager = $this->managerFactory->getReadManager();
        $repository = $manager->getRepository($resourceClass);
        if (!method_exists($repository, 'createQueryBuilder')) {
            throw new RuntimeException('The repository class must have a "createQueryBuilder" method.');
        }

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->filterSoftDeletable($repository->createQueryBuilder('o'), $resourceClass);

        $queryNameGenerator = new QueryNameGenerator();
        foreach ($this->collectionExtensions as $extension) {
            $extension->applyToCollection($queryBuilder, $queryNameGenerator, $resourceClass, $operationName);

            if ($extension instanceof QueryResultCollectionExtensionInterface && $extension->supportsResult($resourceClass, $operationName)) {
                return $extension->getResult($queryBuilder);
            }
        }

        $query = $queryBuilder->getQuery();

        $result = $query->getResult();

        return $result;
    }
}
