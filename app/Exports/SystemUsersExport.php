<?php

namespace App\Exports;

use App\Models\SystemUser;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Auth;
class SystemUsersExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $systemusers = SystemUser::all();
        foreach ($systemusers as $user) {
            $created=$user['created_at'];
            $updated=$user['updated_at'];
            unset($user['created_at']);
            unset($user['updated_at']);
            unset($user['role_id']);
            unset($user['branch_id']);
            $role = $user->getRoleNames();
            $user['role'] = $role->implode(", ");
            $user['created_at'] = $created;
            $user['updated_at'] = $updated;
        }
        return $systemusers;
    }

    public function headings(): array
    {
        return [
            '#',
            'Phone',
            'Email',
            'User Status',
            'Other Names',
            'Last Name',
            'First Name',
            'Username',
            '',
            'Roles',
            'Created at',
            'Updated at'
        ];
    }
}
