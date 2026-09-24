<?php

return [
    'title' => 'Flusso di cassa: Pianificato, Reale e Proiettato',
    'subtitle' => 'Differenza accumulata distribuita sui mesi rimanenti di ogni anno',
    'planned' => 'Pianificato',
    'actual' => 'Reale',
    'projected' => 'Proiettato',
    'series' => 'Serie',
    'calculation' => 'Calcolo della proiezione per anno',
    'year' => 'Anno',
    'closed_months' => 'Mesi chiusi',
    'closed_planned' => 'Pianificato nei mesi chiusi',
    'closed_actual' => 'Reale nei mesi chiusi',
    'difference' => 'Differenza (Pianificato − Reale)',
    'remaining_months' => 'Mesi rimanenti',
    'per_month' => 'Differenza per mese',
    'explanation' => 'Data limite: inizio di :month in ogni anno visualizzato. La differenza tra Pianificato e Reale dei mesi precedenti viene distribuita dal mese corrente a dicembre. Reale è raggruppato per data del documento; i dati del mese corrente sono visibili ma esclusi dalla differenza accumulata. Si applicano i filtri e la valuta selezionati.',
    'formula' => 'Proiettato = Pianificato mensile + (Pianificato accumulato − Reale accumulato) / mesi rimanenti. Ogni anno è calcolato separatamente; il mese corrente viene usato come limite in ogni anno visualizzato. Mesi senza Reale = 0; record senza data del documento esclusi. Proiezioni arrotondate a 2 decimali senza modificare le milestone salvate.',
];
