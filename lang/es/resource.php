<?php

return [
    'created_at' => 'Creado el',
    'updated_at' => 'Actualizado el',
    'navigation' => [
        'group' => [
            'company' => 'EMPRESAS',
            'places' => 'LUGARES',
            'user' => 'USUARIOS',
        ],
    ],
    'notification' => [
        'success' => [
            'default_title' => 'Operación exitosa',
            'default_body' => 'Acción completada correctamente.',
        ],
    ],
    'category' => [
        'navigation' => [
            'label' => 'Categoría',
            'model_label' => 'Categoría',
            'plural_model_label' => 'Categoría',
        ],
        'fields' => [
            'name' => 'Nombre',
            'slug' => 'Identificador URL',
            'parent_category' => 'Superior',
            'description' => 'Descripción',
            'status' => 'Estado',
        ],
        'action' => [
            'toggle' => 'Cambiar estado',
        ],
        'notification' => [
            'toggle_status' => [
                'success' => [
                    'title' => 'Estado de categoría actualizado',
                    'body' => 'Las categorías seleccionadas se actualizaron correctamente.',
                ],
                'error' => [
                    'title' => 'Error al actualizar',
                    'body' => 'No se pudieron actualizar los estados de las categorías. Inténtalo de nuevo.',
                ],
            ],
        ],
    ],
    'post' => [
        'navigation' => [
            'label' => 'Publicación',
            'model_label' => 'Publicación',
            'plural_model_label' => 'Publicación',
        ],
        'filters' => [
            'status' => 'Filtrar por estado',
        ],
        'fields' => [
            'type' => 'Tipo',
            'title' => 'Título',
            'slug' => 'Identificador URL',
            'exercpt' => 'Extracto',
            'content' => 'Contenido',
            'feature_image' => 'Imagen',
            'is_featured' => 'Destacado',
            'comment_status' => 'Estado de comentarios',
            'status' => 'Estado',
            'published_at' => 'Fecha de publicación',
            'parent_id' => 'Superior',
            'author' => 'Autor',
            'view_count' => 'Visitas',
            'categories' => 'Categorías',
        ],
    ],
    'page' => [
        'navigation' => [
            'label' => 'Página',
            'model_label' => 'Página',
            'plural_model_label' => 'Página',
        ],
    ],
    'menu' => [
        'navigation' => [
            'label' => 'Menú',
            'model_label' => 'Menú',
            'plural_model_label' => 'Menú',
        ],
        'fields' => [
            'name' => 'Nombre del menú',
            'slug' => 'Identificador URL',
        ],
    ],
    'setting' => [
        'company_name' => 'Nombre de la empresa',
        'logo' => 'Logotipo',
        'header_menu' => 'Menú de cabecera',
        'footer_menu' => 'Menú del pie de página',
    ],
];
