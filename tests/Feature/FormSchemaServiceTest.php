<?php

namespace Tests\Feature;

use App\Services\FormSchemaService;
use Tests\TestCase;

class FormSchemaServiceTest extends TestCase
{
    public function test_valid_schema_is_accepted(): void
    {
        $service = app(FormSchemaService::class);

        $schema = [
            [
                'type' => 'text',
                'key' => 'full_name',
                'label' => 'Full name',
                'required' => true,
                'placeholder' => 'Enter your full name',
            ],
            [
                'type' => 'email',
                'key' => 'email',
                'label' => 'Email address',
                'required' => true,
            ],
        ];

        $result = $service->validateSchema($schema);

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    public function test_invalid_schema_is_rejected(): void
    {
        $service = app(FormSchemaService::class);

        $schema = [
            [
                'type' => 'text',
                'label' => 'Full name',
                'required' => true,
            ],
        ];

        $result = $service->validateSchema($schema);

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }
}
