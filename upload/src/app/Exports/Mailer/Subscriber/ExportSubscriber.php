<?php

namespace App\Exports\Mailer\Subscriber;

use App\Models\Subscriber\Subscriber;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExportSubscriber implements FromCollection, WithHeadings
{
    public function headings(): array
    {
        return [
            __t('name'),
            __t('email'),
            __t('list'),
        ];
    }

    public function collection()
    {
        $data = Subscriber::query()->with('lists')->get();
        return $data->map(function ($row) {
            $listName = []; 
            foreach ($row->lists as $list) {
                $listName[]  = $list->name;
            }
            return [
                'name' => $row->full_name,
                'email' => $row->email,
                'list' => implode(', ',$listName),
            ];
        });
    }
}
