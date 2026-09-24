<?php

return [
    'title' => 'Flujo de caja: Planificado, Real y Proyectado',
    'subtitle' => 'Diferencia acumulada repartida entre los meses restantes de cada año',
    'planned' => 'Planificado',
    'actual' => 'Real',
    'projected' => 'Proyectado',
    'series' => 'Serie',
    'calculation' => 'Cálculo de la proyección por año',
    'year' => 'Año',
    'closed_months' => 'Meses cerrados',
    'closed_planned' => 'Planificado en meses cerrados',
    'closed_actual' => 'Real en meses cerrados',
    'difference' => 'Diferencia (Planificado − Real)',
    'remaining_months' => 'Meses restantes',
    'per_month' => 'Diferencia por mes',
    'explanation' => 'Corte: inicio de :month en cada año mostrado. Se compara Planificado y Real de los meses anteriores y se reparte la diferencia entre el mes actual y diciembre. Real se agrupa por fecha del documento; los datos del mes en curso se muestran, pero no intervienen en la diferencia acumulada. Se aplican los filtros y la moneda seleccionados.',
    'formula' => 'Proyectado = Planificado del mes + (Planificado acumulado − Real acumulado) / meses restantes. Cada año se calcula por separado; se usa el mes actual como corte en todos los años mostrados. Meses sin Real = 0; registros sin fecha del documento quedan excluidos. Se redondea a 2 decimales al mostrar la proyección, sin modificar los milestones guardados.',
];
