<?php

namespace App\Http\Controllers\Subscriber;

use App\Exports\Mailer\Subscriber\ExportSubscriber;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportSubscriberController extends Controller
{
    public function export()
    {
        return Excel::download(new ExportSubscriber(), 'subscribers.csv', \Maatwebsite\Excel\Excel::CSV);
    }
}
