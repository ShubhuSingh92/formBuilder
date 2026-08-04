# Form Builder UI Enhancement

## What changed

- Replaced the create and edit screens with one shared, responsive builder workspace.
- Added a searchable, categorized field library with click-to-add and native drag-to-add support.
- Added true drag-and-drop reordering with visible insertion indicators.
- Rendered realistic controls on the canvas instead of plain field-name cards.
- Added a contextual field inspector for labels, keys, placeholders, help text, defaults, options, and required state.
- Added duplicate, delete, move up/down, undo, and redo controls.
- Added a focused JSON editor dialog with format and apply actions.
- Added client-side schema checks before the existing Laravel validation endpoint is called.
- Added local draft recovery and autosave status.
- Fixed the missing AI-schema alert path that could previously throw a JavaScript error.
- Refreshed the application navigation, logo, surfaces, inputs, buttons, spacing, and responsive states.

## Main files

- `resources/views/forms/partials/builder-workspace.blade.php`
- `resources/js/form-builder.js`
- `resources/css/app.css`
- `resources/views/layouts/navigation.blade.php`
- `resources/views/layouts/app.blade.php`

## Run locally

```bash
npm install
npm run dev
php artisan serve
```

For a production asset build:

```bash
npm run build
```
