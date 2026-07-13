<?php
// app/Helpers/ProdiFilter.php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class ProdiFilter
{
    /**
     * Ambil id_prodi user yang sedang login.
     * Null = admin/dekan (lihat semua), ada nilai = kaprodi (filter by prodi)
     */
    public static function getProdiId(): ?int
    {
        $user = Auth::user();

        if (!$user) return null;

        // Admin & Dekan → lihat semua (return null = tidak difilter)
        if ($user->isAdmin() || $user->isDekan()) {
            return null;
        }

        // Kaprodi atau Sekretaris Prodi → filter by id_prodi user
        if ($user->isKaprodi() || $user->isSekretarisProdi()) {
            return $user->getProdiId();
        }

        // Dosen → filter by prodi dosen
        if ($user->isDosen() && $user->dosen) {
            return $user->dosen->id_prodi;
        }

        return null;
    }

    /**
     * Apakah user saat ini harus difilter by prodi?
     */
    public static function shouldFilter(): bool
    {
        return self::getProdiId() !== null;
    }
}
