<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-800">Create a form</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <form method="POST" action="{{ route('forms.store') }}" class="space-y-6">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Title</label>
                        <input type="text" name="title" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Description</label>
                        <textarea name="description" rows="3" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm"></textarea>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-slate-700">Schema JSON</label>
                            <button type="button" id="validate-schema" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700">Validate schema</button>
                        </div>
                        <textarea name="schema" id="schema-editor" rows="20" class="mt-3 block w-full rounded-md border-slate-300 font-mono text-sm shadow-sm" required>{{ json_encode([["type" => "text", "key" => "full_name", "label" => "Full name", "required" => true, "placeholder" => "Enter your full name", "help_text" => "We need your full name", "default" => "", "options" => [], "validations" => ["required"]], ["type" => "email", "key" => "email", "label" => "Email", "required" => true, "placeholder" => "name@example.com", "help_text" => "We will contact you here", "default" => "", "options" => [], "validations" => ["required", "email"]]], JSON_PRETTY_PRINT) }}</textarea>
                        <div id="schema-feedback" class="mt-3 text-sm text-slate-600">Use the editor below to adjust fields manually. Validation happens before saving.</div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_public" value="1" checked>
                        <label class="text-sm text-slate-700">Make the form public</label>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Save form</button>
                    </div>
                </form>

                <script>
                    document.getElementById('validate-schema').addEventListener('click', async () => {
                        const schema = document.getElementById('schema-editor').value;
                        const feedback = document.getElementById('schema-feedback');
                        try {
                            const payload = JSON.parse(schema);
                            const response = await fetch('{{ route('schema.validate') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ schema: payload })
                            });
                            const result = await response.json();
                            if (result.valid) {
                                feedback.textContent = 'Schema looks good. You can save it.';
                                feedback.className = 'mt-3 text-sm text-emerald-600';
                            } else {
                                feedback.textContent = result.errors.join(', ');
                                feedback.className = 'mt-3 text-sm text-rose-600';
                            }
                        } catch (error) {
                            feedback.textContent = 'The schema must be valid JSON.';
                            feedback.className = 'mt-3 text-sm text-rose-600';
                        }
                    });
                </script>
            </div>
        </div>
    </div>
</x-app-layout>
