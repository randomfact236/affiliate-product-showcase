# Frontend - Vite + Tailwind Setup

## Development

```bash
cd frontend
npm install
npm run dev
```

## Build for Production

```bash
npm run build
```

## File Structure

```
frontend/
├── templates/           # PHP templates
│   └── products-showcase.php
├── src/                # Source files
│   ├── main.js         # Entry point
│   └── style.css       # Tailwind + custom styles
├── dist/               # Built files (generated)
├── package.json
├── vite.config.js
├── tailwind.config.js
└── postcss.config.js
```

## Shortcode Usage

Use `[aps_showcase]` in any WordPress page to display the tools directory.
