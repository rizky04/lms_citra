<?php

namespace App\Support;

// Nama role spatie, dipusatkan supaya tidak ada string liar di controller/seeder.
final class Role
{
    public const SUPER_ADMIN = 'super_admin';
    public const ADMIN_SEKOLAH = 'admin_sekolah';
    public const GURU = 'guru';
    public const SISWA = 'siswa';

    public const SEMUA = [self::SUPER_ADMIN, self::ADMIN_SEKOLAH, self::GURU, self::SISWA];
}
