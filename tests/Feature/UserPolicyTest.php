<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

function userPolicyUser(string $prefix = 'user-policy'): User
{
    static $id = 0;
    $id++;

    return User::create([
        'name' => 'User Policy '.$id,
        'username' => $prefix.'-'.$id,
        'email' => $prefix.'-'.$id.'@example.com',
        'password' => 'password',
        'role' => User::ROLE_PETUGAS,
        'is_active' => true,
    ]);
}

test('admin boleh menonaktifkan akun pengguna lain', function () {
    $admin = userPolicyUser('admin-policy');
    $target = userPolicyUser('target-policy');

    expect(Gate::forUser($admin)->allows('deactivate', $target))->toBeTrue()
        ->and($admin->can('deactivate', $target))->toBeTrue();
});

test('admin tidak boleh menonaktifkan akunnya sendiri', function () {
    $admin = userPolicyUser('admin-diri');

    expect(Gate::forUser($admin)->allows('deactivate', $admin))->toBeFalse()
        ->and($admin->can('deactivate', $admin))->toBeFalse();
});

test('policy membandingkan identitas akun bukan hanya role', function () {
    $admin = userPolicyUser('admin-identitas');
    $lain = userPolicyUser('lain-identitas');

    expect($admin->is($admin))->toBeTrue()
        ->and($admin->is($lain))->toBeFalse()
        ->and(Gate::forUser($admin)->allows('deactivate', $lain))->toBeTrue();
});
