<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;

class LegalController extends Controller
{
    public function cgu(Request $request): void
    {
        $this->render('legal/cgu', [
            'title' => 'Conditions Générales d\'Utilisation - Le Bon Resto',
            'meta_description' => 'Conditions Générales d\'Utilisation de Le Bon Resto, annuaire des restaurants en Algérie.'
        ]);
    }

    public function confidentialite(Request $request): void
    {
        $this->render('legal/confidentialite', [
            'title' => 'Politique de Confidentialité - Le Bon Resto',
            'meta_description' => 'Politique de confidentialité et protection des données personnelles de Le Bon Resto.'
        ]);
    }
}
