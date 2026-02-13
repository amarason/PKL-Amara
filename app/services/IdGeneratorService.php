<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class IdGeneratorService
{
    public function generateInternshipId($strata = 'S1')
    {
        $prefix = "PKL/{$strata}/";

        $count = DB::table('internship')
            ->where('internship_id', 'like', "{$prefix}%")
            ->count();

        $nextNumber = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        return "{$prefix}{$nextNumber}";
    }

    public function generateInstitutionId()
    {
        $count = DB::table('institution')->count();
        return "INST-" . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    }

    public function generateMajorId()
    {
        $count = DB::table('major')->count();
        return "MJR-" . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    }
}