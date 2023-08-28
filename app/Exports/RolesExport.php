<?php

namespace App\Exports;

use App\Models\Role;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Auth;

class RolesExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $roles = Role::all();//Get all roles
        foreach ($roles as $role) {
            unset($role['guard_name']);
            $created=$role['created_at'];
            $updated=$role['updated_at'];
            unset($role['created_at']);
            unset($role['updated_at']);
           // $role['user_number'] = Auth::user()->role($role->name)->count();
            $role['created_at'] = $created;
            $role['updated_at'] = $updated;
        }

        return $roles;
    }

    public function headings(): array
    {
        return [
            '#',
            'Name',
            'Number of users',
            'Created at',
            'Updated_at'
        ];
    }
}
