<?php

return [
    'created_at' => 'Creato il',
    'updated_at' => 'Aggiornato il',
    'navigation' => [
        'group' => [
            'company' => 'AZIENDE',
            'places' => 'LUOGHI',
            'user' => 'UTENTI',
        ],
    ],
    'notification' => [
        'success' => [
            'default_title' => 'Operazione riuscita',
            'default_body' => 'Azione completata correttamente.',
        ],
    ],
    'category' => [
        'navigation' => [
            'label' => 'Categoria',
            'model_label' => 'Categoria',
            'plural_model_label' => 'Categoria',
        ],
        'fields' => [
            'name' => 'Nome',
            'slug' => 'Identificatore URL',
            'parent_category' => 'Elemento padre',
            'description' => 'Descrizione',
            'status' => 'Stato',
        ],
        'action' => [
            'toggle' => 'Cambia stato',
        ],
        'notification' => [
            'toggle_status' => [
                'success' => [
                    'title' => 'Stato della categoria aggiornato',
                    'body' => 'Le categorie selezionate sono state aggiornate correttamente.',
                ],
                'error' => [
                    'title' => 'Aggiornamento non riuscito',
                    'body' => 'Impossibile aggiornare gli stati delle categorie. Riprova.',
                ],
            ],
        ],
    ],
    'post' => [
        'navigation' => [
            'label' => 'Articolo',
            'model_label' => 'Articolo',
            'plural_model_label' => 'Articolo',
        ],
        'filters' => [
            'status' => 'Filtra per stato',
        ],
        'fields' => [
            'type' => 'Tipo',
            'title' => 'Titolo',
            'slug' => 'Identificatore URL',
            'exercpt' => 'Estratto',
            'content' => 'Contenuto',
            'feature_image' => 'Immagine',
            'is_featured' => 'In evidenza',
            'comment_status' => 'Stato dei commenti',
            'status' => 'Stato',
            'published_at' => 'Data di pubblicazione',
            'parent_id' => 'Elemento padre',
            'author' => 'Autore',
            'view_count' => 'Visualizzazioni',
            'categories' => 'Categorie',
        ],
    ],
    'page' => [
        'navigation' => [
            'label' => 'Pagina',
            'model_label' => 'Pagina',
            'plural_model_label' => 'Pagina',
        ],
    ],
    'menu' => [
        'navigation' => [
            'label' => 'Menu',
            'model_label' => 'Menu',
            'plural_model_label' => 'Menu',
        ],
        'fields' => [
            'name' => 'Nome del menu',
            'slug' => 'Identificatore URL',
        ],
    ],
    'setting' => [
        'company_name' => 'Nome dell’azienda',
        'logo' => 'Logo',
        'header_menu' => 'Menu dell’intestazione',
        'footer_menu' => 'Menu del piè di pagina',
    ],
];
