export default [
  {
    type: 'item',
    isHeader: true,
    name: 'sidebar.main_nav'
  },
  {
    type: 'tree',
    icon: 'tachometer',
    name: 'sidebar.dashboard',
    router: {
      name: 'admin.dashboard'
    }
  },
  {
    type: 'tree',
    icon: 'user',
    name: 'sidebar.users',
    items: [
      {
        type: 'item',
        icon: 'list-ul',
        name: 'sidebar.list',
        router: {
          name: 'admin.users.index'
        }
      },
      {
        type: 'item',
        icon: 'plus',
        name: 'sidebar.add',
        router: {
          name: 'admin.users.add'
        }
      }
    ]
  },
  {
    type: 'tree',
    icon: 'users',
    name: 'sidebar.groups',
    items: [
      {
        type: 'item',
        icon: 'list-ul',
        name: 'sidebar.list',
        router: {
          name: 'admin.groups.index'
        }
      },
      {
        type: 'item',
        icon: 'plus',
        name: 'sidebar.add',
        router: {
          name: 'admin.groups.add'
        }
      }
    ]
  },
  {
    type: 'tree',
    icon: 'newspaper-o',
    name: 'sidebar.blog',
    items: [
      {
        type: 'item',
        icon: 'list-ul',
        name: 'sidebar.list',
        router: {
          name: 'admin.blog_posts.index'
        }
      },
      {
        type: 'item',
        icon: 'plus',
        name: 'sidebar.add',
        router: {
          name: 'admin.blog_posts.add'
        }
      },
      {
        type: 'item',
        icon: 'sitemap',
        name: 'sidebar.category',
        router: {
          name: 'admin.blog_posts.category'
        }
      }
    ]
  },
  {
    type: 'tree',
    icon: 'image',
    name: 'sidebar.media',
    router: {
      name: 'admin.media.index'
    }
  }
]
