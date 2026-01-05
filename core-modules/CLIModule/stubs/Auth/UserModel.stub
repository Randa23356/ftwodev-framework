<?php

namespace Projects\Models;

use Engine\ModelBase;

class User extends ModelBase
{
    protected $table = 'users';
    protected $timestamps = true;
    protected $softDeletes = false;

    // Auth-related methods
    public function findByEmail($email)
    {
        return $this->newQuery()
            ->where('email', $email)
            ->first();
    }

    public function findByUsername($username)
    {
        return $this->newQuery()
            ->where('username', $username)
            ->first();
    }

    public function createUser($data)
    {
        $userData = [
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->newQuery()->insert($userData);
    }

    // Scopes for common auth operations
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }
}
