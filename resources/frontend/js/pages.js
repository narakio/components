export default {
  // Login
  'd56b699830': {
    components: {
      frontend: [
        'InlineForm',
        'LoginOAuth'
      ],
      backend: [
        'SubmitButton'
      ]
    }
  },
  // Register
  '9de4a97425': {
    page: 'register.js',
    components: {
      frontend: [
        'PasswordStrength',
        'InlineForm',
        'InputValidator'
      ],
      backend: [
        'SubmitButton'
      ]
    }
  },
  // Settings: profile
  '7d97481b1f': {
    components: {
      frontend: [
        'InlineForm'
      ],
      backend: [
        'SubmitButton',
        'AvatarUploader'
      ]
    }
  },
  // Settings: Account
  'e268443e43': {
    page: [
      'pages/account.js'
    ],
    components: {
      frontend: [
        'InlineForm',
        'PasswordStrength',
        'InputValidator'
      ],
      backend: [
        'SubmitButton'
      ]
    }
  },
  // Settings: Notifications
  'f37bd2f666': {
    components: {
      frontend: [
        'InlineForm',
        'InputValidator'
      ],
      backend: [
        'SubmitButton'
      ]
    }
  },
  // Contact
  '2f8a6bf31f': {
    components: {
      frontend: [
        'InlineForm'
      ],
      backend: [
        'SubmitButton'
      ]
    }
  },
  // Blog pages
  '126ac9f614': {
    page: [
      'plugins/vendor/twitterLoader.js',
      'plugins/vendor/prettify.js',
      'pages/blog.js'
    ],
    components: {
      frontend: [
        'Comments'
      ]
    }
  },
  // Search
  '06a943c59f': {
    components: {
      frontend: ['FullPageSearch']
    }
  }
}
