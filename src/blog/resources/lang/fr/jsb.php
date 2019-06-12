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
        'blog_post_delete_ok' => '__The blog post was deleted.|The {number} blog posts were deleted.',
    ],
    'blog' => [
        'info_sticky' => 'This blog post is pinned. Click to unpin.',
        'info_not_sticky' => 'Click to pin this post.',
        'add_post' => '__Add post',
        'add_root_button' => '__Add root category',
        'add_source_button' => '__Add source',
        'add_success' => '__The blog post was created.',
        'add_tag_pholder' => '__Type enter to add tag, click to remove',
        'author' => '__Author',
        'blog_post_excerpt' => '__Excerpt',
        'categories' => '__Categories',
        'click_featured' => '__Click on an image to make it the featured image for this post.',
        'delete_image' => '__Delete avatar',
        'edit_image' => '__Edit image',
        'excerpt_label' => '__This user-defined summary of the post can be displayed on the front page.',
        'filter_name' => '__Filter by name',
        'filter_title' => '__Filter by title',
        'image_uploaded' => '__Upload is complete.',
        'media' => '__Media',
        'published_at' => '__Publishing date:',
        'save_success' => '__The blog post was updated.',
        'tab_available' => '__Available media',
        'tab_upload' => '__Upload',
    ],
    'blog_categories' => [
        'add_child_node' => '__Add a child element to "{name}"',
        'edit_node' => '__Edit node "{name}"',
        'delete_node' => '__Delete node "{name}"',
    ],
    'tables' => [
        'option_del_blog' => '__Delete blog post',
    ],

];