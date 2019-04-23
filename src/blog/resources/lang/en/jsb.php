<?php

return [
    'title' => [
        'blog_index' => 'Listing blog posts',
        'blog_add' => 'Managing a blog entry',
        'blog_category' => 'Managing blog categories'
    ],
    'breadcrumb' => [
        'admin-blog_posts-index' => 'Blog Posts',
        'admin-blog_posts-add' => 'Create',
        'admin-blog_posts-edit' => 'Edit',
        'admin-blog_posts-category' => 'Categories'
    ],
    'sidebar' => [
        'blog' => 'Blog'
    ],
    'db' => [
        'blog_post_title' => 'Post title',
        'blog_posts' => 'Blog Post|Blog Posts'
    ],
    'db_raw' => [
        'blog_post_title' => 'blog_post_title'
    ],
    'db_raw_inv' => [
        'blog_post_title' => 'blog_post_title'
    ],
    'filters' => [
        'blog_posts_title' => 'title'
    ],
    'filter_labels' => [
        'blog_posts_title' => 'Post title:'
    ],
    'filters_inv' => [
        'blog_post_title' => 'title'
    ],
    'constants' => [
        'BLOG_STATUS_DRAFT' => 'Draft',
        'BLOG_STATUS_REVIEW' => 'Under review',
        'BLOG_STATUS_PUBLISHED' => 'Published'
    ],
    'modal' => [
        'blog_post_delete' => [
            'h' => 'Confirm blog deletion',
            't' => 'Do you really want to delete blog post "{name}"?|Do you really want to delete those {number} blog posts?'
        ],
    ],
    'message' => [
        'blog_post_delete_ok' => 'The blog post was deleted.|The {number} blog posts were deleted.'
    ],
    'blog' => [
        'add_post' => 'Add post',
        'add_root_button' => 'Add root category',
        'add_source_button' => 'Add source',
        'add_success' => 'The blog post was created.',
        'add_tag_pholder' => 'Type enter to add tag, click to remove',
        'author' => 'Author',
        'blog_post_excerpt' => 'Excerpt',
        'categories' => 'Categories',
        'click_featured' => 'Click on an image to make it the featured image for this post.',
        'delete_image' => 'Delete avatar',
        'edit_image' => 'Edit image',
        'excerpt_label' => 'This user-defined summary of the post can be displayed on the front page.',
        'filter_name' => 'Filter by name',
        'filter_title' => 'Filter by title',
        'image_uploaded' => 'Upload is complete.',
        'media' => 'Media',
        'published_at' => 'Publishing date:',
        'save_success' => 'The blog post was updated.',
        'tab_available' => 'Available media',
        'tab_upload' => 'Upload'
    ],
    'blog_categories' => [
        'add_child_node' => 'Add a child element to "{name}"',
        'edit_node' => 'Edit node "{name}"',
        'delete_node' => 'Delete node "{name}"'
    ],
    'tables' => [
        'option_del_blog' => 'Delete blog post'
    ]
];
