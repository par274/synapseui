<?php

namespace NativePlatform\Db\Entities;

use Doctrine\DBAL\Connection;

class User
{
    protected Connection $db;
    protected string $table = 'users';

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function findByUsername(string $username): ?array
    {
        return $this->db->createQueryBuilder()
            ->select('*')
            ->from($this->table)
            ->where('username = :u')
            ->setParameter('u', $username)
            ->executeQuery()
            ->fetchAssociative() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        return $this->db->createQueryBuilder()
            ->select('*')
            ->from($this->table)
            ->where('email = :e')
            ->setParameter('e', $email)
            ->executeQuery()
            ->fetchAssociative() ?: null;
    }

    public function findById(int $id): ?array
    {
        return $this->db->createQueryBuilder()
            ->select('*')
            ->from($this->table)
            ->where('id = :id')
            ->setParameter('id', $id)
            ->executeQuery()
            ->fetchAssociative() ?: null;
    }

    public function updatePassword(int $id, string $newPassword): bool
    {
        return (bool) $this->db->update($this->table, ['password' => $newPassword], ['id' => $id]);
    }

    public function create(array $data): bool
    {
        return (bool) $this->db->insert($this->table, $data);
    }
}
