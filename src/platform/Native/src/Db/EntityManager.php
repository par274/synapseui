<?php

namespace NativePlatform\Db;

use Doctrine\DBAL\Connection;
use NativePlatform\Db\Entities\User;

class EntityManager
{
    public Connection $db;
    public User $user;

    public function __construct(Connection $db)
    {
        $this->db = $db;
        $this->user = new User($this->db);
    }

    public function test()
    {
        return $this->db->executeQuery('SELECT 1');
    }

    public function find(string $table, array $criteria): ?array
    {
        $qb = $this->db->createQueryBuilder()
            ->select('*')
            ->from($table);

        foreach ($criteria as $key => $value)
        {
            $qb->andWhere("{$key} = :{$key}")->setParameter($key, $value);
        }

        return $qb->executeQuery()->fetchAssociative() ?: null;
    }

    public function findAll(string $table, array $criteria = []): array
    {
        $qb = $this->db->createQueryBuilder()
            ->select('*')
            ->from($table);

        foreach ($criteria as $key => $value)
        {
            $qb->andWhere("{$key} = :{$key}")->setParameter($key, $value);
        }

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function insert(string $table, array $data): bool
    {
        return (bool) $this->db->insert($table, $data);
    }

    public function update(string $table, array $data, array $where): bool
    {
        return (bool) $this->db->update($table, $data, $where);
    }

    public function delete(string $table, array $where): bool
    {
        return (bool) $this->db->delete($table, $where);
    }
}
