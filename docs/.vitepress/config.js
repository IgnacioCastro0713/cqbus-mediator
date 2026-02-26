import { defineConfig } from 'vitepress'
import { withMermaid } from "vitepress-plugin-mermaid";

export default withMermaid(defineConfig({
  title: "CQBus Mediator",
  description: "A lightweight, zero-configuration Command/Query Bus for Laravel 11+.",
  base: '/cqbus-mediator/', // Necessary for GitHub Pages

  themeConfig: {
    // Logo and upper navigation
    nav: [
      { text: 'Home', link: '/' },
      { text: 'Documentation', link: '/5.3/installation' },
      {
        text: 'v5.3.x', 
        items: [
          { text: 'v5.3.x (Current)', link: '/5.3/installation' },
        ]
      }
    ],

    // Global Sidebar Configuration
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

    editLink: {
      pattern: 'https://github.com/IgnacioCastro0713/cqbus-mediator/edit/main/docs/:path',
      text: 'Edit this page on GitHub'
    },

    search: {
      provider: 'local'
    }
  }
}))