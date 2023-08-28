<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Spatie\Activitylog\Models\Activity;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExportAuditTrail implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $activities = Activity::all();
        $data=[];
        $i=0;
        foreach ($activities as $activity) {
            $act = [];
            $act['created_at'] = $activity->created_at;
            if(isset($activity->causer)){
                $act['name'] = $activity->causer->first_name.' '.$activity->causer->last_name;
            }else{
                $act['name'] ='';
            }
            $act['action'] = strtoupper(last(explode(" ", $activity->description)));
            if(isset($activity->subject)){
                $act['subject'] = last(explode("\\", get_class($activity->subject)));
            }else{
                $act['subject'] = '';
            }
            $act['description'] = $activity->description;

            $data[$i]=$act;
            $i++;
        }
        return collect($data);
    }

    public function headings(): array
    {
        return [
            'Date',
            'Name',
            'Action',
            'Subject',
            'Description'
        ];
    }
}
