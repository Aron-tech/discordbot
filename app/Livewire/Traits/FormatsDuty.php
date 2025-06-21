<?php

namespace App\Livewire\Traits;

trait FormatsDuty
{
    public function formatMinutesToHHMM($minutes): string
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%02d:%02d', $hours, $mins);
    }
}
