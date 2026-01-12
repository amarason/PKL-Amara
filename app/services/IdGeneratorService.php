<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class IdGeneratorService
{
    public function generateInternshipId($strata = 'S1')
    {
        $count = DB::table('internship')
            ->where('internship_id', 'like', "PKL/{$strata}/%")
            ->count();

        $nextNumber = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        return "PKL/{$strata}/{$nextNumber}";
    }

    public function generateInstitutionId()
    {
        $count = DB::table('institution')->count();
        $nextNumber = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        return "INST-{$nextNumber}";
    }

    public function generateMajorId()
    {
        $count = DB::table('major')->count();
        $nextNumber = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        return "MJR-{$nextNumber}";
    }
}