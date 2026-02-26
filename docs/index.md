---
# Esta es tu Landing Page. Se renderiza con un diseño espectacular por defecto en VitePress.
layout: home

hero:
  name: "CQBus Mediator"
  text: "for Laravel"
  tagline: "A lightweight, zero-configuration Command/Query Bus to supercharge your architecture."
  actions:
    - theme: brand
      text: Get Started
      link: /5.3/installation
    - theme: alt
      text: View on GitHub
      link: https://github.com/IgnacioCastro0713/cqbus-mediator

features:
  - title: ⚡ Zero Config
    details: Automatically discovers Handlers and Events using modern PHP Attributes (#[RequestHandler], #[EventHandler]).
  - title: 📢 Dual Pattern Support
    details: Seamlessly handle both Command/Query (one-to-one) and Event Bus (one-to-many) patterns in the same package.
  - title: 🎮 Attribute Routing
    details: Manage routes, prefixes, and middleware directly in your Action classes—no more bloated route files.
---
