<?php

namespace WS\Repository;

class Authentication extends Repository
{

    public function getAuthenticatedUser(int $userId): array
    {
        $authenticationCookie = trim($this->app->request()->getFiltered('service_authentication', FILTER_DEFAULT));
        return $this->app->db()->selectSingle('authenticated', ['[>]user' => ['user_id' => 'user_id']],
        ['user.*'], ['authenticated.token' => $authenticationCookie, 'user_id' => $userId]);
    }

    public function postAuthenticatedUser(int $userId): string
    {
        $token = bin2hex(random_bytes(255));
        $this->app->db()->insert('authenticated', [
            'user_id' => $userId, 'token' => $token
        ]);

        return $token;
    }

    public function verifyCredential(int $userId, string $password): bool
    {
        $authentication = $this->app->db()->selectSingle('authentication', 'password_hash', ['user_id' => $userId]);

        return password_verify($password, $authentication);
    }

    public function loadCredential(int $userId, string $password): int
    {
        $this->dropCredential($userId);
        return $this->app->db()->insert('authentication', ['user_id' => $userId,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT)])->rowCount();
    }

    public function dropCredential(int $userId): int
    {
        return $this->app->db()->delete('authentication', ['user_id' => $userId])->rowCount();
    }

}