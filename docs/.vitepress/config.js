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
      { text: 'Documentation', link: '/5.4/installation', activeMatch: '^/(5.4|5.3)/' },
      {
        text: 'Versions', 
        items: [
          { text: 'v5.4.x (Current)', link: '/5.4/installation', activeMatch: '^/5.4/' },
          { text: 'v5.3.x', link: '/5.3/installation', activeMatch: '^/5.3/' },
        ]
      }
    ],

    // Version-specific Sidebar Configuration
    sidebar: {
      '/5.4/': [
        {
          text: 'Getting Started',
          collapsed: false,
          items: [
            { text: 'Installation', link: '/5.4/installation' },
            { text: 'Core Concepts', link: '/5.4/concepts' },
          ]
        },
        {
          text: 'Usage',
          collapsed: false,
          items: [
            { text: 'Command & Queries', link: '/5.4/commands' },
            { text: 'Event Bus', link: '/5.4/events' },
            { text: 'Routing & Actions', link: '/5.4/actions' },
            { text: 'Pipelines (Middleware)', link: '/5.4/pipelines' },
            { text: 'Testing Fakes', link: '/5.4/testing' },
          ]
        },
        {
          text: 'Reference',
          collapsed: false,
          items: [
            { text: 'Console Commands', link: '/5.4/console' },
            { text: 'Production & Performance', link: '/5.4/performance' },
          ]
        }
      ],
      '/5.3/': [
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
      ]
    },

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