<?php

namespace App\Services;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetIOFactory;

class ImportService
{
    public function parseFile(string $path): array
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($extension === 'docx') {
            return $this->parseDocx($path);
        }

        if ($extension === 'xlsx' || $extension === 'xls') {
            return $this->parseXlsx($path);
        }

        return [
            'valid' => false,
            'message' => 'Unsupported file type.',
            'schema' => [],
        ];
    }

    public function parseDocx(string $path): array
    {
        $phpWord = IOFactory::load($path);
        $sections = [];
        $schema = [];

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $text = trim($element->getText());
                    if ($text !== '') {
                        $sections[] = $text;
                    }
                }
            }
        }

        foreach ($sections as $index => $section) {
            $schema[] = [
                'type' => 'text',
                'key' => 'field_' . ($index + 1),
                'label' => $section,
                'required' => false,
            ];
        }

        return [
            'valid' => true,
            'message' => 'Word content parsed into a draft schema.',
            'schema' => $schema,
        ];
    }

    public function parseXlsx(string $path): array
    {
        $spreadsheet = SpreadsheetIOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        $schema = [];

        foreach ($rows as $index => $row) {
            if ($index === 0) {
                continue;
            }

            $label = trim((string) ($row[0] ?? ''));
            if ($label === '') {
                continue;
            }

            $schema[] = [
                'type' => 'text',
                'key' => 'field_' . ($index),
                'label' => $label,
                'required' => false,
            ];
        }

        return [
            'valid' => true,
            'message' => 'Spreadsheet rows mapped into draft fields.',
            'schema' => $schema,
        ];
    }
}
