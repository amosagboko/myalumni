<?php

namespace App\Imports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Student([
            'firstname'         => $row['firstname'],
            'surname'           => $row['surname'],
            'matriculation_id'  => $row['matriculation_id'],
            'programme'         => $row['programme'],
            'department'        => $row['department'],
            'faculty'           => $row['faculty'],
            'year_of_graduation'=> $row['year_of_graduation'],
        ]);
    }
}
