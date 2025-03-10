<?php

namespace App\Enum;

enum Schedules: string 
{
    case Matin = 'Matin';
    case Après_Midi = 'Après Midi';
    case Journée = 'Journée';

}