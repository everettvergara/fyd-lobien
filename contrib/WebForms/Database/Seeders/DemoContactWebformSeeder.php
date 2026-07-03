<?php

namespace App\Modules\WebForms\Database\Seeders;

use App\Modules\WebForms\Models\Webform;
use Illuminate\Database\Seeder;

class DemoContactWebformSeeder extends Seeder
{
    public function run(): void
    {
        Webform::updateOrCreate(
            ['slug' => 'contact'],
            [
                'name' => 'Contact Us',
                'description' => 'Send us a message and we will get back to you.',
                'is_active' => true,
                'schema' => [
                    'fields' => [
                        [
                            'key' => 'name',
                            'type' => 'text',
                            'label' => 'Your Name',
                            'placeholder' => 'Jane Doe',
                            'help' => '',
                            'required' => true,
                            'options' => [],
                            'validation' => ['min' => null, 'max' => 255],
                        ],
                        [
                            'key' => 'email',
                            'type' => 'email',
                            'label' => 'Email Address',
                            'placeholder' => 'you@example.com',
                            'help' => '',
                            'required' => true,
                            'options' => [],
                            'validation' => ['min' => null, 'max' => 255],
                        ],
                        [
                            'key' => 'inquiry_type',
                            'type' => 'select',
                            'label' => 'Inquiry Type',
                            'placeholder' => '',
                            'help' => '',
                            'required' => true,
                            'options' => [
                                ['value' => 'general', 'label' => 'General'],
                                ['value' => 'support', 'label' => 'Support'],
                            ],
                            'validation' => ['min' => null, 'max' => null],
                        ],
                        [
                            'key' => 'preferred_date',
                            'type' => 'date',
                            'label' => 'Preferred Contact Date',
                            'placeholder' => '',
                            'help' => '',
                            'required' => false,
                            'options' => [],
                            'validation' => ['min' => null, 'max' => null],
                        ],
                        [
                            'key' => 'message',
                            'type' => 'textarea',
                            'label' => 'Message',
                            'placeholder' => '',
                            'help' => '',
                            'required' => true,
                            'options' => [],
                            'validation' => ['min' => null, 'max' => 5000],
                        ],
                    ],
                    'settings' => [
                        'submit_label' => 'Send Message',
                        'success_message' => 'Thank you for contacting us.',
                        'redirect_url' => null,
                    ],
                ],
            ],
        );
    }
}
