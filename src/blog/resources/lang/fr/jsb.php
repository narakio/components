<?php

return [
    'title' => [
        'blog_index' => 'Lister les articles',
        'blog_add' => 'Ajouter un article',
        'blog_category' => 'Gestion des catégories de blog',
    ],
    'breadcrumb' => [
        'admin-blog_posts-index' => 'Articles',
        'admin-blog_posts-add' => 'Créer',
        'admin-blog_posts-edit' => 'Éditer',
        'admin-blog_posts-category' => 'Catégories',
    ],
    'sidebar' => [
        'blog' => 'Blog',
    ],
    'db' => [
        'blog_post_title' => 'Titre de l\'article',
        'blog_posts' => 'Article|Articles',
    ],
    'db_raw' => [
        'blog_post_title' => 'blog_post_title',
    ],
    'db_raw_inv' => [
        'blog_post_title' => 'blog_post_title',
    ],
    'filters' => [
        'blog_posts_title' => 'title',
    ],
    'filter_labels' => [
        'blog_posts_title' => 'Title de l\'article :',
    ],
    'filters_inv' => [
        'blog_post_title' => 'titre',
    ],
    'constants' => [
        'BLOG_STATUS_DRAFT' => 'Brouillon',
        'BLOG_STATUS_REVIEW' => 'En cours de révision',
        'BLOG_STATUS_PUBLISHED' => 'Publié',
    ],
    'modal' => [
        'blog_post_delete' => [
            'h' => 'Confirmer la suppression de l\'article',
            't' => 'Souhaitez-vous supprimer l\'article "{name}" ?|Souhaitez-vous supprimer ces {number} articles ?',
        ],
    ],
    'message' => [
        'blog_post_delete_ok' => 'L\'article a été supprimé.|Les {number} articles ont été supprimés.',
    ],
    'blog' => [
        'info_sticky' => 'Cet article est épinglé. Cliquer pour supprimer l\'épinglage.',
        'info_not_sticky' => 'Cliquer pour épingler.',
        'add_post' => 'Ajouter un article',
        'add_root_button' => 'Ajouter une catégorie primaire',
        'add_source_button' => 'Ajouter une référence',
        'add_success' => 'L\'article a été créé.',
        'add_tag_pholder' => 'Presser Entrée pour ajouter un tag, cliquer pour l\'enlever.',
        'author' => 'Auteur',
        'blog_post_excerpt' => 'Résumé',
        'categories' => 'Catégories',
        'click_featured' => 'Cliquer sur une image pour en faire l\'image à la une pour cet article.',
        'delete_image' => 'Supprimer l\'avatar',
        'edit_image' => 'Modifier l\'image',
        'excerpt_label' => 'Ce sommaire de l\'article peut être affiché sur la page d\'accueil.',
        'filter_name' => 'Filtrer par nom',
        'filter_title' => 'Filtrer par titre',
        'image_uploaded' => 'Envoi complet',
        'media' => 'Media',
        'published_at' => 'Date de publication :',
        'save_success' => 'L\'article a été publié',
        'tab_available' => 'Média disponibles',
        'tab_upload' => 'Envoyer',
    ],
    'blog_categories' => [
        'add_child_node' => 'Ajouter une catégorie sous "{name}"',
        'edit_node' => 'Modifier "{name}"',
        'delete_node' => 'Supprimer "{name}"',
    ],
    'tables' => [
        'option_del_blog' => 'Supprimer l\'article',
    ],

];