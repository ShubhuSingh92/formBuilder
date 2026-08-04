<?php

namespace App\Services;

class FormSchemaService
{
    public function validateSchema(array $schema): array
    {
        $errors = [];

        if (!is_array($schema) || empty($schema)) {
            return ['valid' => false, 'errors' => ['Schema must contain at least one field.']];
        }

        foreach ($schema as $index => $field) {
            if (!is_array($field)) {
                $errors[] = "Field {$index} is invalid.";
                continue;
            }

            if (empty($field['type'] ?? null)) {
                $errors[] = "Field {$index} is missing a type.";
            }

            if (empty($field['key'] ?? null)) {
                $errors[] = "Field {$index} is missing a key.";
            }

            if (empty($field['label'] ?? null)) {
                $errors[] = "Field {$index} is missing a label.";
            }
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    public function normalizeSchema(array $schema): array
    {
        return array_values(array_filter(array_map(function ($field) {
            if (!is_array($field)) {
                return null;
            }

            $field['type'] = $field['type'] ?? 'text';
            $field['key'] = $field['key'] ?? preg_replace('/[^a-z0-9_]+/', '_', strtolower($field['label'] ?? 'field'));
            $field['label'] = $field['label'] ?? ucfirst($field['key']);
            $field['required'] = (bool) ($field['required'] ?? false);
            $field['options'] = $field['options'] ?? [];
            $field['validations'] = $field['validations'] ?? [];

            return $field;
        }, $schema)));
    }
}
