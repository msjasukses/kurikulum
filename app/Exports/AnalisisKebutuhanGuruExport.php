<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AnalisisKebutuhanGuruExport implements FromView, ShouldAutoSize
{
    public function __construct(private array $data)
    {
    }

    public function view(): View
    {
        return view('kurikulum.analisis-guru-excel', $this->data);
    }
}
