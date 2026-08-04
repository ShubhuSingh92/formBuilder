<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900">AI form generator</h2>
                <p class="mt-2 max-w-2xl text-sm text-slate-500">Describe the form you need and AI will draft the schema, fields, placeholders, and validation for you.</p>
            </div>
            <a href="{{ route('forms.create') }}" class="inline-flex items-center rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">Build manually instead</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-[1.4fr_0.9fr]">
                <div class="rounded-3xl border border-slate-200 bg-slate-950 p-8 shadow-[0_25px_50px_-25px_rgba(15,23,42,0.4)] text-white">
                    <div class="flex flex-col gap-4">
                        <div>
                            <p class="text-sm uppercase tracking-[0.3em] text-indigo-300">Smart generation</p>
                            <h3 class="mt-3 text-2xl font-semibold">Create your form with a natural prompt</h3>
                            <p class="mt-3 max-w-2xl text-sm text-slate-300">Use plain English and the AI assistant will deliver a form schema you can customize or export into the builder.</p>
                        </div>

                        <label class="block text-sm font-medium text-slate-200">Try one of these prompts</label>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <button type="button" data-example="Create a job application form with name, email, resume upload, experience and availability." class="rounded-2xl border border-slate-800 bg-slate-900 px-4 py-3 text-left text-sm text-slate-200 hover:border-indigo-400 hover:bg-slate-800">Job application</button>
                            <button type="button" data-example="Build an event registration form with attendee name, email, session choices, dietary preferences, and comments." class="rounded-2xl border border-slate-800 bg-slate-900 px-4 py-3 text-left text-sm text-slate-200 hover:border-indigo-400 hover:bg-slate-800">Event registration</button>
                            <button type="button" data-example="Create a customer feedback form with rating, service category, comments, and follow-up permission." class="rounded-2xl border border-slate-800 bg-slate-900 px-4 py-3 text-left text-sm text-slate-200 hover:border-indigo-400 hover:bg-slate-800">Customer feedback</button>
                            <button type="button" data-example="Make an internship application form with contact details, education history, skills, and portfolio link." class="rounded-2xl border border-slate-800 bg-slate-900 px-4 py-3 text-left text-sm text-slate-200 hover:border-indigo-400 hover:bg-slate-800">Internship form</button>
                        </div>

                        <div class="mt-6">
                            <label class="block text-sm font-medium text-slate-200">Describe the form</label>
                            <textarea id="ai-prompt" rows="7" class="mt-2 block w-full rounded-3xl border border-slate-800 bg-slate-900 px-4 py-3 text-sm text-slate-100 shadow-inner focus:border-indigo-400 focus:outline-none" placeholder="e.g. 'Membership signup with email, phone number, plan selection, and payment preference'"></textarea>
                        </div>

                        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center">
                            <button id="generate-ai-form" class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-indigo-500 to-violet-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/20 hover:from-indigo-400 hover:to-violet-500">Generate form</button>
                            <span class="text-sm text-slate-300">AI will generate a valid schema using the configured provider or a safe fallback.</span>
                        </div>

                        <div id="ai-status" class="mt-4 rounded-3xl border border-slate-800 bg-slate-900 p-4 text-sm text-slate-300"></div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900">How it works</h3>
                    <ul class="mt-4 space-y-4 text-sm text-slate-600">
                        <li class="rounded-2xl border border-slate-200 bg-slate-50 p-4">• Converts your prompt into a form schema with fields, labels, help text, and validation.</li>
                        <li class="rounded-2xl border border-slate-200 bg-slate-50 p-4">• Supports lists, dropdowns, checkboxes, file upload fields, and rating inputs.</li>
                        <li class="rounded-2xl border border-slate-200 bg-slate-50 p-4">• Sends a clean JSON schema back so you can copy or use it in the builder instantly.</li>
                    </ul>
                </div>
            </div>

            <div class="mt-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Preview schema</h3>
                        <p class="mt-1 text-sm text-slate-500">Review the generated schema before you move it into the form builder.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button id="save-ai-schema" class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Copy JSON</button>
                        <button id="use-ai-schema" type="button" class="rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Use in builder</button>
                    </div>
                </div>
                <textarea id="ai-schema-output" rows="18" class="mt-4 block w-full rounded-3xl border border-slate-200 bg-slate-950 px-4 py-4 font-mono text-sm text-slate-100 shadow-inner" readonly></textarea>
            </div>
        </div>
    </div>

    <script>
        const promptField = document.getElementById('ai-prompt');
        const outputField = document.getElementById('ai-schema-output');
        const statusField = document.getElementById('ai-status');
        const examples = document.querySelectorAll('[data-example]');

        examples.forEach(button => {
            button.addEventListener('click', () => {
                promptField.value = button.getAttribute('data-example');
                promptField.focus();
            });
        });

        document.getElementById('generate-ai-form').addEventListener('click', async () => {
            const prompt = promptField.value.trim();
            if (!prompt) {
                statusField.textContent = 'Please enter a prompt first.';
                return;
            }

            statusField.textContent = 'Generating schema...';
            outputField.value = '';

            try {
                const response = await fetch('{{ route('ai.generate') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ prompt, mode: 'create' })
                });

                const payload = await response.json();
                if (payload.ok) {
                    outputField.value = JSON.stringify(payload.schema, null, 2);
                    statusField.textContent = 'Schema generated successfully.';
                } else {
                    statusField.textContent = payload.message || 'Generation failed. Try a different prompt.';
                }
            } catch (error) {
                console.error(error);
                statusField.textContent = 'Network error when generating schema. Please try again.';
            }
        });

        document.getElementById('save-ai-schema').addEventListener('click', () => {
            const schema = outputField.value;
            if (!schema) {
                statusField.textContent = 'Generate a schema first before copying.';
                return;
            }
            navigator.clipboard.writeText(schema);
            statusField.textContent = 'Schema copied to clipboard.';
        });

        document.getElementById('use-ai-schema').addEventListener('click', () => {
            const schema = outputField.value;
            if (!schema) {
                statusField.textContent = 'Generate a schema first before using it in the builder.';
                return;
            }
            try {
                const parsed = JSON.parse(schema);
                localStorage.setItem('ai_generated_schema', JSON.stringify(parsed));
                window.location.href = '{{ route('forms.create') }}';
            } catch (error) {
                statusField.textContent = 'Invalid schema returned. Please regenerate.';
            }
        });
    </script>
</x-app-layout>
