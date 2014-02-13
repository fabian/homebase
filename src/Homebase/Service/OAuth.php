<?php

namespace Homebase\Service;

class OAuth
{
    protected $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function createAccessToken($client, $user)
    {
        $token = sha1(mt_rand());

        $sql = 'INSERT INTO `oauth_tokens` (`client`, `user`, `token`, `created`) VALUES (?, ?, ?, NOW())';

        $result = $this->database->executeUpdate($sql, array($client, $user, $token));

        return $token;
    }

    public function getClient($id)
    {
        $sql = 'SELECT * FROM `oauth_clients` WHERE `id` = ?';

        $client = $this->database->fetchAssoc($sql, array($id));

        return $client;
    }
}
