# Two-Week Product and Architecture Decision Plan

## Decision summary

With two additional weeks, the next goal should not be to add a mountain of flashy field types. The goal should be to turn the current assignment MVP into a reliable, demonstrable, production-shaped form product.

The recommended priority is:

1. Harden form and submission correctness.
2. Complete the owner workflow from create → publish → share → review → export.
3. Make AI generation predictable and observable.
4. Connect document imports directly to the builder.
5. Add tests, accessibility checks, and deployment readiness.

Assumption: one developer, ten working days, with design polish handled inside the same implementation effort.

---

## Product outcome after two weeks

At the end of the sprint, a form owner should be able to:

- Create, edit, duplicate, publish, close, and delete a form.
- Share a public URL with clear publication status.
- Collect validated responses with anti-spam protection.
- Open an individual submission page.
- Search, filter, and export submissions.
- Download attachments safely.
- Generate a schema with AI and receive useful validation errors when generation fails.
- Import a Word or Excel file and continue editing the result in the visual builder.
- Trust that the most important flows are covered by automated tests.

The application should also be deployable with documented environment variables, storage behavior, queue behavior, and health checks.

---

## Guiding decisions

### Decision 1 — Finish the core owner journey before adding advanced fields

**Decision:** Prioritize lifecycle, response review, export, and reliability over conditional logic, payments, or a large template marketplace.

**Why:** The current product already creates forms. The largest usability gap is everything after creation. A polished builder without a complete management workflow is a nice-looking cul-de-sac.

**Success measure:** A reviewer can complete the entire workflow without editing JSON, opening the database, or manually copying response data.

---

### Decision 2 — Keep the schema-driven architecture

**Decision:** Continue using the JSON field array as the canonical form definition.

**Why:** The current builder, respondent renderer, validation generator, AI output, and import output already converge on this model. Replacing it would consume most of the two weeks without improving the assignment outcome.

**Required hardening:**

- Add a server-side supported-type whitelist.
- Enforce unique keys on the backend.
- Validate choice-field options.
- Validate file settings.
- Add a maximum number of fields.
- Add an explicit schema version in form settings, initially `1`.

Suggested form setting:

```json
{
  "schema_version": 1,
  "allow_csv_export": true,
  "allow_ai_import": true,
  "accepting_submissions": true
}
```

Do not change the top-level schema from an array during this sprint. That would create avoidable compatibility work.

---

### Decision 3 — Introduce Policies and Form Requests

**Decision:** Replace repeated controller-level `abort_unless` ownership checks with Laravel Policies, and move request validation into dedicated Form Request classes.

**Why:** Authorization logic is currently correct in the important paths, but it is scattered. New actions such as duplicate, close, delete, export, and single-response view will multiply that repetition.

Planned classes:

```text
app/Policies/FormPolicy.php
app/Policies/FormSubmissionPolicy.php
app/Policies/ImportJobPolicy.php

app/Http/Requests/StoreFormRequest.php
app/Http/Requests/UpdateFormRequest.php
app/Http/Requests/SubmitFormRequest.php
app/Http/Requests/GenerateFormSchemaRequest.php
```

Policy abilities:

```text
view
update
delete
duplicate
viewSubmissions
exportSubmissions
downloadSubmissionFile
```

---

### Decision 4 — Use a clear form lifecycle

**Decision:** Use the existing `status` column as the canonical lifecycle while maintaining `is_public` for backward compatibility during this sprint.

Statuses:

| Status | Public page | Accept submissions | Owner behavior |
|---|---:|---:|---|
| `draft` | No | No | Editable and previewable by owner |
| `published` | Yes | Yes | Shareable and collecting responses |
| `closed` | Optional read-only message | No | Existing data remains available |

Implementation approach:

- Add model methods such as `isPublished()` and `acceptsSubmissions()`.
- Keep `is_public` synchronized to avoid breaking existing records and views.
- Add publish, unpublish, and close actions.
- Reject submission POST requests when a form is not accepting responses.
- Display a friendly closed-form page rather than a generic 404.

Do not remove `is_public` in this sprint. A later migration can consolidate the columns after all callers have moved to the lifecycle methods.

---

### Decision 5 — Make AI output a validated draft, never trusted data

**Decision:** Treat provider output as untrusted input and pass it through a strict deterministic pipeline.

Target pipeline:

```text
User prompt
  ↓
Prompt length and mode validation
  ↓
Provider adapter
  ↓
Structured JSON response when supported
  ↓
Remove Markdown fences if present
  ↓
Decode JSON
  ↓
Strict schema normalization
  ↓
Whitelist and unique-key validation
  ↓
One repair attempt for recoverable output
  ↓
Safe local fallback
  ↓
Return warnings plus usable draft
```

Move provider settings from direct `env()` calls to `config/services.php`:

```php
'ai' => [
    'provider' => env('AI_PROVIDER', 'openrouter'),
    'openrouter_key' => env('OPENROUTER_API_KEY'),
    'groq_key' => env('GROQ_API_KEY'),
    'timeout' => env('AI_TIMEOUT', 30),
],
```

Create a provider boundary:

```text
app/Contracts/FormAiProvider.php
app/Services/Ai/OpenRouterFormAiProvider.php
app/Services/Ai/GroqFormAiProvider.php
app/Services/Ai/FallbackFormAiProvider.php
```

Log only operational metadata:

- Provider
- Model
- Duration
- Success/failure category
- Number of generated fields
- Repair/fallback usage

Do not log full prompts by default because prompts may contain names, company data, or internal requirements.

---

### Decision 6 — Complete exports with streaming responses

**Decision:** Add CSV export first. Do not build PDF export during this sprint.

**Why CSV first:** It is broadly useful, lightweight, testable, and does not introduce document-layout complexity.

Export behavior:

- Owner-only.
- Export all submissions for a form.
- Optional date range.
- One column per schema field.
- Checkbox arrays joined with a delimiter.
- File fields exported as the original filename and an authorized application URL, not a private filesystem path.
- Stream rows to avoid loading every submission into memory.
- Prefix cells beginning with `=`, `+`, `-`, or `@` to reduce spreadsheet formula injection risk.

Acceptance criterion: a form with 10,000 simple responses can begin downloading without building one massive in-memory array.

---

### Decision 7 — Keep uploads private and add cleanup

**Decision:** Preserve private storage and owner-authorized downloads.

Add during this sprint:

- Server-side MIME and extension rules derived from a safe whitelist.
- Configurable global maximum upload size.
- File cleanup when a submission or form is deleted.
- Tests for cross-owner download denial.
- A clear attachment indicator in the global submission dashboard.

Recommended safe initial types:

```text
pdf
doc
docx
xls
xlsx
png
jpg
jpeg
```

Do not implement arbitrary executable or archive upload support.

Malware scanning is valuable but may require external infrastructure. Add an integration seam and document it; implement actual scanning only if a scanner is already available.

---

### Decision 8 — Add lightweight anti-spam protection

**Decision:** Keep rate limiting and add a honeypot plus submission timing check.

Initial protection:

- Existing 30-per-minute throttle.
- Hidden honeypot field.
- Reject forms submitted unrealistically fast, while allowing accessibility tools and password managers reasonable time.
- Optional CAPTCHA adapter behind configuration, not hard-coded into the product.

Do not require CAPTCHA by default; it adds friction and external-provider setup to the assignment demo.

---

## Ten-day implementation plan

## Week 1 — Correctness and completed workflows

### Day 1 — Baseline, tests, and authorization structure

Tasks:

- Run and stabilize the complete test suite.
- Add factories for forms and submissions.
- Add `FormPolicy`, `FormSubmissionPolicy`, and `ImportJobPolicy`.
- Add Form Request classes.
- Add tests proving cross-owner access is denied.
- Document the current route and data contracts.

Deliverables:

- Centralized authorization.
- Reusable request validation.
- Green baseline tests.

Acceptance criteria:

- Another user cannot edit, export, delete, or download files from a form they do not own.
- Controllers no longer repeat ownership checks for new actions.

### Day 2 — Form lifecycle and management actions

Tasks:

- Implement draft, published, and closed states.
- Add publish/unpublish/close controls.
- Add duplicate form action with a fresh slug.
- Add delete form action with confirmation.
- Add friendly public closed-form page.
- Add status badges consistently across dashboard and forms list.

Deliverables:

- Complete form lifecycle.
- Duplicate and delete actions.

Acceptance criteria:

- A draft cannot receive public submissions.
- A published form can be shared and submitted.
- A closed form preserves old responses but rejects new ones.
- A duplicated form copies schema/settings but not submissions.

### Day 3 — Schema hardening

Tasks:

- Add backend field-type whitelist.
- Enforce unique snake_case keys.
- Validate choice options and maximum option count.
- Validate file settings and accepted types.
- Add maximum field count, for example 100.
- Add schema version to settings.
- Return field-indexed validation messages to the builder.

Deliverables:

- One schema validator used by form save, AI generation, JSON editor, and imports.

Acceptance criteria:

- Unknown types are rejected or deterministically mapped.
- Duplicate keys cannot be saved through a crafted HTTP request.
- Invalid AI/import output cannot bypass validation.

### Day 4 — Submission details and safe file handling

Tasks:

- Add a dedicated single-submission route and page.
- Show field label, value, type, and attachment metadata.
- Add previous/next response navigation within a form.
- Add strict server MIME rules.
- Add attachment cleanup services.
- Add tests for upload, download, ownership, and cleanup.

Deliverables:

- Clean response detail experience.
- Safer upload flow.

Acceptance criteria:

- Global dashboard links to a dedicated response page.
- Cross-owner file downloads return 403 or 404.
- Deleted records do not leave orphaned files.

### Day 5 — CSV export and dashboard completion

Tasks:

- Add per-form CSV export.
- Add optional date filters.
- Add search by submission ID and common text values where practical.
- Add export button to form submissions and global dashboard.
- Add response-count trend for the last 7 or 14 days.
- Add empty, loading, and error states.

Deliverables:

- Useful data extraction.
- More informative dashboard.

Acceptance criteria:

- Export column order follows the form schema.
- Arrays and files serialize predictably.
- Spreadsheet formula injection is mitigated.
- Owner cannot export another owner's data.

---

## Week 2 — AI reliability, imports, quality, and release

### Day 6 — AI provider abstraction and structured output

Tasks:

- Move AI settings into configuration.
- Add provider interface and adapters.
- Request structured JSON output where supported.
- Strip fenced JSON safely.
- Add timeout and provider error categories.
- Add one repair attempt for malformed but recoverable output.
- Add `Http::fake()` tests.

Deliverables:

- Provider-independent AI service.
- Predictable failure behavior.

Acceptance criteria:

- Provider timeout returns a usable fallback and warning.
- Markdown-wrapped JSON is handled.
- Invalid field types cannot reach the builder.

### Day 7 — Prompt quality and AI edit mode

Tasks:

- Improve system prompt with schema examples and explicit invariants.
- Add field-count guidance.
- Add prompt presets by form category.
- Expose create versus edit mode in the UI.
- Show warnings when fallback or repair was used.
- Add generated-field review summary before opening builder.

Deliverables:

- Better generation quality and transparency.

Acceptance criteria:

- Users can tell whether remote AI or fallback produced the draft.
- Edit prompts preserve unaffected fields where possible.
- Generated keys remain stable and unique.

### Day 8 — Import-to-builder integration and queueing

Tasks:

- Add **Use in builder** on import results.
- Normalize imported schema through the same validator as AI/manual forms.
- Improve Excel mapping with an optional header-based format:
  - label
  - type
  - required
  - options
  - placeholder
  - help_text
- Improve DOCX extraction for paragraphs and tables.
- Dispatch import parsing to a queued job when files are large.
- Add job status polling or Livewire refresh.

Deliverables:

- End-to-end import workflow.

Acceptance criteria:

- Imported fields open directly in the visual builder.
- Invalid rows produce warnings rather than breaking the full import.
- Large imports do not block the request until timeout.

### Day 9 — Accessibility, responsive QA, and UX polish

Tasks:

- Keyboard-test builder controls.
- Add non-drag alternatives for all reorder actions.
- Verify focus order and visible focus states.
- Add accessible status announcements for copy, save, validation, and AI generation.
- Test public forms at 320 px, 768 px, desktop, and high zoom.
- Verify color contrast.
- Add reduced-motion behavior where needed.
- Test long labels, many options, and validation errors.

Deliverables:

- More defensible accessibility and mobile quality.

Acceptance criteria:

- A form can be built without drag-and-drop.
- Every public field has an accessible label and error association.
- No horizontal overflow on supported mobile widths.

### Day 10 — Release hardening and demonstration package

Tasks:

- Run PHP tests, Pint, JavaScript syntax checks, and production build.
- Add deployment checklist and `.env` reference.
- Add seed/demo data command.
- Add one-click demo scenarios:
  - job application
  - customer feedback
  - event registration
- Verify production cache commands.
- Verify upload permissions and cleanup.
- Record known limitations.
- Prepare screenshots or a short walkthrough.

Deliverables:

- Release candidate.
- Reproducible evaluator setup.

Acceptance criteria:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm ci
npm run build
php artisan test
```

All commands complete successfully in a clean environment with the documented PHP extensions.

---

## Prioritized backlog

### P0 — Must complete

- Policies and authorization tests.
- Form lifecycle: draft, published, closed.
- Duplicate and delete form actions.
- Strict server schema validation.
- Dedicated submission detail page.
- CSV export.
- File validation and cleanup.
- AI parsing hardening and provider abstraction.
- Import result to builder.
- Critical feature tests.

### P1 — Complete when P0 is stable

- Seven- or fourteen-day submission trend.
- Search and richer filters.
- Honeypot and timing-based anti-spam.
- Queue-based imports.
- AI edit mode UX.
- Accessibility and responsive audit.
- Demo seed data.

### P2 — Stretch goals

- Email notification on submission.
- Configurable success message and redirect URL.
- Scheduled form closing date.
- Response limit per form.
- Basic webhook delivery.
- Reusable form templates.

---

## Explicitly out of scope for these two weeks

Do not attempt the following unless all P0 work is finished and tested:

- Payment collection.
- Complex conditional branching.
- Multi-page logic with arbitrary jumps.
- Real-time multi-user collaboration.
- Full organization and role-based tenancy.
- White-label domains.
- Marketplace or public template ecosystem.
- Native mobile applications.
- PDF report designer.
- Full analytics warehouse.
- Dozens of new field types.

These features are attractive but dangerous in a short sprint because they expand the data model, authorization model, testing surface, and UI states simultaneously.

---

## Proposed route additions

```text
POST   /forms/{form}/duplicate
PATCH  /forms/{form}/publish
PATCH  /forms/{form}/close
DELETE /forms/{form}

GET    /forms/{form}/submissions/{submission}
DELETE /forms/{form}/submissions/{submission}
GET    /forms/{form}/submissions/export.csv
```

Suggested route names:

```text
forms.duplicate
forms.publish
forms.close
forms.destroy
forms.submissions.show
forms.submissions.destroy
forms.submissions.export
```

---

## Proposed service additions

```text
app/Services/FormLifecycleService.php
app/Services/FormSchemaValidator.php
app/Services/SubmissionExportService.php
app/Services/SubmissionFileService.php
app/Services/SubmissionCleanupService.php
app/Services/AntiSpamService.php

app/Contracts/FormAiProvider.php
app/Services/Ai/OpenRouterFormAiProvider.php
app/Services/Ai/GroqFormAiProvider.php
app/Services/Ai/FallbackFormAiProvider.php

app/Jobs/ProcessImportJob.php
```

Keep controllers thin: authorize, validate, call a service, and return a response.

---

## Proposed test matrix

| Area | Required tests |
|---|---|
| Form lifecycle | Draft, publish, close, duplicate, delete, owner boundary |
| Schema | Type whitelist, duplicate keys, invalid options, max fields, file settings |
| Public form | Public access, private denial, closed state, required fields |
| Choices | Invalid dropdown/radio/checkbox values rejected |
| Files | Upload success, size failure, MIME failure, owner download, cleanup |
| Submissions | Global scoping, detail view, delete, pagination, filters |
| Export | Column order, arrays, files, date range, formula injection |
| AI | Provider success, fenced JSON, malformed JSON, timeout, repair, fallback |
| Imports | DOCX, XLSX, invalid type, ownership, builder handoff, queued failure |
| UI | Clipboard feedback, autosave restore, drag reorder, keyboard reorder |

Minimum release rule: no P0 feature ships without at least one success test and one authorization or failure test.

---

## Risks and mitigations

### Risk — Schema changes break older forms

Mitigation:

- Add `schema_version`.
- Normalize on read and write.
- Keep aliases such as `select`, `upload`, and `file_upload` during transition.
- Add fixture tests for existing schemas.

### Risk — AI consumes time without improving reliability

Mitigation:

- Cap AI work to two days.
- Keep deterministic fallback.
- Use provider adapters and fake HTTP tests.
- Do not build a conversational agent during this sprint.

### Risk — Export leaks another user's data

Mitigation:

- Authorize at form level.
- Build export query through the authorized form relationship.
- Add cross-owner feature tests.

### Risk — File cleanup deletes the wrong path

Mitigation:

- Require the expected `form-submissions/{form_id}/` prefix.
- Use the configured filesystem disk abstraction.
- Test path-prefix failures.
- Delete only metadata-confirmed files.

### Risk — Queueing makes local setup harder

Mitigation:

- Keep synchronous mode available through configuration.
- Document the worker command.
- Use database queue, which is already configured and migrated.

### Risk — Dashboard scope balloons

Mitigation:

- Limit analytics to counts and one simple time-series chart.
- Do not add field-level BI or custom report building in this sprint.

---

## Definition of done

A feature is done only when:

- Authorization is explicit and tested.
- Request validation is server-side.
- Empty, loading, success, and error states exist.
- Desktop and mobile layouts are checked.
- Keyboard interaction is possible where relevant.
- Tests pass.
- The production asset build passes.
- Documentation is updated.
- No private filesystem path is exposed to respondents.
- No feature is represented in the UI before its backend behavior exists.

---

## Final recommendation

The best two-week version of this project is not the one with the most buttons. It is the one where every visible button works, every important action is authorized, AI failure is graceful, attachments stay private, and an evaluator can understand the architecture in ten minutes.

Build the boring reliability pieces first. They are only boring until they fail in production—then they become the entire plot.
