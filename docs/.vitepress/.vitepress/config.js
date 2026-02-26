import { defineConfig } from 'vitepress'

export default defineConfig({
  title: "CQBus Mediator",
  description: "A lightweight, zero-configuration Command/Query Bus for Laravel.",
  srcDir: 'src',
  base: '/cqbus-mediator/', // Necesario para GitHub Pages (nombre de tu repo)

  themeConfig: {
    // Logo y enlaces en la barra superior (Navbar)
    nav: [
      { text: 'Home', link: '/' },
      { text: 'Documentation', link: '/5.3/installation' },
      {
        text: 'v5.3.x', // Menú desplegable para versiones
        items: [
          { text: 'v5.3.x (Current)', link: '/5.3/installation' },
        ]
      }
    ],

    // Barra lateral (Sidebar)
    sidebar: [
      {
        text: 'Getting Started',
        collapsed: false,
        items: [
          { text: 'Installation', link: '/5.3/installation' },
          { text: 'Core Concepts', link: '/5.3/concepts' },
        ]
      },
      {
        text: 'Usage',
        collapsed: false,
        items: [
          { text: 'Command & Queries', link: '/5.3/commands' },
          { text: 'Event Bus', link: '/5.3/events' },
          { text: 'Routing & Actions', link: '/5.3/actions' },
          { text: 'Pipelines (Middleware)', link: '/5.3/pipelines' },
        ]
      },
      {
        text: 'Reference',
        collapsed: false,
        items: [
          { text: 'Console Commands', link: '/5.3/console' },
          { text: 'Production & Performance', link: '/5.3/performance' },
        ]
      }
    ],

    socialLinks: [
      { icon: 'github', link: 'https://github.com/IgnacioCastro0713/cqbus-mediator' }
    ],

    // Botón de edición en GitHub
    editLink: {
      pattern: 'https://github.com/IgnacioCastro0713/cqbus-mediator/edit/main/docs/src/:path',
      text: 'Edit this page on GitHub'
    },

    // Búsqueda integrada
    search: {
      provider: 'local'
    }
  }
})
